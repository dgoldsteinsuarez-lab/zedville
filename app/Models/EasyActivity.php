<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * An easy activity (2026-10-29): a short task that counts for the Engagement
 * badge. Its type says what the student does and how the app sees it's done
 * (App\Services\EasyActivityService). Three admin sections make them:
 *   - Engagement > Easy Activity: Transfer money to a friend (bank_transfer);
 *   - Money Factory (currency_exchange: change Zeds into another currency for a
 *     reason; mf_*: the Money Factory's activities, e.g. where a banknote has
 *     been - 2026-11-02), each for the levels it is for (`levels`);
 *   - Hybrid Classes > Language Activities (word_match: match words in the
 *     language the class learns with their English meaning).
 * The title is English, like every activity name in the database; students
 * see it in their language through resources/content-translations/<locale>/activities.php.
 */
class EasyActivity extends Model
{
    /** What an admin can choose - each type needs code that sees it's done. */
    public const TYPES = [
        'bank_transfer' => [
            'admin' => 'Bank: transfer money to another student',
            'title' => 'Transfer money to a friend',
        ],
        'word_match' => [
            'admin' => 'Language: match the words with their English meaning',
            'title' => 'Bank words',
        ],
        'currency_exchange' => [
            'admin' => 'Money Factory: exchange Zeds into another currency',
            'title' => 'Exchange money for a trip',
        ],
        // the built-in Money Factory activities: what the admin reads about each (`about`)
        'mf_journey' => [
            'admin' => 'Money Factory: where has this banknote been?',
            'title' => 'Where has this banknote been?',
            'about' => 'Each student gets their own banknote (its serial number) and puts it in the Money Factory\'s banknote tracker. They see its month - printed, the Central Bank, Universal Bank, a cash machine, then shops and people - and answer four simple questions: how many hands, where it stayed longest, how long at one stop, who gave it to them.',
        ],
        'mf_fake' => [
            'admin' => 'Money Factory: real or fake banknote?',
            'title' => 'Real or fake banknote?',
            'about' => 'Three sets of three Zed banknotes; in each set one is fake. With the buttons Feel, Look against the light and Tilt - the euro\'s real three checks, from the European Central Bank - the student finds it, then says what to do with a banknote they think is fake.',
        ],
        'mf_history' => [
            'admin' => 'Money Factory: the story of money',
            'title' => 'The story of money',
            'about' => 'Six cards to put in order, oldest first: swapping things and shells, the first coins, paper money, cards, euro banknotes, paying with a phone. Then three simple questions (where the first coins and paper money were made, what is hard about swapping). After checking, each card says when.',
        ],
        'mf_prices' => [
            'admin' => 'Money Factory: how much is it in other currencies?',
            'title' => 'How much is it in other currencies?',
            'about' => 'A Zedville price (an ice cream, a comic, a cinema ticket, a football) and the board\'s rates: about how much it is in one currency and in Japanese yen (the wrong answers are ten times too big or too small, so an estimate is enough), which number is biggest and why.',
        ],
        'mf_roundtrip' => [
            'admin' => 'Money Factory: back from the trip (buy and sell prices)',
            'title' => 'Back from the trip: buy and sell prices',
            'about' => 'An exchange office sells a currency 2% above the board\'s rate and buys it back 2% below. The student works out what the trip money costs and what the leftover brings back, and answers why the office earns money and how to change money cheaply.',
        ],
        'mf_bigmac' => [
            'admin' => 'Money Factory: the Big Mac index',
            'title' => 'The Big Mac index',
            'about' => 'The Economist\'s Big Mac prices (July 2026) changed into Zeds with the board\'s rates: the student works out one country\'s price, finds the most expensive, and says whether the Zed is undervalued against the dollar and overvalued against the yen.',
        ],
        'mf_headlines' => [
            'admin' => 'Money Factory: headlines that move the Zed',
            'title' => 'Headlines that move the Zed',
            'about' => 'Six news headlines (interest rates, tourists, exports, a safe economy, political chaos, printing money, imports): does the Zed go up or down?',
        ],
        'mf_central_bank' => [
            'admin' => 'Money Factory: central bank for a day',
            'title' => 'Central bank for a day',
            'about' => 'The student runs the Central Bank of Zedland for four years, aiming at prices rising about 2% a year: prices rising too fast, on target, a crisis, and a government that wants money to pay its bills. Each year: inflation, unemployment and growth, and a decision.',
        ],
        'mf_print_or_cut' => [
            'admin' => 'Money Factory: print money or cut spending?',
            'title' => 'Print money or cut spending?',
            'about' => 'Zedland\'s government spends 120 million and collects 100 million: the deficit, borrowing with bonds, what printing the gap does, why it tempts politicians, and the EU rule that forbids the European Central Bank to finance governments (Article 123).',
        ],
    ];

    /** The admin sections and the types each one makes. */
    public const SECTIONS = [
        'transfer' => ['bank_transfer'],
        'language' => ['word_match'],
        'money_factory' => ['currency_exchange', 'mf_journey', 'mf_fake', 'mf_history', 'mf_prices', 'mf_roundtrip', 'mf_bigmac', 'mf_headlines', 'mf_central_bank', 'mf_print_or_cut'],
    ];

    /** Built-in Money Factory activities: one each, made by a migration - admins assign them, they don't write them. */
    public const BUILT_IN = ['mf_journey', 'mf_fake', 'mf_history', 'mf_prices', 'mf_roundtrip', 'mf_bigmac', 'mf_headlines', 'mf_central_bank', 'mf_print_or_cut'];

