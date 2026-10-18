<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Mascot;
use App\Models\SchoolDomain;
use App\Helpers\MailboxScheduler;
class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        /*$students = User::with(['gradeRelation', 'mascotRelation'])
        ->where('role', 4) // role = 4 means student
        ->get();*/
        $query = User::with(['gradeRelation.grade.level', 'gradeRelation.tutors:users.id,users.nickname', 'mascotRelation'])
            ->where('role', 4); // role = 4 means student

        // ✅ Apply SID filter (only if selected)
        if (session()->has('selected_school')) {
            $query->where('sid', session('selected_school'));
        }
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('citizenId', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by Class (Bank Admin Functions doc: "Filter by Class").
        // `users.grade` holds the student's Class id (SchoolClass), not a
        // curriculum Grade - see the SchoolClass/Grade split note elsewhere.
        if ($request->filled('class_id')) {
            $query->where('grade', $request->class_id);
        }

        //$students = $query->get();
        // Grade level first, then class, then name (students without a class last).
        $students = $query
            ->orderBy('name')
            ->get()
            ->sortBy(fn ($st) => [
                optional(optional(optional($st->gradeRelation)->grade)->level)->name ?? 'zzz',
                optional($st->gradeRelation)->name ?? 'zzz',
            ])->values();
        $sid = session('selected_school');

        $grades = $sid
            ? SchoolClass::where('sid', $sid)->get()
            : SchoolClass::all();

        $mascots = $sid
            ? Mascot::where(fn ($q) => $q->where('sid', $sid)->orWhereNull('sid'))->get() // shared mascots have no sid
            : Mascot::all();
        $schools = DB::table('school_domains')
            ->orderBy('id', 'asc')
            ->get();
        return view('admin.student.index', compact('students', 'grades', 'mascots', 'schools'));
    }
    public function details($id)
    {
        $student = User::with(['gradeRelation', 'mascotRelation', 'avatarRelation', 'schoolRelation'])
            ->where('role', 4) // role = 4 means student
            ->findOrFail($id);

        $grades = SchoolClass::all();
        $mascots = Mascot::all();
        $schools = DB::table('school_domains')
            ->orderBy('id', 'asc')
            ->get();

        // Bank Admin Functions doc, Screen 2: balance, transaction history,
        // and salary list shown on the same page as the rest of the
        // student's details, instead of three disconnected admin tools.
        $entries = \App\Models\Transaction1::where('user_id', $student->id)->orderByDesc('id')->limit(100)->get();
        $balance = $entries->first()->balance ?? 0;
        $salaryBonuses = \App\Models\SalaryBonus::where('student_id', $student->id)->latest()->get();

        return view('admin.student.details', compact('student', 'grades', 'mascots', 'schools', 'entries', 'balance', 'salaryBonuses'));
    }

    public function update_details(Request $request, $id)
    {
        $student = User::findOrFail($id);
        $student->sid = $request->school;
        $student->name = $request->fullName;
        $student->email = $request->email;
        $student->age = $request->age;
        $student->grade = $request->grade;
        $student->mascot = $request->mascot;
        $student->address = $request->address;
        $student->save();
        return redirect('admin/student/details/' . $student->id)->with('success', 'Student details updated successfully.');

    }
    /**
     * Set a new password for an existing student. Needed because
     * add_student()'s password field had no `name` attribute at all
     * until 2026-10-19 - every student added before that fix ended up
     * with an unknown/empty password and no way to log in. See bugs.md.
     */
    public function reset_password(Request $request, $id)
    {
        $student = User::where('role', 4)->findOrFail($id);

        $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $student->password = Hash::make($request->password);
        $student->save();

        return redirect('admin/student/details/' . $student->id)->with('success', 'Password updated.');
    }

    public function add_student(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            // The password input had no `name` attribute at all until now,
            // so this was never actually required or hashed - every
            // admin-created student ended up with an unknown/empty
            // password (bugs.md, 2026-10-19).
            'password' => ['required', 'string', 'min:8'],
        ]);
        $email = $request->email;
        $emailDomain = substr(strrchr($email, "@"), 1); // Gets everything after '@'
        // Check if domain exists in school_domains
        $domainExists = \App\Models\SchoolDomain::where('school_domain', $emailDomain)->exists();
        if (!$domainExists) {
            return redirect()->back()
                ->withErrors(['email' => 'Your school domain is not authorised to register.'])
                ->withInput();
        }
        //Generate Cityzen ID
        $year = now()->year;
        $userCount = User::where('role', '4')->count() + 1;
        //$userCount = User::count() + 1;
        $formattedNumber = str_pad($userCount, 4, '0', STR_PAD_LEFT);
        $cityzenId = "ZV-{$year}-{$formattedNumber}";
        //Generate Cityzen ID
        $user = User::create([
            'sid' => session()->has('selected_school')
                ? session('selected_school')
                : $request->school,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'citizenId' => $cityzenId, // Generate a unique citizen ID
            'role' => 4,
            'avatar' => random_int(1, 9),
            // The form's Class dropdown (name="grade") was never actually
            // saved - found while building the self-service account-claim
            // flow, which needs this set to verify a student's class code
            // on claim.
            'grade' => $request->grade ?: null,
        ]);
        MailboxScheduler::scheduleForEvent('registration', $user->id);
        return redirect('admin/student')->with('success', 'Student details Created successfully.');
    }
}