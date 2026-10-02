<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Grade;
use App\Models\SchoolDomain;
use App\Models\LoginQuestion;
use App\Models\LoginOption;
use App\Models\Month;
use App\Models\SchoolMonthSetting;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $schools = DB::table('school_domains')
        ->orderBy('id', 'asc')
        ->get();

    return view('admin.dashboard', compact('schools'));
    }
    public function setSchool(Request $request)
{
    $request->validate([
        'school_id' => 'nullable|exists:school_domains,id',
    ]);

    // Only a Super Admin may switch school (IsAdmin pins everyone else).
    if (!Auth::user()->isSuperAdmin()) {
        abort(403);
    }

    // Store selected school in session
    session(['selected_school' => $request->school_id]);

    // Redirect to dashboard
    //return redirect()->route('dashboard');
    return redirect('admin/dashboard')->with('success', 'School selected successfully!');
}


    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|different:current_password',
            'confirm_password' => 'required|same:password',
        ]);

        $user = Auth::user();
        $user->forceFill([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'must_change_password' => false,
        ])->save();

        return redirect('admin/profile')->with('success', 'Password changed.');
    }

    public function profile()
    {
        return view('admin.profile');
    }
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect('admin/profile')->with('success', 'Profile updated successfully!');
    }
    public function updatepassowrd(Request $request)
    {
        
         $user = Auth::user();

    $request->validate([
        'current_password' => 'required',
        'password' => 'required|min:8',
        'confirm_password' => 'required|same:password',
    ]);

    if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors(['current_password' => 'Current password is incorrect']);
    }

    $user->update([
        'password' => Hash::make($request->password),
    ]);

    return redirect('admin/profile')->with('success', 'Password updated successfully!');
    }
    //For Role
    public function role(Request $request)
    {
        //$query = Role::orderByDesc('id');
        $query = Role::orderBy('id', 'asc');

        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
    // ✅ Apply SID filter (only if selected)
        /*if (session()->has('selected_school')) {
            $query->where('sid', session('selected_school'));
        }*/
        $roles = $query->get();

        return view('admin.role.index', compact('roles'));
    }
    public function addRole(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:roles,name',
    ]);

    $role = new Role();
    $role->sid  = session('selected_school'); // ✅ sid or NULL
    $role->name = $request->name;
    $role->save();

    return redirect()->back()->with('success', 'Role added successfully!');
}
public function deleteRole(Request $request, $id)
{
    if ($request->confirm_code != $request->delete_code) {
        return back()->with('error', 'Confirmation code does not match.');
    }

    $role = Role::findOrFail($id);
    $role->delete();

    return redirect()->back()->with('success', 'Role deleted successfully!');
}
public function roleDetails($id)
{
    //$query = Role::orderByDesc('id');
    $role = Role::findOrFail($id);

    return view('admin.role.details', compact('role'));
}
public function updateRole(Request $request, $id)
{
    // Validate incoming request
    $request->validate([
        'name' => 'required|string|max:255',  // Ensure 'name' is required and valid
    ]);

    // Get role by ID or fail with 404
    $role = Role::findOrFail($id);

    // Update the role's name
    $role->name = $request->name;

    // Save changes to database
    $role->save();

    // Redirect back with success message
    return redirect()->back()->with('success', 'Role updated successfully!');
}
//For Grade / Class
public function grade(Request $request)
    {
        //$query = Role::orderByDesc('id');
        $query = Grade::orderBy('id', 'asc');

        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        // ✅ Apply SID filter (only if selected)
    if (session()->has('selected_school')) {
        $query->where('sid', session('selected_school'));
    }
        $grade = $query->get();

        return view('admin.grade.index', compact('grade'));
        
    }
    public function addGrade(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:roles,name',
    ]);
    // Generate unique 6-digit class code
    do {
        $classCode = mt_rand(100000, 999999);
    } while (Grade::where('classCode', $classCode)->exists());

    $grade = new Grade();
    $grade->sid  = session('selected_school'); // ✅ sid or NULL
    $grade->classCode = $classCode; // Save generated code
    $grade->name = $request->name;
    $grade->save();

    return redirect()->back()->with('success', 'Grade / Class added successfully!');
}
/*public function updateGrade(Request $request, $id)
{
    // Validate incoming request
    $request->validate([
        'name' => 'required|string|max:255',  // Ensure 'name' is required and valid
    ]);

    // Get role by ID or fail with 404
    $grade = Grade::findOrFail($id);

    // Update the role's name
    $grade->name = $request->name;

    // Generate class code only if it doesn't exist
    if (empty($grade->classCode)) {

        do {
            $classCode = mt_rand(100000, 999999);
        } while (Grade::where('classCode', $classCode)->exists());

        $grade->classCode = $classCode;
    }


    // Save changes to database
    $grade->save();

    // Redirect back with success message
    return redirect()->back()->with('success', 'Grade / Class updated successfully!');
}*/
public function updateGrade(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'classCode' => 'nullable|numeric|digits:6|unique:classes,classCode,' . $id,
    ]);

    $grade = Grade::findOrFail($id);

    $grade->name = $request->name;

    // Admin entered a class code
    if ($request->filled('classCode')) {

        $grade->classCode = $request->classCode;

    }
    // No class code exists yet, generate one
    elseif (empty($grade->classCode)) {

        do {
            $classCode = mt_rand(100000, 999999);
        } while (Grade::where('classCode', $classCode)->exists());

        $grade->classCode = $classCode;
    }

    $grade->save();

    return redirect()->back()->with(
        'success',
        'Grade / Class updated successfully!'
    );
}
public function deleteGrade($id)
{
    $role = Grade::findOrFail($id);
    $role->delete();

    return redirect()->back()->with('success', 'Grade / Class deleted successfully!');
}
// School Domain
public function schoolDomain(Request $request)
{
    $query = SchoolDomain::orderBy('id', 'asc');

    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('school_name', 'like', '%' . $search . '%')
              ->orWhere('school_code', 'like', '%' . $search . '%')
              ->orWhere('school_domain', 'like', '%' . $search . '%');
        });
    }

    $SchoolDomain = $query->get();

    return view('admin.domain.index', compact('SchoolDomain'));
}
    public function addschoolDomain(Request $request)
{
    $request->validate([
        'school_name' => 'required|string|max:255',
        'school_code' => 'required|string|max:50|unique:school_domains,school_code',
        'school_phone' => 'nullable|string|max:20',
        'country_region' => 'nullable|string|max:255',
        'school_domain' => 'required|string|max:255',
    ]);

    // Extract domain name only
    $inputDomain = trim($request->school_domain);

    // Add protocol if missing, for parse_url to work
    if (!preg_match('/^https?:\/\//', $inputDomain)) {
        $inputDomain = 'http://' . $inputDomain;
    }

    $parsedUrl = parse_url($inputDomain, PHP_URL_HOST);

    // Remove 'www.' prefix if present
    $domainOnly = preg_replace('/^www\./', '', $parsedUrl);

    // Validate uniqueness of cleaned domain
    $existing = \App\Models\SchoolDomain::where('school_domain', $domainOnly)->exists();
    if ($existing) {
        return redirect()->back()->withErrors(['school_domain' => 'This domain already exists.'])->withInput();
    }

    SchoolDomain::create([
        'school_name' => $request->school_name,
        'school_code' => $request->school_code,
        'school_phone' => $request->school_phone,
        'country_region' => $request->country_region,
        'school_domain' => $domainOnly,
    ]);

    return redirect()->back()->with('success', 'School domain added successfully!');
}
public function deleteschoolDomain(Request $request, $id)
{
    if ($request->confirm_code != $request->delete_code) {
        return back()->with('error', 'Confirmation code does not match.');
    }
    $role = SchoolDomain::findOrFail($id);
    $role->delete();

    return redirect()->back()->with('success', 'School domain deleted successfully!');
}
public function updateschoolDomain(Request $request, $id)
{
    $request->validate([
        'school_name' => 'required|string|max:255',
        'school_code' => 'required|string|max:50|unique:school_domains,school_code,' . $id,
        'school_phone' => 'nullable|string|max:20',
        'country_region' => 'nullable|string|max:255',
        'school_domain' => 'required|string|max:255',
    ]);

    // Extract domain name only
    $inputDomain = trim($request->school_domain);

    // Add protocol if missing
    if (!preg_match('/^https?:\/\//', $inputDomain)) {
        $inputDomain = 'http://' . $inputDomain;
    }

    $parsedUrl = parse_url($inputDomain, PHP_URL_HOST);

    // Remove 'www.' prefix
    $domainOnly = preg_replace('/^www\./', '', $parsedUrl);

    // Validate uniqueness of cleaned domain excluding current record
    $existing = \App\Models\SchoolDomain::where('school_domain', $domainOnly)
        ->where('id', '!=', $id)
        ->exists();

    if ($existing) {
        return redirect()->back()->withErrors(['school_domain' => 'This domain already exists.'])->withInput();
    }

    // Find and update
    $school = \App\Models\SchoolDomain::findOrFail($id);
    $school->update([
        'school_name' => $request->school_name,
        'school_code' => $request->school_code,
        'school_phone' => $request->school_phone,
        'country_region' => $request->country_region,
        'school_domain' => $domainOnly,
    ]);

    return redirect()->back()->with('success', 'School domain updated successfully!');
}
public function calendar()
{
    $activitiesTypes = DB::table('activitiesType')->get();
    $activities = DB::table('activities')->get();
    return view('admin.calendar.calendar', compact('activitiesTypes', 'activities'));
}
/*public function class_list()
{
    $classes = Grade::select('id', 'name')->orderBy('name')->get();

    return response()->json([
        'status' => true,
        'data' => $classes
    ]);
}*/
public function class_list()
{
    $query = Grade::select('id', 'name');

    if (session()->has('selected_school')) {
        $query->where('sid', session('selected_school'));
    }

    $classes = $query->orderBy('name')->get();

    return response()->json([
        'status' => true,
        'data' => $classes
    ]);
}
public function city_bank_account(Request $request)
{
    $query = DB::table('bank_accounts as ba')
        ->select(
            'ba.*',
            DB::raw('(
                SELECT t.balance
                FROM transactions1 t
                WHERE t.bank_account_id = ba.id
                ORDER BY t.id DESC
                LIMIT 1
            ) as current_balance')
        )
        ->orderBy('ba.id', 'asc');

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('ba.student_name', 'like', '%' . $request->search . '%')
              ->orWhere('ba.student_email', 'like', '%' . $request->search . '%')
              ->orWhere('ba.primary_savings_account_number', 'like', '%' . $request->search . '%');
        });
    }
    // ✅ Apply SID filter (only if selected)
    if (session()->has('selected_school')) {
        $query->where('sid', session('selected_school'));
    }
    //$accounts = $query->get();
    $accounts = $query->paginate(20)->withQueryString();

    return view('admin.city-bank.accounts', compact('accounts'));
}
//Question List
public function login_question(Request $request)
{
    $months = Month::all();
    //$questions = LoginQuestion::with('month')->latest()->get();
    //$questions = LoginQuestion::with(['month','options'])->latest()->get();
    $query = LoginQuestion::with(['month','options','school']);

    // ✅ SEARCH FILTER
    if ($request->search) {

    $search = $request->search;

    $query->where(function($q) use ($search) {

        // search in question
        $q->where('question', 'like', "%$search%")

          // search in month name
          ->orWhereHas('month', function($m) use ($search) {
              $m->where('name', 'like', "%$search%");
          })

          // ✅ search in school name
          ->orWhereHas('school', function($s) use ($search) {
              $s->where('school_name', 'like', "%$search%");
          });

    });
}

    $questions = $query->latest()->get();
    $schools = DB::table('school_domains')
        ->orderBy('id', 'asc')
        ->get();
    return view('admin.loginQuestion.index', compact('months','questions', 'schools'));
}
// Question STORE

