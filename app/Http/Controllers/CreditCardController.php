<?php

namespace App\Http\Controllers;

use App\Models\CardMovement;
use App\Models\CardStatement;
use App\Services\CreditCardService;
use Illuminate\Http\Request;

/**
 * The bank's credit card page, from the Intermediate (App\Services\CreditCardService):
 * its conditions and what paying all or deferring costs, the request, and
 * with a card what is owed and available, how the statements are paid (all,
 * the minimum, a fixed amount), the statements and the movements.
 */
class CreditCardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless(CreditCardService::offered($user), 404);
        CreditCardService::settle($user);
        $card = CreditCardService::card($user->id);
        $owed = $card ? CreditCardService::owed($card) : 0.0;
        // what deferring costs, with the student's own debt or the limit
        $example = $owed > 0 ? $owed : CreditCardService::LIMIT;

        return view('bank.card', [
            'user' => $user,
            'card' => $card,
            'owed' => $owed,
            'available' => $card ? CreditCardService::available($card) : CreditCardService::LIMIT,
            'refusal' => $card ? null : CreditCardService::refusal($user),
            'example' => $example,
            // the minimum each month, or the same payment for a year (at the card's 20 %)
            'payoff' => ['minimum' => CreditCardService::payoff($example), 'year' => CreditCardService::payoff($example, CreditCardService::equalPayment($example, 12)),
                'year_payment' => CreditCardService::equalPayment($example, 12)],
            'statements' => $card ? CardStatement::where('card_id', $card->id)->orderByDesc('month')->limit(6)->get() : collect(),
            'movements' => $card ? CardMovement::where('card_id', $card->id)->orderByDesc('moved_at')->orderByDesc('id')->limit(20)->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['accept' => ['accepted']]);
        $card = CreditCardService::open(auth()->user());
        if (is_string($card)) {
            return back()->with('error', __('flash.' . $card));
        }

        return back()->with('success', e(__('flash.card_opened', ['limit' => zeds((float) $card->limit)])));
    }

    public function mode(Request $request)
    {
        $data = $request->validate(['mode' => ['required', 'in:' . implode(',', CreditCardService::MODES)], 'fixed' => ['nullable', 'numeric', 'min:0', 'max:100000']]);
        if ($why = CreditCardService::setMode(auth()->user(), $data['mode'], isset($data['fixed']) ? (float) $data['fixed'] : null)) {
            return back()->withInput()->with('error', __('flash.' . $why, ['min' => zeds(CreditCardService::MIN_FLOOR)]));
        }

        return back()->with('success', e(__('flash.card_mode_saved')));
    }
}
