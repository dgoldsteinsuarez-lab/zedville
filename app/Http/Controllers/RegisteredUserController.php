<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mailbox;
use App\Models\Grade;
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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            //'reEnterEmail' => ['required', 'same:email'],
            'classCode' => ['required', 'exists:classes,classCode'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:2,3,4'],
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
        $grade = SchoolClass::where('classCode', $request->classCode)
            //->where('sid', $sid) // optional but recommended
            ->first();

        if (!$grade) {
            return redirect()->back()
                ->withErrors(['classCode' => 'Invalid class code.'])
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
            'sid' => $sid,
            'name' => $request->name,
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
            'subject' => 'Welcome to Zedville , ' . $user->name . '!',
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
}