public function loginQuestionStore(Request $request)
{
    if ($request->id) {

        // ✅ UPDATE QUESTION
        $question = LoginQuestion::find($request->id);

        $question->update([
            'sid' => $request->school,
            'month_name' => $request->month_name,
            'month_id' => $request->month_id,
            'question' => $request->question,
            'type' => $request->type
        ]);

    } else {

        // ✅ CREATE QUESTION
        $question = LoginQuestion::create([
            'sid' => $request->school,
            'month_name' => $request->month_name,
            'month_id' => $request->month_id,
            'question' => $request->question,
            'type' => $request->type
        ]);
    }

    $existingIds = [];

    foreach ($request->options as $key => $option) {

        if (!empty($option)) {

            $optionId = $request->option_ids[$key] ?? null;

            if ($optionId) {

                // ✅ UPDATE OPTION
                $opt = LoginOption::find($optionId);

                if ($opt) {
                    $opt->update([
                        'option_text' => $option,
                        'is_correct' => ($request->correct == $key) ? 1 : 0
                    ]);

                    $existingIds[] = $opt->id;
                }

            } else {

                // ✅ ADD NEW OPTION
                $newOpt = LoginOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option,
                    'is_correct' => ($request->correct == $key) ? 1 : 0
                ]);

                $existingIds[] = $newOpt->id;
            }
        }
    }

    // ✅ DELETE REMOVED OPTIONS
   /* LoginOption::where('question_id', $question->id)
        ->whereNotIn('id', $existingIds)
        ->delete();*/

    $message = $request->id ? 'Updated successfully!' : 'Added successfully!';

    return redirect()->back()->with('success', $message);
}
//Question Delete
public function loginQuestionDestroy(Request $request, $id)
{
    if ($request->confirm_code != $request->delete_code) {
        return back()->with('error', 'Confirmation code does not match.');
    }
    // delete options first
    LoginOption::where('question_id', $id)->delete();
    // delete question
    LoginQuestion::findOrFail($id)->delete();

    return redirect()->back()->with('success', 'Login Question Deleted successfully!');
}
public function school_month_settings(){
     $schools = SchoolDomain::leftJoin('school_month_settings as sms', 'sms.school_id', '=', 'school_domains.id')
        ->select(
            'school_domains.id',
            'school_domains.school_name',
            'sms.start_month'
        )
        ->get();

    return view('admin.loginQuestion.school-month-settings', compact('schools'));
}
public function bulkSaveSMS(Request $request)
{
    $data = $request->data; // array

    foreach ($data as $item) {

        SchoolMonthSetting::updateOrCreate(
            ['school_id' => $item['school_id']],
            ['start_month' => $item['start_month']]
        );
    }

    return response()->json([
        'status' => true,
        'message' => 'All months saved successfully!'
    ]);
}

