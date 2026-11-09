<?php

namespace App\Services\MoneyFactory;

use Carbon\Carbon;

/**
 * Where has this banknote been? (Daniel, 2026-11-02: "que descubran cuánto ha
 * viajado, por qué manos ha pasado un billete en el último mes. Puedes darle un
 * billete y decirles que lo pongan en una sección de Money Factory ... Luego,
 * deberán responder a preguntas sencillas, por cuántas manos ha pasado o cuánto
 * tiempo ha estado en una mano").
 *
 * Each serial number has its own month: printed and checked at the Money
 * Factory, to the Central Bank and Universal Bank, out of a cash machine, then
 * from hand to hand - people and shops of Zedville - until it reached the
 * student. The same serial always tells the same story (a seeded generator),
 * so nothing is stored and every student's note is different.
 */
class BanknoteJourney
{
    /** Shops (their names: city.buildings.*) and people (money_tasks.journey.people.*) a note can pass through. */
    private const SHOPS = ['bakery', 'supermarket', 'city-mall', 'bus-station', 'restaurants', 'sports-center', 'gas-station'];
    private const PEOPLE = ['bus_driver', 'baker', 'nurse', 'neighbour', 'tourist', 'taxi_driver', 'musician', 'grandmother', 'teacher', 'footballer'];

