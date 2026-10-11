<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\Supermarket;
use App\Models\Transaction1;
use App\Models\ConsumerSurvey;
use App\Models\BankAccount;
use App\Models\Npo;
class SpendingActivitiesController extends Controller
{
    public function market_list(Request $request)
{
    
    $userId = auth()->id();
 // 🔹 Bank account check
    $hasBankAccount = BankAccount::where('student_id', $userId)->exists();

    $summary = \App\Services\ConsumerSurveySummaryService::forUser($userId);
    $ConsumersurveyExists = $summary['exists'];
    $diet = $summary['diet'];
    $hasSurvey = $ConsumersurveyExists;
    $itemNames = $summary['itemNames'];
    $basketTotal = $summary['basketTotal'];
    $lunch = $summary['lunch'];
    $transport = $summary['transport'];
    $activities = $summary['activities'];
    $restaurants = $summary['restaurants'];
    $total = $summary['total'];
    $activityId = $request->query('activity_id');
    if (!$activityId) {
    return redirect('/education/high-spending-activities')
        ->with('error', 'Please select an activity first.');
    }
    
    return view(
        'spendingActivities.market-list',
        compact(
            'hasSurvey',
            'diet',
            'basketTotal',
            'lunch',
            'transport',
            'activities',
            'restaurants',
            'total',
            'itemNames',
            'ConsumersurveyExists',
            'hasBankAccount',
            'activityId'
        )
    );
}
public function supermarket(Request $request)
{
    $user = auth()->user();
    $now = now(); // ✅ SAFE replacement for Carbon

    // 🔹 Bank account
    $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();

    // 🔹 Classmates
    $classmates = User::where('id', '!=', $user->id)
        ->where('role', '4')
        ->where('sid', $user->sid)
        ->where('grade', $user->grade)
        ->orderBy('citizenId')
        ->get(['id', 'citizenId', 'nickname']);

    // 🔹 URL type (omnivore / veg / etc.)
    $type = request()->segment(count(request()->segments()));

    // 🔹 Products by type
    $products = Supermarket::where('type', $type)
        ->orderBy('id', 'ASC')
        ->get();

    // 🔹 Last transaction balance
    $latestTxn = Transaction1::where('user_id', $user->id)
        ->latest('id')
        ->first();

    $lastBalance = $latestTxn ? $latestTxn->balance : 0;

    // 🔹 CURRENT MONTH consumer survey ONLY
    $summary = \App\Services\ConsumerSurveySummaryService::forUser($user->id);
    $ConsumersurveyExists = $summary['exists'];

    if (!$ConsumersurveyExists) {
        return redirect('/supermarket/market-list')
            ->with('error', 'Please complete your monthly survey first.');
    }

    $groceries = $summary['groceryItems'];
    $itemNames = $summary['itemNames'];
    $basketTotal = $summary['basketTotal'];
    $lunch = $summary['lunch'];
    $transport = $summary['transport'];
    $activities = $summary['activities'];
    $restaurants = $summary['restaurants'];
    $total = $summary['total'];
    //check the price and product qty limit
    $activityId = $request->query('activity_id');
    if (!$activityId) {
    return redirect('/education/high-spending-activities')
        ->with('error', 'Please select an activity first.');
    }
    $activity = null;

    if ($activityId) {
        $activity = DB::table('activities')->where('id', $activityId)->first();
    }
    $npos = Npo::where('status', 1)
    ->orderBy('name')
    ->get(['id', 'name']);
    //check the price and product qty limit
    return view(
        'spendingActivities.supermarket',
        compact(
            'user',
            'bankAccount',
            'classmates',
            'products',
            'type',
            'lastBalance',
            'groceries',
            'basketTotal',
            'lunch',
            'transport',
            'activities',
            'restaurants',
            'total',
            'itemNames',
            'ConsumersurveyExists',
            'activity',
            'npos'
        )
    );
}
public function spending_tracker($type, Request $request)
{
    $user = auth()->user();
    $hasBankAccount = BankAccount::where('student_id', $user->id)->exists();
    $bankAccount = \App\Models\BankAccount::where('student_id', $user->id)->first();

    $classmates = User::where('id', '!=', $user->id)
        ->where('role', '4')
        ->where('sid', $user->sid)
        ->where('grade', $user->grade)
        ->orderBy('citizenId')
        ->get(['id', 'citizenId', 'nickname']);

    // URL → DB mapping
    $storeMap = [
        'accessories'     => 'Accessories',
        'bespirit-sport-shop'     => 'BeSpirit - Sport Shop',
        'beats-music-store'     => 'Beats - Music Store',
        'comfort-zone'     => 'Comfort Zone',
        'beu-beLuxury'     => 'BeU - BeLuxury',
        'tech-hub'     => 'Tech Hub',
        'daily-essentials'     => 'Daily Essentials',
        'stationery-store'     => 'Stationery Store',
        'the-basics-co'     => 'The Basics Co.',
        'basicco'     => 'The Basics Co.',
        'stationary'   => 'The Stanary.',
        'citymall'  => 'The City Mall.',
    ];

    // Validate URL
    if (!array_key_exists($type, $storeMap)) {
        abort(404);
    }

    // Actual DB value
    $dbType = $storeMap[$type];

    // Fetch products using DB value
    $products = Product::where('type', $dbType)
        ->orderBy('product_name')
        ->get();

    $latestTxn = Transaction1::where('user_id', $user->id)
        ->latest('id')
        ->first();

    $lastBalance = $latestTxn ? $latestTxn->balance : 0;
     //check the price and product qty limit
    $activityId = $request->query('activity_id');
    if (!$activityId) {
    return redirect('/education/high-spending-activities')
        ->with('error', 'Please select an activity first.');
    }
    $activity = null;

    if ($activityId) {
        $activity = DB::table('activities')->where('id', $activityId)->first();
    }
    $npos = Npo::where('status', 1)
    ->orderBy('name')
    ->get(['id', 'name']);
    return view(
        'spendingActivities.spending-tracker',
        compact(
            'user',
            'bankAccount',
            'classmates',
            'products',
            'type',      // URL value (basicco)
            'dbType',    // DB value (The Basics Co.)
            'lastBalance',
            'hasBankAccount',
            'activityId',
            'npos'
        )
    );
}
}