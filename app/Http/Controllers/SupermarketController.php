<?php

namespace App\Http\Controllers;

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


class SupermarketController extends Controller
{
    public function index()
    {
        return view('supermarket.index');
    }

public function market_list()
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

    return view(
        'supermarket.market-list',
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
            'hasBankAccount'
        )
    );
}
public function supermarket()
{
    $user = auth()->user();
    $now = now(); // ✅ SAFE replacement for Carbon

    app(\App\Services\ParticipationService::class)->award($user->id, 'building_enter', 'supermarket');

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
$npos = Npo::where('status', 1)
    ->orderBy('name')
    ->get(['id', 'name']);
    return view(
        'supermarket.supermarket',
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
            'npos' 
        )
    );
}
}
