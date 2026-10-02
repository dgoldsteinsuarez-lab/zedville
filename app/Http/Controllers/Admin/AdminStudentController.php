<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\Grade;
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
        $query = User::with(['gradeRelation', 'mascotRelation'])
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

        //$students = $query->get();
        $students = $query
            ->orderBy('id', 'desc')
            ->get();
        $sid = session('selected_school');

        $grades = $sid
            ? Grade::where('sid', $sid)->get()
            : Grade::all();

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

        $grades = Grade::all();
        $mascots = Mascot::all();
        $schools = DB::table('school_domains')
            ->orderBy('id', 'asc')
            ->get();
        return view('admin.student.details', compact('student', 'grades', 'mascots', 'schools'));
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
    public function add_student(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            //'reEnterEmail' => ['required', 'same:email'],
            //'password' => ['required', 'confirmed', Rules\Password::defaults()],
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
        ]);
        MailboxScheduler::scheduleForEvent('registration', $user->id);
        return redirect('admin/student')->with('success', 'Student details Created successfully.');
    }
}