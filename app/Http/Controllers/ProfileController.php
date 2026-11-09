<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Mascot;
use App\Models\SchoolClass;
use App\Models\Avatar;
use App\Models\ConsumerSurvey;
use App\Models\BankAccount;
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $now = now(); // ✅ month-safe, no Carbon import issues

        // 🔹 Avatars & mascots
        $avatar = Avatar::where('status', 1)->get();
        $mascots = Mascot::where('status', 1)->get();
        $selectedAvatar = $avatar->firstWhere('id', $user->avatar);
        // 🔹 Bank account check
        $hasBankAccount = BankAccount::where('student_id', $user->id)->exists();

        // City Map, Phase 1 (2026-10-19): the student's own street+number,
        // auto-assigned on first view - null if they have no Class yet or
        // their Grade's address pool is exhausted.
        $myAddress = $user->isStudent()
            ? \App\Services\AddressService::assignForStudent($user)?->load('street')
            : null;


        // 🔹 Grades
        $grades = SchoolClass::where('status', 1)->get();

        // 🔹 The survey in effect this month (one done this month for next
        // month takes over on the 1st - ConsumerSurvey::inEffect)
        $Consumersurvey = ConsumerSurvey::inEffect($user->id);
        // Once per calendar month (Daniel, 2026-10-23); a newer survey
        // waiting for next month is shown as such.
        $surveyDoneThisMonth = ConsumerSurvey::doneThisMonth($user->id);
        $upcomingSurvey = ConsumerSurvey::where('user_id', $user->id)->orderByDesc('id')->get()
            ->first(fn ($s) => $s->monthStart() && $s->monthStart()->gt(now()->startOfMonth()));

        // A survey skipped in activation isn't done: show the form, not
        // results full of zeros with the button locked.
        $ConsumersurveyExists = $Consumersurvey && !$Consumersurvey->isSkipped();

        // 🔹 Groceries (string / array safe)
        $groceriesRaw = $Consumersurvey->groceries ?? '[]';

        $groceryItems = is_array($groceriesRaw)
            ? $groceriesRaw
            : json_decode($groceriesRaw, true);

        $itemNames = collect($groceryItems)->pluck('name')->implode(', ');

        // 🔹 Default values
        $basketTotal = 0;
        $lunch = 0;
        $transport = 0;
        $activities = 0;
        $restaurants = 0;
        $total = 0;

        if ($Consumersurvey) {
            $basketTotal = $Consumersurvey->basket_cost ?? 0;
            $lunch = $Consumersurvey->lunch ?? 0;
            $transport = $Consumersurvey->transport ?? 0;
            $activities = $Consumersurvey->activities ?? 0;
            $restaurants = $Consumersurvey->restaurants ?? 0;

            $total = $Consumersurvey->total_cost
                ?? ($basketTotal + $lunch + $transport + $activities + $restaurants);
        }
        $moodData = app(MoodController::class)->getMoodData();
        // the Badges tab: the latest badge earned, this month so far, the year (App\Services\BadgeOverview)
        $badges = app(\App\Services\BadgeOverview::class)->forStudent((int) $user->id);
        //Durable goods / Non Durable 
        $userId = Auth::id();

        // Durable Goods
        $durableProducts = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.product_name', '=', 'order_items.name')
            ->where('orders.user_id', $userId)
            ->where('products.goods_type', 'Durable Goods')
            ->select('products.product_name')
            ->distinct()
            ->pluck('product_name');
        //dd($durableProducts);
        // Non Durable Goods
        $nonDurableProducts = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.product_name', '=', 'order_items.name')
            ->where('orders.user_id', $userId)
            ->where('products.goods_type', 'Not Durable Goods')
            ->select('products.product_name')
            ->distinct()
            ->pluck('product_name');
        //Durable goods / Non Durable goods

        // Settings > Notifications tab: existing prefs, defaulting to
        // enabled for any category the user hasn't touched yet.
        $savedNotificationPrefs = \App\Models\NotificationPreference::where('user_id', $user->id)
            ->pluck('enabled', 'category');
        $notificationPreferences = collect(\App\Models\NotificationPreference::CATEGORIES)
            ->mapWithKeys(fn ($category) => [$category => $savedNotificationPrefs->get($category, true)]);

        return view('profile.edit', [
            'notificationPreferences' => $notificationPreferences,
            'user' => $user,
            'mascots' => $mascots,
            'avatar' => $avatar,
            'selectedAvatar' => $selectedAvatar,
            'grades' => $grades,

            // 🔹 Survey data (MONTH-WISE)
            'ConsumersurveyExists' => $ConsumersurveyExists,
            'surveyDoneThisMonth' => $surveyDoneThisMonth,
            'upcomingSurvey' => $upcomingSurvey,
            'Consumersurvey' => $Consumersurvey,
            'basketTotal' => $basketTotal,
            'lunch' => $lunch,
            'transport' => $transport,
            'activities' => $activities,
            'restaurants' => $restaurants,
            'total' => $total,
            'itemNames' => $itemNames,
            'hasBankAccount' => $hasBankAccount,
            'myAddress' => $myAddress,
            // ✅ ADD THESE
            'myLogs' => $moodData['myLogs'],
            'todayMyLog' => $moodData['todayMyLog'],
            'cityMonths' => $moodData['cityMonths'],
            'badges' => $badges,
            'durableProducts' => $durableProducts, // ✅ ADD THIS
            'nonDurableProducts' => $nonDurableProducts, // ✅ ADD THIS

        ]);
    }


    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'exists:avatars,id'],
        ]);

        $user = $request->user();
        $user->avatar = $request->avatar;
        $user->save();

        return redirect()->back()->with('status', 'avatar-updated');
    }
}
