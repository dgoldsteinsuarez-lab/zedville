<?php

namespace App\Http\Controllers;

use App\Models\Fraud;
use App\Services\FraudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The frauds the student gets (proposal «Fraudes», step 15; App\Services\FraudService):
 * the call's answer, and for the fake email its fake page, the report button
 * and what happened.
 */
class FraudController extends Controller
{
    /** The answer to the call on screen: what happened, for the call window. */
    public function call(Request $request, Fraud $fraud)
    {
        abort_unless((int) $fraud->user_id === (int) Auth::id() && $fraud->channel === 'call', 404);
        $data = $request->validate(['answer' => ['required', 'string', 'max:30']]);

        return response()->json(FraudService::lesson(FraudService::answer(Auth::user(), $fraud, $data['answer'])));
    }

    /** The fake page the email's link opens: it asks for nothing real. */
    public function fake(string $token)
    {
        $fraud = $this->own($token);
        if ($fraud->status !== 'open') {
            return redirect()->route('fraud.lesson', $token);
        }

        return view('fraud.fake', ['fraud' => $fraud]);
    }

    /** Its button: the student fell for it. */
    public function verify(string $token)
    {
        FraudService::answer(Auth::user(), $this->own($token), 'verify');

        return redirect()->route('fraud.lesson', $token);
    }

    /** The email's "report as fraud": well spotted. */
    public function report(string $token)
    {
        FraudService::answer(Auth::user(), $this->own($token), 'report');

        return redirect()->route('fraud.lesson', $token);
    }

    public function lesson(string $token)
    {
        $fraud = $this->own($token);
        abort_if($fraud->status === 'open', 404);

        return view('fraud.lesson', ['fraud' => $fraud, 'lesson' => FraudService::lesson($fraud)]);
    }

    private function own(string $token): Fraud
    {
        return Fraud::where('token', $token)->where('user_id', Auth::id())->where('channel', 'email')->firstOrFail();
    }
}