    /** "zv12345678", "ZV-1234 5678" → "ZV 1234 5678" (null if it isn't a serial). */
    public static function normalize(?string $serial): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $serial);
        if (!preg_match('/^\s*zv/i', (string) $serial) || strlen($digits) !== 8) {
            return null;
        }

        return 'ZV ' . substr($digits, 0, 4) . ' ' . substr($digits, 4);
    }

    /** The banknote a student is given. */
    public static function serialFor(int $studentId): string
    {
        $digits = str_pad((string) (crc32('zedville-note:' . $studentId) % 100000000), 8, '0', STR_PAD_LEFT);

        return 'ZV ' . substr($digits, 0, 4) . ' ' . substr($digits, 4);
    }

    /**
     * The note's month, oldest first: each stop with its kind (factory, central,
     * bank, machine, shop, person, you), its key, when it arrived and how many
     * hours it stayed. The hands are the shops and people after the machine.
     *
     * How long a hand keeps it (Daniel, 2026-11-02: "calculate how much really
     * does money stay in someone hands"): nobody can see it directly (Banque de
     * France), but a UK estimate from the quantity of money puts it at about 147
     * hands a year - every 2 to 3 days - and a £20 note at 2,328 hands in 113
     * months, every day and a half (The Guardian / OnStride). So a person keeps
     * it 6 hours to 4½ days (2½ on average), a shop gives it back as change
     * within hours, and a month sees 8 to 11 hands. A shop only gets it from a
     * person, and gives it to a person.
     */
    public static function stops(string $serial, ?Carbon $now = null): array
    {
        $rand = new SeededRandom(crc32($serial));
        $stops = [
            ['kind' => 'factory', 'key' => 'factory', 'hours' => 48],
            ['kind' => 'central', 'key' => 'central', 'hours' => $rand->between(48, 96)],
            ['kind' => 'bank', 'key' => 'bank', 'hours' => $rand->between(24, 48)],
            ['kind' => 'machine', 'key' => 'machine', 'hours' => $rand->between(2, 20)],
        ];

        // 8 to 11 hands, each a different length of time, within the month (30 days)
        $shops = $rand->shuffle(self::SHOPS);
        $people = $rand->shuffle(self::PEOPLE);
        $used = [];
        $previous = 'machine';
        $left = 30 * 24 - array_sum(array_column($stops, 'hours'));
        for ($i = 0, $n = $rand->between(8, 11); $i < $n; $i++) {
            $shop = $previous === 'person' && $rand->between(0, 1) === 0;
            $shop = ($shop && $shops) || !$people;
            $hours = $shop ? $rand->between(1, 14) : $rand->between(6, 108);
            if ($hours > $left - ($n - $i - 1) * 2) {
                // the month is nearly over: shorter stays, and no more than 8 hands
                $hours = max(1, intdiv($left, max(1, $n - $i)));
                $n = max($i + 1, min($n, 8));
            }
            while (in_array($hours, $used, true)) {
                $hours++;
            }
            $left -= $hours;
            $used[] = $hours;
            $stops[] = $shop
                ? ['kind' => 'shop', 'key' => array_shift($shops), 'hours' => $hours]
                : ['kind' => 'person', 'key' => array_shift($people), 'hours' => $hours];
            $previous = $shop ? 'shop' : 'person';
        }

        // dated backwards from now: the student has it now
        $at = ($now ?? now())->copy()->startOfHour()->subHours(array_sum(array_column($stops, 'hours')));
        foreach ($stops as &$stop) {
            $stop['at'] = $at->copy();
            $at->addHours($stop['hours']);
        }
        unset($stop);
        $stops[] = ['kind' => 'you', 'key' => 'you', 'hours' => 0, 'at' => $at->copy()];

        return $stops;
    }

    /** The hands it passed through after the cash machine (shops and people). */
    public static function hands(array $stops): array
    {
        return array_values(array_filter($stops, fn ($stop) => in_array($stop['kind'], ['shop', 'person'], true)));
    }

    /** A stop's name in the student's language. */
    public static function label(array $stop): string
    {
        return match ($stop['kind']) {
            'shop' => __('city.buildings.' . $stop['key']),
            'person' => __('money_tasks.journey.people.' . $stop['key']),
            default => __('money_tasks.journey.places.' . $stop['key']),
        };
    }

    /** "5 hours", "3 days" - hours under a day, else whole days. */
    public static function duration(int $hours): string
    {
        return $hours < 24
            ? trans_choice('money_tasks.journey.hours', $hours, ['count' => $hours])
            : trans_choice('money_tasks.journey.days', (int) round($hours / 24), ['count' => (int) round($hours / 24)]);
    }

    /**
     * How a stop passed the note on to the next one (Daniel, 2026-11-02: "I guess
     * that the street musician paid the teacher"): the banks pass it along, someone
     * takes it out of the cash machine, a person spends it in a shop or pays
     * another person for something, a shop gives it as change. Null for "you".
     */
    public static function passed(array $stop, ?array $next): ?string
    {
        if (!$next) {
            return null;
        }

        return match (true) {
            in_array($stop['kind'], ['factory', 'central', 'bank'], true) => __('money_tasks.journey.passed.' . $stop['key']),
            $stop['kind'] === 'machine' => __('money_tasks.journey.took_out', ['who' => self::label($next)]),
            $next['kind'] === 'you' => __($stop['kind'] === 'shop' ? 'money_tasks.journey.to_you_change' : 'money_tasks.journey.to_you'),
            $stop['kind'] === 'shop' => __('money_tasks.journey.change_to', ['to' => __('money_tasks.journey.to.' . $next['key'])]),
            $next['kind'] === 'shop' => __('money_tasks.journey.spent.' . $next['key']),
            default => __('money_tasks.journey.paid.' . $next['key']),
        };
    }

    /** "1 day": how long, on average, the note stayed in each hand. */
    public static function average(array $stops): string
    {
        $hands = self::hands($stops);

        return self::duration((int) round(array_sum(array_column($hands, 'hours')) / max(1, count($hands))));
    }

    /**
     * The simple questions about the note's month, each with its options and
     * the right one: how many hands, where longest, how long with one of
     * them, who gave it to the student. Options in a fixed order per note.
     */
    public static function questions(string $serial): array
    {
        $stops = self::stops($serial);
        $hands = self::hands($stops);
        $rand = new SeededRandom(crc32('questions:' . $serial));
        $n = count($hands);

        $longest = collect($hands)->sortByDesc('hours')->first();
        $others = collect($rand->shuffle($hands))->reject(fn ($h) => $h['key'] === $longest['key'])->take(3)->all();

        $asked = $hands[1];
        $times = [self::duration($asked['hours'])];
        foreach ($asked['hours'] < 24 ? [$asked['hours'] + 6, max(1, $asked['hours'] - 4), $asked['hours'] + 30, 70, 2] : [$asked['hours'] + 30, $asked['hours'] + 55, max(5, $asked['hours'] - 26), 6, 100] as $hours) {
            $text = self::duration($hours);
            if (!in_array($text, $times, true) && count($times) < 4) {
                $times[] = $text;
            }
        }

        $giver = $hands[$n - 1];
        $notGiver = collect($rand->shuffle($hands))->reject(fn ($h) => $h['key'] === $giver['key'])->take(3)->all();

        return [
            self::ask(__('money_tasks.journey.q_hands'), array_map('strval', [$n, $n - 1, $n + 1, $n + 2]), $rand),
            self::ask(__('money_tasks.journey.q_longest'), array_map([self::class, 'label'], array_merge([$longest], $others)), $rand),
            self::ask(__('money_tasks.journey.q_time', ['holder' => self::label($asked)]), $times, $rand),
            self::ask(__('money_tasks.journey.q_from'), array_map([self::class, 'label'], array_merge([$giver], $notGiver)), $rand),
        ];
    }

    /** A question whose right option is the first given; the options are mixed. */
    private static function ask(string $text, array $options, SeededRandom $rand): array
    {
        $order = $rand->shuffle(array_keys($options));

        return ['text' => $text, 'options' => array_map(fn ($i) => $options[$i], $order), 'answer' => array_search(0, $order, true)];
    }
}
