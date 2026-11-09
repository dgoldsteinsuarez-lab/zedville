<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CurrencyExchange;
use App\Models\EasyActivity;
use App\Services\BadgeCalculatorService;
use App\Services\CurrencyExchangeService;
use App\Services\EasyActivityService;
use App\Services\LedgerService;
use App\Services\MoneyFactory\Activities;
use App\Services\MoneyFactory\BanknoteJourney;
use App\Support\BankLevel;
use App\Support\Hybrid;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The Money Factory in Financial Square (Daniel, 2026-11-02: "anunciar que
 * Zedville es la capital del mismo país llamado Zedland. En la capital se
 * imprime el dinero ... en la página inicial, simplemente, explicar qué es el
 * dinero y por qué se imprime dinero"). The first page: what money is, why
 * it's printed and how a banknote is made, and the currency exchange with its
 * live board. Two pages apart: the money supply (M1, M2, M3 - for the
 * advanced levels) and what too little or too much money does, with charts.
 * A hybrid class is only informed, not told why ("para clases híbridas, no es
 * necesario explicar, solo informar").
 */
class MoneyFactoryController extends Controller
{
    public function index(Request $request, EasyActivityService $easy)
    {
        $user = $request->user();
        $account = BankAccount::where('student_id', $user->id)->first();

        return view('education.money-factory.index', [
            'board' => CurrencyExchangeService::board(),
            'hasAccount' => (bool) $account,
            'balance' => $account ? LedgerService::currentBalance($user->id) : 0.0,
            'tasks' => $easy->forStudent($user, 'currency_exchange'),
            'scene' => self::scene(BankLevel::advanced($user), Hybrid::on()),
            'journeyTask' => $easy->forStudent($user, 'mf_journey')->first(),
            // the other activities running for the class, each with its own page
            'activityTasks' => $easy->forStudent($user)->filter(fn ($activity) => isset(Activities::ALL[$activity->type]))->values(),
            'serial' => BanknoteJourney::serialFor((int) $user->id),
            'task' => (int) $request->query('task'),
            'history' => CurrencyExchange::where('user_id', $user->id)->latest('id')->limit(5)->get(),
            'advanced' => BankLevel::advanced($user),
            'hybrid' => Hybrid::on(),
            'money' => Money::style(),
            'now' => now()->getTimestamp(), // the board runs on the server's clock
        ]);
    }

    /**
     * The places of the Money Factory's picture (Daniel, 2026-11-02: "me gustaría
     * hacerlo más visual ... la información sobre el dinero puede darse tras pinchar
     * sobre una moneda ... el tablón de cambios de divisas, a la izquierda arriba, o
     * una factoría que hace el dinero ... si el cursor pasa por un sitio darle la
     * posibilidad de abrirlo"): where each is on the picture (% across, % down),
     * its icon, and what it opens - a window (what, why, how), a part of the page
     * or a page apart. The pages apart are for the levels that have them, not for
     * hybrid classes. Each mark sits on a corner of its place, not over it.
     */
    private const SPOTS = [
        'board' => [26, 11, '💱', 'money.exchange_title', '#exchange'],
        'coin' => [19, 72, '💰', 'money.what_title', 'what'],
        'factory' => [61, 34, '🏭', 'money.how_title', 'how'],
        'president' => [90, 60, '👩‍💼', 'money.president', 'why'],
        'scanner' => [54, 78, '🔎', 'money_tasks.journey.title', '#tracker'],
        'stall' => [72, 80, '🏷️', 'money.circulation_card', 'circulation'],
        'truck' => [66, 61, '🚚', 'money.enter_card', 'enter'],
        'vault' => [96, 79, '🔐', 'money.supply_card', 'supply'],
    ];

    /** The picture (public/images/money-factory/scene.*, or the drawing until there is one) and its places. */
    private static function scene(bool $advanced, bool $hybrid): array
    {
        $pages = ['circulation' => !$hybrid, 'enter' => $advanced && !$hybrid, 'supply' => $advanced && !$hybrid];
        $spots = [];
        foreach (self::SPOTS as $key => [$x, $y, $icon, $label, $open]) {
            if (isset($pages[$open]) && !$pages[$open]) {
                continue;
            }
            $spots[$key] = ['x' => $x, 'y' => $y, 'icon' => $icon, 'label' => __($label),
                'open' => isset($pages[$open]) ? route('education.money-factory.' . $open) : $open];
        }

        $image = collect(['webp', 'png', 'jpg'])->map(fn ($ext) => 'images/money-factory/scene.' . $ext)
            ->first(fn ($path) => is_file(public_path($path)));

        return ['spots' => $spots, 'image' => $image ? asset($image) : null];
    }

    public function supply()
    {
        return view('education.money-factory.money-supply');
    }

    public function circulation()
    {
        return view('education.money-factory.circulation');
    }

    /** How money gets into the economy, and politicians who print it (advanced, 2026-11-02). */
    public function enter()
    {
        return view('education.money-factory.how-money-enters');
    }

    public function exchange(Request $request)
    {
        $range = __('money.amount_range', ['min' => zeds(CurrencyExchangeService::MIN), 'max' => zeds(CurrencyExchangeService::MAX)]);
        $data = $request->validate([
            'currency' => ['required', Rule::in(array_keys(CurrencyExchangeService::ECB_RATES))],
            'amount' => ['required', 'numeric', 'min:' . CurrencyExchangeService::MIN, 'max:' . CurrencyExchangeService::MAX],
            'at' => ['nullable', 'integer'], // the second of the rate the student confirmed
        ], ['amount.*' => $range, 'currency.*' => __('money.error'), 'at.*' => __('money.error')]);

        $user = $request->user();
        if (!BankAccount::where('student_id', $user->id)->exists()) {
            return response()->json(['message' => __('money.no_account')], 422);
        }
        $at = isset($data['at']) ? Carbon::createFromTimestamp((int) $data['at']) : null;
        $exchange = CurrencyExchangeService::exchange($user, $data['currency'], round((float) $data['amount'], 2), $at);
        if (!$exchange) {
            return response()->json(['message' => __('money.not_enough')], 422);
        }

        return response()->json([
            'text' => __('money.done_text', [
                'foreign' => self::foreign($exchange->foreign_amount, $exchange->currency),
                'total' => zeds($exchange->zeds + $exchange->commission),
            ]),
            'balance' => LedgerService::currentBalance($user->id),
            'line' => [
                'date' => $exchange->created_at->locale(app()->getLocale())->isoFormat('D MMM'),
                'zeds' => zeds($exchange->zeds),
                'foreign' => self::foreign($exchange->foreign_amount, $exchange->currency),
            ],
            'done' => app(EasyActivityService::class)->forStudent($user, 'currency_exchange')->where('done', true)->pluck('id')->values(),
        ]);
    }

    /** Each stop's picture on the tracker. */
    private const STOP_ICONS = [
        'factory' => '🏭', 'central' => '🏛️', 'bank' => '🏦', 'machine' => '🏧', 'person' => '🧑', 'you' => '⭐',
        'bakery' => '🥖', 'supermarket' => '🛒', 'city-mall' => '🛍️', 'bus-station' => '🚌', 'restaurants' => '🍽️',
        'sports-center' => '⚽', 'gas-station' => '⛽',
    ];

    /**
     * The banknote tracker (Daniel, 2026-11-02): a serial number's month, stop by
     * stop. For the student's own note, the questions too (without the answers).
     */
    public function tracker(Request $request, EasyActivityService $easy)
    {
        $serial = BanknoteJourney::normalize($request->query('serial'));
        if (!$serial) {
            return response()->json(['message' => __('money_tasks.journey.invalid')], 422);
        }
        $user = $request->user();
        $own = $serial === BanknoteJourney::serialFor((int) $user->id);
        $task = $own ? $easy->forStudent($user, 'mf_journey')->first() : null;
        $stops = BanknoteJourney::stops($serial);

        return response()->json([
            'serial' => $serial,
            'title' => __('money_tasks.journey.journey_of', ['serial' => $serial]),
            'stops' => array_map(fn ($stop, $i) => [
                'icon' => self::STOP_ICONS[$stop['key']] ?? self::STOP_ICONS[$stop['kind']] ?? '•',
                'kind' => $stop['kind'],
                'name' => BanknoteJourney::label($stop),
                'date' => $stop['at']->copy()->locale(app()->getLocale())->isoFormat('D MMM, HH:mm'),
                'stayed' => $stop['kind'] === 'you' ? __('money_tasks.journey.now') : __('money_tasks.journey.stayed', ['time' => BanknoteJourney::duration($stop['hours'])]),
                // how it went on to the next stop: "Paid a teacher for a private lesson."
                'passed' => BanknoteJourney::passed($stop, $stops[$i + 1] ?? null),
            ], $stops, array_keys($stops)),
            'average' => __('money_tasks.journey.average', ['time' => BanknoteJourney::average($stops)]),
            'questions' => $task ? array_map(fn ($q) => ['text' => $q['text'], 'options' => $q['options']], BanknoteJourney::questions($serial)) : [],
            'done' => (bool) ($task->done ?? false),
        ]);
    }

    /** The answers about the student's own banknote. */
    public function trackerAnswers(Request $request, EasyActivityService $easy)
    {
        $data = $request->validate(['answers' => ['required', 'array'], 'answers.*' => ['nullable', 'integer']]);
        $result = $easy->journeyAnswered($request->user(), $data['answers']);
        $wrong = count(array_filter($result['right'], fn ($ok) => !$ok));

        return response()->json($result + [
            'message' => $wrong ? trans_choice('money_tasks.some_wrong', $wrong, ['count' => $wrong]) : __('money_tasks.all_right'),
        ]);
    }

    /**
     * A Money Factory activity's page (Daniel, 2026-11-02: the ideas "en Admin,
     * bajo Money Factory, pero los alumnos lo harán en Educational Finance
     * Department"): what to look at, and the student's own questions.
     */
    public function activity(Request $request, EasyActivityService $easy, string $slug)
    {
        $type = Activities::typeOfSlug($slug) ?? abort(404);
        $user = $request->user();
        $activity = Activities::ALL[$type];

        return view('education.money-factory.activity', [
            'type' => $type,
            'key' => $activity['key'],
            'slug' => $slug,
            'icon' => $activity['icon'],
            'advanced' => $activity['advanced'],
            'title' => EasyActivity::where('type', $type)->first()?->studentTitle() ?? __('money_tasks.' . $activity['key'] . '.title'),
            'task' => $easy->forStudent($user, $type)->first(),
            'stage' => Activities::stage($type, (int) $user->id),
            'questions' => Activities::forPage(Activities::questions($type, (int) $user->id)),
            'hybrid' => Hybrid::on(),
            'ownCounts' => $this->ownCounts($request),
        ]);
    }

    /**
     * Whether an activity done on the student's own counts towards Gold: in a
     * month with no Optional activity on their class calendar (Daniel,
     * 2026-11-02: "Oro si hace otras actividades no asignadas, pero si hay
     * opcionales, no").
     */
    private function ownCounts(Request $request): bool
    {
        return app(BadgeCalculatorService::class)->getOptionalAssigned((int) $request->user()->id, now()->month, now()->year) === 0;
    }

    /** The answers to an activity's questions: right or not, why (once right), and done. */
    public function activityAnswers(Request $request, EasyActivityService $easy, string $slug)
    {
        $type = Activities::typeOfSlug($slug) ?? abort(404);
        $data = $request->validate(['answers' => ['required', 'array']]);
        $result = $easy->quizAnswered($request->user(), $type, $data['answers']);
        $wrong = count(array_filter($result['right'], fn ($ok) => !$ok));
        // a hybrid class is only told right or not ("no es necesario explicar, solo informar")
        $why = Hybrid::on() ? [] : array_map(fn ($q, $ok) => $ok ? ($q['why'] ?? null) : null,
            Activities::questions($type, (int) $request->user()->id), $result['right']);
        $countsForGold = $result['own'] && $this->ownCounts($request);

        return response()->json($result + [
            'why' => $why,
            'message' => $wrong ? trans_choice('money_tasks.some_wrong', $wrong, ['count' => $wrong])
                : ($result['done'] ? __('money_tasks.all_right') : __($countsForGold ? 'money_tasks.all_right_own' : 'money_tasks.all_right_practice')),
        ]);
    }

    /** "94.96 USD" the way the student's language writes numbers. */
    public static function foreign(float $amount, string $code): string
    {
        return CurrencyExchangeService::amount($amount, $code);
    }
}
