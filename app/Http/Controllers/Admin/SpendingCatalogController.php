<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\WantsPlan;
use App\Models\WantsPlanChoice;
use App\Services\ShoppingList;
use App\Services\WantsPlanService;
use App\Support\AdminLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Spending Activity in Admin (Daniel, 2026-10-31): the shopping list and
 * budget of each Spending Activity, and the plans students choose from each
 * month. Both are shared by every school - Super Admin only.
 */
class SpendingCatalogController extends Controller
{
    private const LANGS = ['es', 'fr', 'de'];

    // ── Shopping lists ──────────────────────────────────────────────

    public function lists()
    {
        // the level being worked on (the menu by level): its activities and those for every level
        $activities = AdminLevel::scope(Activity::whereIn('type', array_keys(Activity::SLOTS)))->orderBy('type')->orderBy('number')->get();
        $products = DB::table('products')->orderBy('type')->orderBy('price')->get(['product_name', 'type', 'price']);
        $shops = $products->pluck('type')->unique()->values();

        return view('admin.spending.lists', compact('activities', 'products', 'shops') + ['groups' => ShoppingList::GROUPS]);
    }

    public function saveList(Request $request, ?Activity $activity = null)
    {
        $data = $request->validate([
            'type' => ['required', 'in:high,low'],
            'title' => ['required', 'string', 'max:120'],
            'mission' => ['required', 'string', 'max:1000'],
            'destination' => ['required', 'string', 'max:120'],
            'budget' => ['required', 'numeric', 'min:1', 'max:100000'],
            'lines' => ['required', 'array', 'min:1', 'max:8'],
            'lines.*.qty' => ['required', 'integer', 'min:1', 'max:10'],
            'lines.*.kind' => ['required', 'in:group,products,store'],
            'lines.*.group' => ['nullable', 'in:' . implode(',', ShoppingList::GROUPS)],
            'lines.*.products' => ['nullable', 'array'],
            'lines.*.products.*' => ['string'],
            'lines.*.store' => ['nullable', 'string'],
            'translations' => ['nullable', 'array'],
        ]);

        $list = [];
        foreach (array_values($data['lines']) as $i => $line) {
            $entry = ['qty' => (int) $line['qty']];
            if ($line['kind'] === 'group') {
                $entry['group'] = $line['group'] ?? $this->fail("lines.$i.group", 'Choose a food group.');
            } elseif ($line['kind'] === 'store') {
                $entry['store'] = DB::table('products')->where('type', $line['store'] ?? '')->exists()
                    ? $line['store'] : $this->fail("lines.$i.store", 'Choose a shop that has products.');
            } else {
                $names = array_values(array_unique($line['products'] ?? []));
                $shops = DB::table('products')->whereIn('product_name', $names)->distinct()->pluck('type');
                if (!$names || $shops->count() !== 1 || DB::table('products')->whereIn('product_name', $names)->distinct()->count('product_name') !== count($names)) {
                    $this->fail("lines.$i.products", 'Line ' . ($i + 1) . ': choose products from one shop.');
                }
                // the same product may count more than once (2026-11-02), so one name can cover "2 ×"
                $entry['products'] = $names;
            }
            $list[] = $entry;
        }

        $translations = [];
        foreach (self::LANGS as $lang) {
            foreach (['title', 'mission', 'destination'] as $field) {
                $text = trim((string) ($data['translations'][$lang][$field] ?? ''));
                if ($text !== '') {
                    $translations[$lang][$field] = $text;
                }
            }
        }

        // numbered within its kind (High or Low Spending)
        $activity ??= new Activity();
        if (!$activity->exists || $activity->type !== $data['type']) {
            $activity->number = (int) Activity::where('type', $data['type'])->max('number') + 1;
        }
        // a food list's budget leaves room for what is chosen (ShoppingList::budgetFor, 2026-11-02)
        $budget = ShoppingList::budgetFor($list, round((float) $data['budget'], 2));
        $activity->fill([
            'type' => $data['type'],
            'title' => $data['title'], 'mission' => $data['mission'], 'destination' => $data['destination'],
            'budget' => $budget, 'shopping_list' => $list, 'translations' => $translations ?: null,
            'levels' => AdminLevel::fromRequest($request->input('levels')),
            'shop' => collect($list)->map(fn ($line) => ShoppingList::shopOf($line))->unique()->implode(','),
        ])->save();

        return redirect()->route('admin.spending.lists')->with('success', '“' . $activity->title . '” saved.'
            . ($budget > round((float) $data['budget'], 2) ? ' Its budget was raised to Ƶ ' . number_format($budget, 2) . ' so the food list leaves room for what students choose.' : ''));
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    // ── Plans of the month ──────────────────────────────────────────

    public function plans()
    {
        $plans = WantsPlan::orderByRaw("kind = 'high'")->orderBy('sort_order')->orderBy('id')->get();
        $chosen = WantsPlanChoice::where('month', WantsPlanService::month())->where('kind', 'plan')->selectRaw('wants_plan_id, count(*) c')
            ->groupBy('wants_plan_id')->pluck('c', 'wants_plan_id');

        // each plan's price level (1-4) within its kind: one of each is offered every month
        $levels = [];
        foreach (['low', 'high', 'mid'] as $kind) {
            foreach (WantsPlanService::levels($kind) as $i => $level) {
                foreach ($level as $plan) {
                    $levels[$plan->id] = $i + 1;
                }
            }
        }

        // every class: its Spending 4, its Low Spending activity, and whether they reach the share of the salary
        $salary = WantsPlanService::salary();
        $classes = SchoolClass::with('grade.level')->when(session('selected_school'), fn ($q, $sid) => $q->where('sid', $sid))
            // the level being worked on (the menu by level): its classes only
            ->when(AdminLevel::id(), fn ($q, $level) => $q->whereHas('grade', fn ($g) => $g->where('grade_level_id', $level)))
            ->orderBy('name')->get()->each(function ($class) use ($plans, $salary) {
                // mid from the Intermediate on (its own plans, about 15% with the Low activity)
                $kind = WantsPlanService::kindForClass($class);
                $minimum = WantsPlanService::minimum($kind, $class->id);
                $offered = collect(WantsPlanService::levels($kind, $minimum))->flatten();
                $class->s4_kind = $kind;
                $class->s4_low = $kind !== 'high' ? WantsPlanService::lowActivity($class->id) : null;
                $class->s4_cheapest = $offered->min(fn ($p) => $p->monthlyCost());
                $class->s4_options = $offered->count();
                $class->s4_total = $kind !== 'high' ? (float) ($class->s4_low->budget ?? 0) + (float) $class->s4_cheapest : (float) $class->s4_cheapest;
                $class->s4_ok = match ($kind) {
                    'low' => $class->s4_low && round($class->s4_total, 2) >= round(WantsPlanService::LOW_TOTAL * $salary, 2),
                    'mid' => $class->s4_low && round($class->s4_total, 2) >= round(WantsPlanService::MID_TOTAL * $salary, 2),
                    default => $class->s4_cheapest > WantsPlanService::HIGH_MIN * $salary,
                };
            });
        $gradeLevels = GradeLevel::orderBy('sort_order')->get(['id', 'name']);

        return view('admin.spending.plans', compact('plans', 'chosen', 'levels', 'classes', 'gradeLevels', 'salary'));
    }

    public function savePlan(Request $request, ?WantsPlan $plan = null)
    {
        $data = $request->validate([
            'kind' => ['required', 'in:low,high,mid'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:600'],
            'cost' => ['required', 'numeric', 'min:1', 'max:100000'],
            'times_per_month' => ['required', 'in:1,2,4'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'translations' => ['nullable', 'array'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        // a High Spending 4 plan is more than 20% of the salary on its own (Daniel, 2026-10-31)
        $high = WantsPlanService::HIGH_MIN * WantsPlanService::salary();
        if ($data['kind'] === 'high' && round($data['cost'] * (int) $data['times_per_month'], 2) <= round($high, 2)) {
            $this->fail('cost', 'A High Spending 4 plan must cost more than 20% of the salary in the month (more than Ƶ ' . number_format($high, 2) . ').');
        }

        $translations = [];
        foreach (self::LANGS as $lang) {
            foreach (['name', 'description'] as $field) {
                $text = trim((string) ($data['translations'][$lang][$field] ?? ''));
                if ($text !== '') {
                    $translations[$lang][$field] = $text;
                }
            }
        }

        $plan ??= new WantsPlan(['is_active' => true]);
        $plan->fill([
            'kind' => $data['kind'],
            'name' => $data['name'], 'description' => $data['description'], 'cost' => round((float) $data['cost'], 2),
            'times_per_month' => (int) $data['times_per_month'], 'sort_order' => (int) ($data['sort_order'] ?? $plan->sort_order ?? 0),
            'translations' => $translations,
        ]);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/wants-plans'), $name);
            $plan->image = 'uploads/wants-plans/' . $name;
        }
        $plan->save();

        return redirect()->route('admin.spending.plans')->with('success', '“' . $plan->name . '” saved.');
    }

    /** A class's Spending 4: Low (with its Low Spending activity) or High (on its own). */
    public function classKind(Request $request, SchoolClass $class)
    {
        $data = $request->validate(['spending4' => ['required', 'in:low,high']]);
        $class->update(['spending4' => $data['spending4']]);

        return back()->with('success', $class->name . ' now has ' . ucfirst($data['spending4']) . ' Spending 4.');
    }

    /** Every class at a curriculum level at once (e.g. Intermediate: High Spending 4). */
    public function levelKind(Request $request)
    {
        $data = $request->validate(['grade_level_id' => ['required', 'integer'], 'spending4' => ['required', 'in:low,high']]);
        $count = SchoolClass::whereHas('grade', fn ($q) => $q->where('grade_level_id', $data['grade_level_id']))
            ->when(session('selected_school'), fn ($q, $sid) => $q->where('sid', $sid))
            ->update(['spending4' => $data['spending4']]);

        return back()->with('success', $count . ' ' . \Illuminate\Support\Str::plural('class', $count) . ' now have ' . ucfirst($data['spending4']) . ' Spending 4.');
    }

    public function togglePlan(WantsPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', '“' . $plan->name . '” is now ' . ($plan->is_active ? 'offered' : 'not offered') . '.');
    }
}
