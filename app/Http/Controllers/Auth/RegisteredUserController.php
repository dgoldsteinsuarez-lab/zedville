<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mailbox;
use App\Models\SchoolClass;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Helpers\MailboxHelper;
use App\Helpers\MailboxScheduler;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        //$timezone = $request->input('timezone', 'UTC');
        //dd(Carbon::now($timezone)->format('Y-m-d H:i:s'));
        //dd($request->timezone);

        // An admin may have already created this student's account (real
        // name + a shared temporary password, no nickname set yet - see
        // AdminStudentController::add_student()). If so, this submission
        // is the student CLAIMING that account (setting their own nickname
        // + password), not creating a new one - a nickname is the signal
        // an account has already been claimed, since add_student() never
        // sets one. Daniel, 2026-10-19.
        $staged = User::where('email', $request->email)
            ->where('role', 4)
            ->whereNull('nickname')
            ->first();

        if ($staged) {
            return $this->claimStagedAccount($request, $staged);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Single word only — keeps this from being used to enter a full name
            // (first + family name). Public views show this, never 'name'.
            'nickname' => ['required', 'string', 'min:3', 'max:15', 'regex:/^\S+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            //'reEnterEmail' => ['required', 'same:email'],
            'classCode' => ['required', 'exists:classes,classCode'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['nullable'],
        ], [
            'nickname.regex' => 'Nickname must be a single word (e.g. your first name) — no family name.',
        ]);
        // Extract domain from email
        $email = $request->email;
        $emailDomain = substr(strrchr($email, "@"), 1); // Gets everything after '@'
        // Check if domain exists in school_domains
        $domainExists = \App\Models\SchoolDomain::where('school_domain', $emailDomain)->exists();
        if (!$domainExists) {
            return redirect()->back()
                ->withErrors(['email' => 'Your school domain is not authorised to register.'])
                ->withInput();
        }
        $school = \App\Models\SchoolDomain::where('school_domain', $emailDomain)->first();
        $sid = $school->id;
        // Find Grade/Class by class code
        // The class must belong to the same school as the email domain; otherwise a
        // student of school A could join (or probe) a class of school B.
        $grade = SchoolClass::where('classCode', $request->classCode)
            ->where('sid', (string) $sid)
            ->first();

        if (!$grade) {
            return redirect()->back()
                ->withErrors(['classCode' => 'Invalid class code.'])
                ->withInput();
        }
        //Generate Cityzen ID
        $year = now()->year;
        // Next free number: counting students breaks as soon as one is deleted, which
        // would hand out an existing ID again, so keep going until the ID is unused.
        $userCount = User::where('role', '4')->count() + 1;
        do {
            $cityzenId = 'ZV-' . $year . '-' . str_pad($userCount++, 4, '0', STR_PAD_LEFT);
        } while (User::where('citizenId', $cityzenId)->exists());
        //Generate Cityzen ID

        $user = User::create([
            'sid' => $sid,
            'name' => $request->name,
            'nickname' => $request->nickname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'citizenId' => $cityzenId, // Generate a unique citizen ID
            //'role' => $request->role,
            'role' => 4,
            'avatar' => random_int(1, 9),
            'grade' => $grade->id, // Store class ID
        ]);

        event(new Registered($user));

        //Auth::login($user);
        // Insert welcome message into mailbox table
        // Render the content from welcome template
        /*$content = MailboxHelper::renderMailboxTemplate('welcome', ['user' => $user]);
        $mailbox =Mailbox::create([
        'student_id' => $user->id,
        'subject' => 'Welcome to Zedville',
        'content' => $content,
        'type' => 'primary',
        'read' => 0,
    ]);*/
        // Schedule registration emails
        //MailboxScheduler::scheduleForEvent('registration', $user->id);
        /*MailboxScheduler::scheduleForEvent('registration', $user->id, [
            'name' => $user->name, // replace placeholder dynamically
            'citizenId' => $user->citizen_id, // replace placeholder dynamically
        ]);*/
        $timezone = $request->input('timezone', 'UTC');
        // Send Welcome Email
        $content = view('mailbox_templates.welcome', ['user' => $user])->render();
        Mailbox::create([
            'student_id' => $user->id,
            'subject' => 'Welcome to Zedville , ' . $user->nickname . '!',
            'content' => $content,
            'type' => 'primary',
            'read' => 0,
            'created_at' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
        ]);
        // Send Welcome Email
        //return redirect(RouteServiceProvider::HOME);
        return redirect()->route('login')->with('success', 'You successfully registered to the website and log in to your profile now');
    }

    /**
     * A student finishing an account an admin already created for them
     * (real name + a shared temporary password set via
     * AdminStudentController::add_student()). Requires the current
     * (admin-given) password as proof - the class code and new password
     * alone aren't enough, since the class code and the shared starting
     * password are both known to every student in the class, not just the
     * account owner.
     */
    private function claimStagedAccount(Request $request, User $staged): RedirectResponse
    {
        $request->validate([
            'nickname' => ['required', 'string', 'min:3', 'max:15', 'regex:/^\S+$/'],
            'classCode' => ['required', 'exists:classes,classCode'],
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nickname.regex' => 'Nickname must be a single word (e.g. your first name) — no family name.',
            'current_password.required' => 'Enter the password your teacher gave you to finish setting up this account.',
        ]);

        if (!Hash::check($request->current_password, $staged->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'That password is incorrect - ask your teacher for your original login details.'])
                ->withInput();
        }

        // The class must belong to the same school this account was
        // already created under - never re-derived from the email, since
        // the account's sid is already fixed by the admin.
        $class = SchoolClass::where('classCode', $request->classCode)
            ->where('sid', (string) $staged->sid)
            ->first();

        if (!$class) {
            return redirect()->back()
                ->withErrors(['classCode' => 'Invalid class code for this account.'])
                ->withInput();
        }

        if ($staged->grade && (int) $staged->grade !== $class->id) {
            return redirect()->back()
                ->withErrors(['classCode' => "This class code doesn't match your account - check with your teacher."])
                ->withInput();
        }

        $staged->nickname = $request->nickname;
        $staged->password = Hash::make($request->password);
        $staged->grade = $class->id;
        $staged->save();

        $timezone = $request->input('timezone', 'UTC');
        $content = view('mailbox_templates.welcome', ['user' => $staged])->render();
        Mailbox::create([
            'student_id' => $staged->id,
            'subject' => 'Welcome to Zedville , ' . $staged->nickname . '!',
            'content' => $content,
            'type' => 'primary',
            'read' => 0,
            'created_at' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now($timezone)->format('Y-m-d H:i:s'),
        ]);

        return redirect()->route('login')->with('success', 'Your account is ready - log in with your new password now.');
    }
}
