<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EasyActivity;
use App\Services\CurrencyExchangeService;
use App\Support\AdminLevel;
use App\Support\AssignmentScope;
use App\Support\ClassColor;
use App\Support\ClassLabel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Easy activities in Admin (Daniel, 2026-10-29: "Under easy activities, with
 * Engagement Badge, we can start adding activities in Admin, like this
 * transfer activity", then "Create in Admin a section for these [hybrid]
 * activities, also, the transfer, in a separate admin activity under
 * Engagement - Easy Activities"). Three sections, one screen each:
 *   - Engagement > Easy Activity: Transfer money to a friend;
 *   - Engagement > Exchange money (at the Money Factory, 2026-11-02);
 *   - Hybrid Classes > Language Activities (match the words).
 * An admin creates an activity and assigns it to classes, required or
 * optional, with dates - the same assignment as Substitute Goods
 * (EngagementActivityController): a mba_position row per class and the
 * matching event on the class calendar, which the Engagement badge counts.
 *
 * A super admin's activities are for every school; a school admin's only
 * for their own school.
 */
class EasyActivityController extends Controller
{
    private const ROUTES = [
        'transfer' => 'admin.engagement.easy-activities',
        'language' => 'admin.hybrid.activities',
        'money_factory' => 'admin.money-factory',
    ];

    private function school()
    {
        return Auth::user()->isSuperAdmin() ? session('selected_school') : Auth::user()->sid;
    }

    /** Classes of the selected school (all classes for a super admin with none selected). */
    private function classes()
    {
        return ClassLabel::classes($this->school());
    }

    /** Every school's activities and the admin's own school's. */
    private function visible()
    {
        $sid = $this->school();

        return EasyActivity::query()->when(!Auth::user()->isSuperAdmin() || $sid,
            fn ($q) => $q->where(fn ($q) => $q->whereNull('sid')->orWhere('sid', $sid)));
    }

    private function findVisible(int $id): EasyActivity
    {
        return $this->visible()->findOrFail($id);
    }

    /** Only the school that made it (or a super admin) changes or deletes it. */
    private function mayEdit(EasyActivity $activity): bool
    {
        return Auth::user()->isSuperAdmin() || ($activity->sid && (int) $activity->sid === (int) Auth::user()->sid);
    }

    private function back(EasyActivity $activity, string $message)
    {
        return redirect(route(self::ROUTES[$activity->section()]) . '#activity-' . $activity->id)->with('success', $message);
    }

    public function index()
    {
        return $this->page('transfer');
    }

    public function language()
    {
        return $this->page('language');
    }

    /** The Money Factory's activities: exchanging money, the banknote tracker... (Daniel, 2026-11-02). */
    public function moneyFactory()
    {
        return $this->page('money_factory');
    }

    private function page(string $section)
    {
        $classes = $this->classes();
        // the level being worked on (the menu by level): its activities and those for every level
        $activities = AdminLevel::scope($this->visible()->whereIn('type', EasyActivity::SECTIONS[$section]))->orderBy('title')->get();

        $assignments = DB::table('mba_position as mp')
            ->join('classes as c', 'c.id', '=', 'mp.cid')
            ->leftJoin('grades as cg', 'cg.id', '=', 'c.grade_id')
            ->whereIn('mp.activity_key', $activities->map->key())
            ->whereIn('mp.cid', $classes->pluck('id'))
            ->get(['mp.*', 'c.name as class_name', 'c.default_language as class_language', 'c.language_level as class_level', 'cg.name as class_grade_name'])
            ->each(function ($row) {
                $row->label = ClassLabel::make($row->class_grade_name, $row->class_name);
                // How many of the class's students have done it since it started.
                $students = DB::table('users')->where('grade', $row->cid)->where('role', 4)->pluck('id');
                $row->students = $students->count();
                $row->done = DB::table('activity_completions')
                    ->whereIn('student_id', $students)
                    ->where('activity_type', $row->activity_key)
                    ->when($row->start_date, fn ($q) => $q->where('completed_at', '>=', Carbon::parse($row->start_date)->startOfDay()))
                    ->distinct()
                    ->count('student_id');
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->groupBy(fn ($row) => EasyActivity::idFromKey($row->activity_key));

        // Suggested words for a word match: the bank's own verified names, in each language.
        $bankWords = [];
        foreach (array_keys(EasyActivity::LANGUAGES) as $language) {
            $bankWords[$language] = collect(['current', 'emergency', 'account_number', 'amount', 'reference_needed', 'st_balance', 'st_title', 'when_recurring', 'm_needs', 'm_wants'])
                ->map(fn ($key) => trans('bank_ui.' . $key, [], $language) . ' = ' . trans('bank_ui.' . $key, [], 'en'))
                ->implode("\n");
        }

        return view('admin.engagement.easy-activities', [
            'section' => $section,
            'route' => self::ROUTES[$section],
            'classes' => $classes,
            'activities' => $activities,
            'assignments' => $assignments,
            'types' => array_intersect_key(EasyActivity::TYPES, array_flip(EasyActivity::SECTIONS[$section])),
            'mayEdit' => fn (EasyActivity $activity) => $this->mayEdit($activity),
            'bankWords' => $bankWords,
        ]);
    }

    private function rules(?EasyActivity $activity = null): array
    {
        return [
            'title' => ['required', 'string', 'max:120', Rule::unique('easy_activities', 'title')->ignore($activity?->id)],
            'min_amount' => ['nullable', 'numeric', 'min:0.01', 'max:100000'],
            'language' => ['nullable', Rule::in(array_keys(EasyActivity::LANGUAGES))],
            'instructions' => ['nullable', 'string', 'max:500'],
            'pairs' => ['nullable', 'string', 'max:5000'],
            'currency' => ['nullable', Rule::in(array_keys(CurrencyExchangeService::ECB_RATES))],
            'reason' => ['nullable', 'string', 'max:300'],
            'levels' => ['nullable', 'array'],
            'levels.*' => ['integer'],
        ];
    }

    private const MESSAGES = ['title.unique' => 'There is already an activity with this title. Choose another title, or assign that activity.'];

    /** The fields of each type, from a validated form. */
    private function fields(string $type, array $data): array
    {
        if ($type === 'currency_exchange') {
            if (empty($data['currency']) || empty($data['min_amount'])) {
                throw ValidationException::withMessages(['currency' => 'Choose the currency and how many Zeds to exchange.']);
            }
            if ($data['min_amount'] < CurrencyExchangeService::MIN || $data['min_amount'] > CurrencyExchangeService::MAX) {
                throw ValidationException::withMessages(['min_amount' => 'The Money Factory exchanges ' . CurrencyExchangeService::MIN . ' to ' . CurrencyExchangeService::MAX . ' Zeds at a time.']);
            }

            return [
                'title' => $data['title'],
                'min_amount' => $data['min_amount'],
                'language' => null,
                'content' => ['currency' => $data['currency'], 'reason' => trim((string) ($data['reason'] ?? '')) ?: null],
            ];
        }
        if ($type !== 'word_match') {
            return ['title' => $data['title'], 'min_amount' => $data['min_amount'] ?? null, 'language' => null, 'content' => null];
        }

        if (empty($data['language'])) {
            throw ValidationException::withMessages(['language' => 'Choose the language the words are in.']);
        }
        // one pair per line: "word in the language = English meaning"
        $pairs = collect(preg_split('/\R/u', (string) ($data['pairs'] ?? '')))
            ->map(fn ($line) => array_map('trim', explode('=', $line, 2)))
            ->filter(fn ($pair) => count($pair) === 2 && $pair[0] !== '' && $pair[1] !== '')
            ->map(fn ($pair) => [mb_substr($pair[0], 0, 60), mb_substr($pair[1], 0, 60)])
            ->unique(fn ($pair) => mb_strtolower($pair[0]))
            ->values();
        if ($pairs->count() < 3 || $pairs->count() > 20) {
            throw ValidationException::withMessages(['pairs' => 'Write 3 to 20 pairs, one per line: word = English meaning.']);
        }
        if ($pairs->pluck(1)->map(fn ($meaning) => mb_strtolower($meaning))->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['pairs' => 'Two words have the same English meaning - each meaning must be different.']);
        }

        return [
            'title' => $data['title'],
            'min_amount' => null,
            'language' => $data['language'],
            'content' => ['instructions' => trim((string) ($data['instructions'] ?? '')) ?: null, 'pairs' => $pairs->all()],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate(['type' => ['required', Rule::in(array_keys(EasyActivity::TYPES))]] + $this->rules(), self::MESSAGES);

        abort_if(in_array($data['type'], EasyActivity::BUILT_IN, true), 422, 'This activity is built in: assign it, it has no new copies.');
        $activity = EasyActivity::create($this->fields($data['type'], $data) + [
            'type' => $data['type'],
            'levels' => AdminLevel::fromRequest($data['levels'] ?? null),
            'sid' => Auth::user()->isSuperAdmin() ? null : Auth::user()->sid,
            'created_by' => Auth::id(),
        ]);

        return $this->back($activity, '"' . $activity->title . '" created. Now assign it to a class.');
    }

    public function update(Request $request, $id)
    {
        $activity = $this->findVisible((int) $id);
        abort_unless($this->mayEdit($activity), 403);

        // a built-in activity keeps its name - its translations hang on it: only its levels change
        if (in_array($activity->type, EasyActivity::BUILT_IN, true)) {
            $data = $request->validate(['levels' => ['nullable', 'array'], 'levels.*' => ['integer']]);
            $activity->update(['levels' => AdminLevel::fromRequest($data['levels'] ?? null)]);

            return $this->back($activity, '"' . $activity->title . '" saved.');
        }

        $data = $request->validate($this->rules($activity), self::MESSAGES);
        $oldTitle = $activity->calendarTitle();
        $activity->update($this->fields($activity->type, $data) + ['levels' => AdminLevel::fromRequest($data['levels'] ?? null)]);

        // Its calendar events carry the title (that's how completions find them).
        if ($oldTitle !== $activity->calendarTitle()) {
            DB::table('calendar_events')->where('title', $oldTitle)->update(['title' => $activity->calendarTitle()]);
        }

        return $this->back($activity, '"' . $activity->title . '" saved.');
    }

    public function assign(Request $request, $id)
    {
        $activity = $this->findVisible((int) $id);
        $classes = $this->classes();

        $data = $request->validate([
            // One class, or "all classes" of this school / of every school.
            'cid' => ['required', function ($attribute, $value, $fail) use ($classes) {
                if (!AssignmentScope::isMany($value) && !$classes->pluck('id')->contains((int) $value)) {
                    $fail('Choose a class.');
                }
            }],
            'position' => ['required', 'in:required,optional'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        $targets = AssignmentScope::isMany($data['cid'])
            ? AssignmentScope::classes($data['cid'], $this->school())
            : $classes->where('id', (int) $data['cid']);
        // A school's own activity stays in that school.
        if ($activity->sid) {
            $targets = $targets->where('sid', $activity->sid);
        }

        if ($targets->isEmpty()) {
            return back()->with('error', 'There are no classes to assign this to.');
        }

        foreach ($targets as $class) {
            $this->assignTo($activity, $class, $data);
        }

        return $this->back($activity, '"' . $activity->title . '" assigned to ' . ($targets->count() === 1 ? ($targets->first()->label ?? $targets->first()->name) : $targets->count() . ' classes') . '.');
    }

    private function assignTo(EasyActivity $activity, object $class, array $data): void
    {
        DB::table('mba_position')->updateOrInsert(
            ['activity_key' => $activity->key(), 'cid' => $class->id],
            [
                'user_id' => Auth::id(),
                'sid' => $class->sid,
                'required' => $data['position'] === 'required' ? 1 : 0,
                'optional' => $data['position'] === 'optional' ? 1 : 0,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // `end` is exclusive on the calendar, so the last day is stored one day later.
        DB::table('calendar_events')->updateOrInsert(
            ['classId' => $class->id, 'title' => $activity->calendarTitle()],
            [
                'sid' => $class->sid,
                'description' => 'Easy activity: ' . $activity->title,
                'position' => $data['position'],
                'repeatActivity' => '0',
                'backgroundColor' => ClassColor::hex((int) $class->id),
                'borderColor' => ClassColor::hex((int) $class->id),
                'start' => Carbon::parse($data['start_date'])->startOfDay(),
                'end' => Carbon::parse($data['end_date'])->addDay()->startOfDay(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function unassign($assignmentId)
    {
        $assignment = DB::table('mba_position')
            ->where('id', $assignmentId)
            ->where('activity_key', 'like', 'easy\_%')
            ->whereIn('cid', $this->classes()->pluck('id'))
            ->first();
        abort_unless($assignment, 404);
        $activity = $this->findVisible((int) EasyActivity::idFromKey($assignment->activity_key));

        DB::table('calendar_events')->where('classId', $assignment->cid)->where('title', $activity->calendarTitle())->delete();
        DB::table('mba_position')->where('id', $assignment->id)->delete();

        return $this->back($activity, '"' . $activity->title . '" removed from that class.');
    }

    public function destroy($id)
    {
        $activity = $this->findVisible((int) $id);
        abort_unless($this->mayEdit($activity), 403);
        abort_if(in_array($activity->type, EasyActivity::BUILT_IN, true), 422, 'This activity is built in: remove it from classes instead.');

        // Students' completions stay: they're part of past badges.
        DB::table('calendar_events')->where('title', $activity->calendarTitle())->delete();
        DB::table('mba_position')->where('activity_key', $activity->key())->delete();
        $activity->delete();

        return redirect()->route(self::ROUTES[$activity->section()])->with('success', '"' . $activity->title . '" deleted.');
    }
}
