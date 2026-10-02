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
    $now = Carbon::now();
 // 🔹 Bank account check
    $hasBankAccount = BankAccount::where('student_id', $userId)->exists();
    // 🔹 Get latest survey for CURRENT MONTH
    $Consumersurvey = ConsumerSurvey::where('user_id', $userId)
        //->whereMonth('created_at', $now->month)
        //->whereYear('created_at', $now->year)
        ->orderBy('id', 'desc')
        ->first();

    // 🔹 Survey exists for this month?
    $ConsumersurveyExists = (bool) $Consumersurvey;

    // 🔹 Diet
    $diet = $Consumersurvey->diet ?? null;
    $hasSurvey = $ConsumersurveyExists;

    // 🔹 Groceries handling (string or array safe)
    $groceryItems = [];
    if ($Consumersurvey && $Consumersurvey->groceries) {
        $groceryItems = is_array($Consumersurvey->groceries)
            ? $Consumersurvey->groceries
            : json_decode($Consumersurvey->groceries, true);
    }

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
    $Consumersurvey = ConsumerSurvey::where('user_id', $user->id)
        //->whereMonth('created_at', $now->month)
        //->whereYear('created_at', $now->year)
        ->orderBy('id', 'desc')
        ->first();

    $ConsumersurveyExists = (bool) $Consumersurvey;

    if (!$ConsumersurveyExists) {
        return redirect('/supermarket/market-list')
            ->with('error', 'Please complete your monthly survey first.');
    }

    // 🔹 Groceries (string / array safe)
    $groceriesRaw = $Consumersurvey->groceries ?? '[]';

    $groceryItems = is_array($groceriesRaw)
        ? $groceriesRaw
        : json_decode($groceriesRaw, true);

    $groceries = $groceryItems;
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
