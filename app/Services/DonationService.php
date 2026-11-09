<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Beneficiary;
use App\Models\Donation;
use App\Models\Mailbox;
use App\Models\Npo;
use App\Models\Transfer;
use App\Models\User;
use App\Support\Hybrid;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * The monthly donation (Daniel, 2026-11-08): every student - Junior, Rookie and
 * up, hybrid classes too - saves one of the city's charities as a beneficiary
 * in the bank and sets up a standing order to it ("tienen que aprender a
 * hacerlo en el banco"), of 4-6% of the salary ("yo haría entre 4 y 6% del
 * sueldo"). It can't be cancelled, only moved to another charity, and it runs
 * to the end of the school year: the next one, it's set up again. Each payment
 * brings a thank-you letter: a different animal of the shelter each month, a
 * child of the orphanage... Admins assign it like the Emergency Fund (Admin >
 * level > Monthly Donation); not set up in time, the usual City Hall fine
 * (ActivityFineService).
 */
class DonationService
{
    public const KIND = 'donation';              // transfers.kind
    public const ACTIVITY = 'Monthly Donation';  // the calendar event's title
    public const KEY = 'donation';               // mba_position.activity_key
    public const MIN = 0.04;
    public const MAX = 0.06;

    /** The least and the most a month's donation may be, in Ƶ. */
    public static function range(): array
    {
        $salary = MonthEndPenaltyService::salary();

        return [ceil(round(self::MIN * $salary * 100, 6)) / 100, floor(round(self::MAX * $salary * 100, 6)) / 100];
    }

    /** The student's standing donation, if they have one. */
    public static function active(int $userId): ?Transfer
    {
        return Transfer::where('user_id', $userId)->where('kind', self::KIND)->where('status', 'scheduled')->latest('id')->first();
    }

    /** The last one that ended, to keep or change when it's set up again. */
    public static function last(int $userId): ?Transfer
    {
        return Transfer::where('user_id', $userId)->where('kind', self::KIND)->where('status', 'done')->latest('id')->first();
    }

    /**
     * The last day of the school year a date is in (config academic_year_start_month):
     * a donation runs until then, and the next year the student sets it up again,
     * keeping or changing it (Daniel, 2026-11-08: "el próximo año, también les daría
     * un cambio o mantener, pero deberán hacer la actividad de transferencia periódica
     * nuevamente").
     */
    public static function yearEnd(Carbon $date): Carbon
    {
        $start = (int) config('zedville.academic_year_start_month', 4);
        $year = $date->month >= $start ? $date->year + 1 : $date->year;

        return Carbon::create($year, $start, 1, 0, 0, 0, $date->getTimezone())->subDay()->endOfDay();
    }

    /** A charity that takes donations, by its account number (dashes and spaces don't count). */
    public static function npo(?string $number): ?Npo
    {
        $digits = self::digits($number);

        return $digits === '' ? null : Npo::where('status', 1)->get()->first(fn (Npo $npo) => self::digits($npo->account_number) === $digits);
    }

    /** Whether a typed name is the charity's, in any of the city's languages. */
    public static function sameName(?string $typed, Npo $npo): bool
    {
        foreach (self::names($npo) as $name) {
            if (BankTransferService::sameName($typed, $name)) {
                return true;
            }
        }

        return false;
    }

    /** The charity's name in English, es, fr and de (resources/content-translations/<locale>/activities.php). */
    private static function names(Npo $npo): array
    {
        static $maps = [];
        $names = [$npo->name, (string) ct('activities', $npo->name)];
        foreach (['es', 'fr', 'de'] as $locale) {
            $file = resource_path("content-translations/{$locale}/activities.php");
            $maps[$locale] ??= is_file($file) ? require $file : [];
            $names[] = $maps[$locale][trim($npo->name)] ?? null;
        }

        return array_values(array_unique(array_filter($names)));
    }

    /** The charities the student has saved as beneficiaries. */
    public static function saved(int $userId): Collection
    {
        return Beneficiary::where('user_id', $userId)->orderBy('name')->get()
            ->filter(fn (Beneficiary $b) => self::npo($b->account_number) !== null)->values();
    }

    /**
     * Sets up the standing order: every month from $first to the end of the
     * school year. The first payment goes out at once when $first is today.
     *
     * @return array{ok: bool, error?: string, transfer?: Transfer}
     */
    public function start(User $user, BankAccount $bank, Npo $npo, float $amount, Carbon $first): array
    {
        [$min, $max] = self::range();
        if ($amount < $min || $amount > $max) {
            return ['ok' => false, 'error' => 'donation_range'];
        }
        if (self::active($user->id)) {
            return ['ok' => false, 'error' => 'donation_exists'];
        }

        $transfer = Transfer::create([
            'user_id' => $user->id,
            'sid' => $bank->sid,
            'from_account' => BankTransferService::FROM_LABELS[BankTransferService::CURRENT],
            'account_number' => trim((string) $npo->account_number),
            'beneficiary_name' => $npo->name,
            'amount' => round($amount, 2),
            'type' => 'recurring',
            'kind' => self::KIND,
            'status' => 'done',
            'start_date' => $first->copy()->startOfDay(),
            'end_date' => self::yearEnd($first),
            'frequency' => 'monthly',
        ]);
        // a recurring order stays scheduled after a run that failed (not enough money): it tries next month
        $result = app(BankTransferService::class)->schedule($transfer, $first->copy()->startOfDay());

        return $result + ['transfer' => $transfer->fresh()];
    }

    /** Moves the donation to another charity: the only change it allows. */
    public function moveTo(Transfer $donation, Npo $npo): void
    {
        $donation->update(['account_number' => trim((string) $npo->account_number), 'beneficiary_name' => $npo->name]);
    }

    /**
     * One month's donation, inside BankTransferService::execute (its
     * transaction): the money out of the current account, the Donation row and
     * the charity's letter.
     */
    public function pay(Transfer $transfer, User $sender, BankAccount $senderBank, float $amount): array
    {
        $npo = self::npo($transfer->account_number);
        if (!$npo) {
            return ['ok' => false, 'error' => 'charity_closed'];
        }
        if (LedgerService::lockedBalance($sender->id) < $amount) {
            return ['ok' => false, 'error' => 'insufficient_balance'];
        }

        LedgerService::post($sender->id, 'debit', 'Donation', $amount, 'Donation to ' . $npo->name,
            ['sid' => $senderBank->sid, 'bank_account_id' => $senderBank->id]);
        $donation = Donation::create(['user_id' => $sender->id, 'npo_name' => $npo->name, 'account_no' => trim((string) $npo->account_number), 'amount' => $amount]);
        $donation->update(['transaction_ref' => 'TXN-' . date('Y') . '-' . str_pad((string) $donation->id, 6, '0', STR_PAD_LEFT)]);

        app(ParticipationService::class)->award($sender->id, 'donation', $npo->name);
        $this->letter($sender, $npo, $amount, Donation::where('user_id', $sender->id)->where('npo_name', $npo->name)->count());

        return ['ok' => true];
    }

    /**
     * The charity's thank-you, in the reader's language when opened. $count:
     * which donation to this charity it is, so each month a different animal
     * or child writes (lang/<locale>/npo_letters.php).
     */
    private function letter(User $user, Npo $npo, float $amount, int $count): void
    {
        $data = ['npo' => self::category($npo), 'name' => $npo->name, 'amount' => $amount, 'story' => $count, 'month' => now()->format('Y-m')];

        Mailbox::create([
            'student_id' => $user->id,
            'sid' => $user->sid,
            'subject' => 'Thank you for your donation',
            'content' => Hybrid::without(fn () => view('mailbox_templates.npo-thanks', $data + ['user' => $user])->render()),
            'type' => 'primary',
            'template' => 'npo-thanks',
            'template_data' => $data,
            'read' => 0,
        ]);
    }

    /** The letters' group: animals, elderly, children, food, education, community or environment. */
    public static function category(Npo $npo): string
    {
        $category = strtolower(trim((string) $npo->category));

        return in_array($category, ['animals', 'elderly', 'children', 'food', 'education', 'community', 'environment'], true) ? $category : 'community';
    }

    private static function digits(?string $number): string
    {
        return (string) preg_replace('/\D/', '', (string) $number);
    }
}