/**
 * Super Admin: update base monthly salary setting
 */
public function updateBaseSalary(Request $request)
{
    $user = Auth::user();
    if ($user->role != 1 && $user->role != '1') {
        return back()->with('error', 'Only Super Admin can edit the base monthly salary.');
    }

    $request->validate([
        'monthly_salary' => 'required|numeric|min:1',
    ]);

    DB::table('settings')->updateOrInsert(
        ['key' => 'monthly_salary'],
        ['value' => $request->monthly_salary, 'updated_at' => now()]
    );

    return back()->with('success', 'Base monthly salary updated successfully!');
}

/**
 * Tutor / Admin: Toggle between Management Panel and Citizen View without logging out
 */
public function toggleViewMode(Request $request)
{
    $user = Auth::user();
    if (in_array($user->role, [1, 2, 3, '1', '2', '3', 'admin', 'tutor'])) {
        $current = session('view_mode', 'admin');
        $newMode = ($current === 'citizen') ? 'admin' : 'citizen';
        session(['view_mode' => $newMode]);

        return $newMode === 'citizen'
            ? redirect('/dashboard')->with('success', 'Switched to Citizen View.')
            : redirect('/admin/dashboard')->with('success', 'Switched to Management Panel.');
    }

    return redirect('/dashboard');
}
}

