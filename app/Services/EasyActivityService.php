<?php

namespace App\Services;

use App\Models\EasyActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Easy activities (Engagement Badge) that a student completes by doing
 * something in the city, e.g. "Transfer money to a friend". The completion
 * goes through ActivityCompletionService, so it points at the class's
 * calendar event and counts as required or optional for the badge.
 */
class EasyActivityService
{
    public function __construct(private ActivityCompletionService $completions)
    {
    }

    /**
     * A student sent money to a classmate's account (BankTransferService):
     * finishes their class's running "transfer money to a friend" activities
     * when the amount is at least the activity's minimum and they wrote a
     * reference (Daniel, 2026-10-29: "the student needs to also write
     * something in Asunto").
     */
    public function transferMade(User $sender, int $recipientId, float $amount, ?string $memo = null): void
    {
        if ($recipientId === (int) $sender->id || trim((string) $memo) === '') {
            return;
        }

        foreach (EasyActivity::runningFor($sender->grade, 'bank_transfer') as $activity) {
            if ($activity->min_amount && round($amount, 2) < round($activity->min_amount, 2)) {
                continue;
            }
            if ($this->isDone((int) $sender->id, $activity)) {
                continue;
            }

            $this->completions->complete((int) $sender->id, $activity->key(), $activity->calendarTitle());
        }
    }

    /**
     * A word match finished: every word with its English meaning (answers in
     * the order of the words). Counts when the activity runs for the
     * student's class; outside its dates it was practice.
     *
     * @return array{ok: bool, counted?: bool}
     */
    public function wordsMatched(User $student, EasyActivity $activity, array $answers): array
    {
        $pairs = $activity->pairs();
        if (!$pairs || count($answers) !== count($pairs)) {
            return ['ok' => false];
        }
        foreach ($pairs as $i => [$word, $meaning]) {
            if (mb_strtolower(trim((string) ($answers[$i] ?? ''))) !== mb_strtolower($meaning)) {
                return ['ok' => false];
            }
        }

        $running = EasyActivity::runningFor($student->grade, 'word_match')->firstWhere('id', $activity->id);
        if (!$running) {
            return ['ok' => true, 'counted' => false];
        }
        if (!$this->isDone((int) $student->id, $running)) {
            $this->completions->complete((int) $student->id, $running->key(), $running->calendarTitle());
        }

        return ['ok' => true, 'counted' => true];
    }

    /**
     * A student exchanged Zeds at the Money Factory (CurrencyExchangeService):
     * finishes their class's running exchange activities in that currency, for
     * at least the Zeds the activity asks.
     */
    public function exchangeMade(User $student, string $currency, float $zeds): void
    {
        foreach (EasyActivity::runningFor($student->grade, 'currency_exchange') as $activity) {
            if ($activity->currency() !== $currency || round($zeds, 2) < round((float) $activity->min_amount, 2)) {
                continue;
            }
            if (!$this->isDone((int) $student->id, $activity)) {
                $this->completions->complete((int) $student->id, $activity->key(), $activity->calendarTitle());
            }
        }
    }

    /** The questions about the student's own banknote answered (Money Factory, banknote tracker). */
    public function journeyAnswered(User $student, array $answers): array
    {
        return $this->quizAnswered($student, 'mf_journey', $answers);
    }

    /**
     * A Money Factory activity's questions answered (the banknote tracker or
     * one of App\Services\MoneyFactory\Activities): which answers are right,
     * and - all right while the activity runs for the class - it's done.
     * All right while it doesn't, it was done on the student's own (`own`):
     * that counts towards Gold in a month without Optional activities.
     *
     * @return array{right: array<int, bool>, done: bool, own: bool}
     */
    public function quizAnswered(User $student, string $type, array $answers): array
    {
        $questions = $type === 'mf_journey'
            ? \App\Services\MoneyFactory\BanknoteJourney::questions(\App\Services\MoneyFactory\BanknoteJourney::serialFor((int) $student->id))
            : \App\Services\MoneyFactory\Activities::questions($type, (int) $student->id);
        $right = \App\Services\MoneyFactory\Activities::grade($questions, $answers);

        $done = false;
        $own = false;
        if (!in_array(false, $right, true)) {
            foreach (EasyActivity::runningFor($student->grade, $type) as $activity) {
                if (!$this->isDone((int) $student->id, $activity)) {
                    $this->completions->complete((int) $student->id, $activity->key(), $activity->calendarTitle());
                }
                $done = true;
            }
            if (!$done) {
                $this->completions->completeOwn((int) $student->id, $type);
                $own = true;
            }
        }

        return ['right' => $right, 'done' => $done, 'own' => $own];
    }

    /** Done since this run of the activity started (not again next month). */
    public function isDone(int $studentId, EasyActivity $activity): bool
    {
        return DB::table('activity_completions')
            ->where('student_id', $studentId)
            ->where('activity_type', $activity->key())
            ->when($activity->from, fn ($q) => $q->where('completed_at', '>=', Carbon::parse($activity->from)->startOfDay()))
            ->exists();
    }

    /** The student's running easy activities, each with `done`. */
    public function forStudent(?User $user, ?string $type = null): Collection
    {
        if (!$user) {
            return collect();
        }

        // each() stops at a callback returning false: an arrow function here returned
        // the first activity not done yet, so every one after it stayed "not done" and
        // on the board (Daniel, 2026-11-03: "esta actividad la hice ... y no desapareció").
        return EasyActivity::runningFor($user->grade, $type)
            ->each(function ($activity) use ($user) {
                $activity->done = $this->isDone((int) $user->id, $activity);
            });
    }
}