    /** Languages a word match can teach (the class languages besides English). */
    public const LANGUAGES = ['es' => 'Spanish', 'fr' => 'French', 'de' => 'German'];

    /** Calendar titles start with this (see App\Support\CalendarTitle). */
    public const CALENDAR_PREFIX = 'easy-';

    protected $fillable = ['type', 'title', 'min_amount', 'language', 'content', 'levels', 'sid', 'created_by'];

    // levels: the grade levels it is for, null for every level (App\Support\AdminLevel)
    protected $casts = ['min_amount' => 'float', 'content' => 'array', 'levels' => 'array'];

    public static function sectionOf(string $type): string
    {
        foreach (self::SECTIONS as $section => $types) {
            if (in_array($type, $types, true)) {
                return $section;
            }
        }

        return 'transfer';
    }

    public function section(): string
    {
        return self::sectionOf($this->type);
    }

    /**
     * activity_completions.activity_type and mba_position.activity_key. The
     * key is the only link: mba_position.activity_id belongs to the spending
     * activities (a foreign key to `activities`).
     */
    public function key(): string
    {
        return 'easy_' . $this->id;
    }

    /** The activity id in an "easy_<id>" key. */
    public static function idFromKey(?string $key): ?int
    {
        return preg_match('/^easy_(\d+)$/', (string) $key, $m) ? (int) $m[1] : null;
    }

    public function calendarTitle(): string
    {
        return self::CALENDAR_PREFIX . $this->title;
    }

    public function studentTitle(): string
    {
        return ct('activities', $this->title);
    }

    /** Where the student does it. */
    public function studentUrl(): string
    {
        return match ($this->type) {
            'bank_transfer' => route('bank.transfer', ['to' => 'friend']),
            'word_match' => route('easy.words', $this),
            'currency_exchange' => route('education.money-factory', ['task' => $this->id]) . '#exchange',
            'mf_journey' => route('education.money-factory', ['task' => $this->id]) . '#tracker',
            default => route('education.money-factory.activity', \App\Services\MoneyFactory\Activities::ALL[$this->type]['slug']),
        };
    }

    /** What the student does, step by step, in their language. */
    public function steps(): array
    {
        return match ($this->type) {
            'bank_transfer' => [
                __('engagement.easy_transfer_step1'),
                $this->min_amount
                    ? __('engagement.easy_transfer_step2_min', ['amount' => zeds($this->min_amount)])
                    : __('engagement.easy_transfer_step2'),
                __('engagement.easy_transfer_step3'),
            ],
            'word_match' => array_values(array_filter([
                __('engagement.words_intro'),
                $this->content['instructions'] ?? null,
            ])),
            // the reason, then the task - a hybrid class only the task (Daniel,
            // 2026-11-02: "para clases híbridas, no es necesario explicar, solo informar")
            'currency_exchange' => array_values(array_filter([
                \App\Support\Hybrid::on() ? null : ct('activities', $this->content['reason'] ?? null),
                __('money.task_go'),
                $this->exchangeTask(),
            ])),
            // the student's own banknote (Daniel, 2026-11-02: "Puedes darle un billete y decirles que lo pongan en una sección de Money Factory")
            'mf_journey' => [
                __('money_tasks.journey.task_note', ['serial' => \App\Services\MoneyFactory\BanknoteJourney::serialFor((int) auth()->id())]),
                __('money_tasks.journey.task_go'),
            ],
            // the other Money Factory activities: what to do, and where
            default => [
                __('money_tasks.' . \App\Services\MoneyFactory\Activities::ALL[$this->type]['key'] . '.task'),
                __('money_tasks.task_go'),
            ],
        };
    }

    /** An exchange activity's currency (ISO code). */
    public function currency(): ?string
    {
        return $this->content['currency'] ?? null;
    }

    /** "Exchange Ƶ 100.00 into US dollars." */
    public function exchangeTask(): string
    {
        return __('money.task_text', ['amount' => zeds($this->min_amount), 'into' => __('money.into.' . $this->currency())]);
    }

    /** A word match's pairs: [word in the language, English meaning]. */
    public function pairs(): array
    {
        return array_values($this->content['pairs'] ?? []);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type]['admin'] ?? $this->type;
    }

    /**
     * The easy activities running today for a class, each with `position`
     * (required/optional) and `from`/`until` from its assignment.
     */
    public static function runningFor(?int $classId, ?string $type = null): Collection
    {
        if (!$classId) {
            return collect();
        }

        $assignments = DB::table('mba_position')
            ->where('cid', $classId)
            ->where('activity_key', 'like', 'easy\_%')
            ->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->get();

        $activities = self::whereIn('id', $assignments->map(fn ($row) => self::idFromKey($row->activity_key))->filter())
            ->when($type, fn ($q) => $q->where('type', $type))
            ->get()
            ->keyBy('id');

        return $assignments
            ->filter(fn ($row) => isset($activities[self::idFromKey($row->activity_key)]))
            ->map(function ($row) use ($activities) {
                $activity = clone $activities[self::idFromKey($row->activity_key)];
                $activity->position = $row->required ? 'required' : 'optional';
                $activity->from = $row->start_date;
                $activity->until = $row->end_date;

                return $activity;
            })
            ->sortBy('id')
            ->values();
    }
}
