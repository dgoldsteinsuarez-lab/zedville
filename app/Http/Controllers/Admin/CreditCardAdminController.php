<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CardStatement;
use App\Models\CreditCard;
use App\Models\SchoolClass;
use App\Services\CreditCardService;
use Illuminate\Http\Request;

/**
 * Admin > Bank > Credit Cards (proposal step 16: "Límite y condiciones;
 * tarjetas activas y deudas"): the conditions, and each student's card - what
 * they owe, how they pay their statements, and how many came late. The
 * conditions are fixed (App\Services\CreditCardService); read-only.
 */
class CreditCardAdminController extends Controller
{
    public function index(Request $request)
    {
        $ids = $request->user()->manageableStudentIds();
        $cards = CreditCard::when($ids !== null, fn ($q) => $q->whereIn('user_id', $ids))->latest()->get()
            ->each(function (CreditCard $card) {
                $card->owed = CreditCardService::owed($card);
                $card->late = CardStatement::where('card_id', $card->id)->where('status', 'late')->count();
                $card->interest = (float) CardStatement::where('card_id', $card->id)->sum('interest');
            });
        $students = \App\Models\User::whereIn('id', $cards->pluck('user_id'))->get(['id', 'name', 'nickname', 'grade'])->keyBy('id');
        $classes = SchoolClass::whereIn('id', $students->pluck('grade')->filter())->pluck('name', 'id');

        return view('admin.cards.index', [
            'cards' => $cards,
            'students' => $students,
            'classes' => $classes,
            'rules' => ['limit' => CreditCardService::LIMIT, 'tae' => CreditCardService::TAE * 100, 'min' => CreditCardService::MIN_SHARE * 100, 'floor' => CreditCardService::MIN_FLOOR],
        ]);
    }
}
