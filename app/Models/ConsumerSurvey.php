<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumerSurvey extends Model
{
    protected $fillable = [
        'user_id',
        'sid',
        'diet',
        'groceries',
        'basket_cost',
        'lunch',
        'transport',
        'activities',
        'restaurants',
        'total_cost',
        'survey_month',
    ];

    protected $casts = [
        'groceries' => 'array',
    ];

    /**
     * The empty record "Skip for now" leaves in Citizen Activation step 5
     * (no diet, nothing spent). It only lets activation continue - the
     * student hasn't answered the survey yet (Daniel, 2026-10-23).
     */
    public function isSkipped(): bool
    {
        return $this->diet === null && (float) $this->total_cost == 0.0;
    }

    /*
     * "Your first month in Zedville" (Daniel, 2026-10-23): every answer is a
     * Need or a Want, and the mix gives the student a consumer type at the
     * end - the bridge to the Needs, Wants & Savings Match.
     */

    /** Taxi is the only way to school that's a want; the rest gets you there. */
    public const TAXI = 240;

    /** Amounts per Need/Want and the total, from a survey or from submitted answers. */
    public static function needsWants(float $basket, float $lunch, float $transport, float $activities, float $restaurants): array
    {
        $wants = $activities + $restaurants + ($transport == self::TAXI ? $transport : 0);
        $total = $basket + $lunch + $transport + $activities + $restaurants;

        return ['needs' => round($total - $wants, 2), 'wants' => round($wants, 2), 'total' => round($total, 2)];
    }

    /**
     * The student's consumer type: cruiser (always takes a taxi), foodie or
     * explorer (wants are 35%+ of the month - eating out vs. paid
     * activities), smart saver (wants under 15%), else balanced.
     */
    public static function profileType(float $basket, float $lunch, float $transport, float $activities, float $restaurants): string
    {
        $split = self::needsWants($basket, $lunch, $transport, $activities, $restaurants);
        $share = $split['total'] > 0 ? $split['wants'] / $split['total'] : 0;

        if ($transport == self::TAXI) {
            return 'cruiser';
        }
        if ($share >= 0.35) {
            return $restaurants >= $activities ? 'foodie' : 'explorer';
        }

        return $share < 0.15 ? 'saver' : 'balanced';
    }

    /** The type and Need/Want split of this survey, with its texts in the student's language. */
    public function profile(): array
    {
        $values = [(float) $this->basket_cost, (float) $this->lunch, (float) $this->transport, (float) $this->activities, (float) $this->restaurants];
        $type = self::profileType(...$values);

        return ['type' => $type] + self::needsWants(...$values) + (array) __('surveys.types.' . $type);
    }

    /** The salary the survey compares against: the student's latest one, else the city's base salary. */
    public static function salaryFor(int $userId): float
    {
        return (float) (\App\Models\Transaction1::where('user_id', $userId)->where('category', 'Income')->where('type', 'credit')
            ->latest('id')->value('amount') ?: config('zedville.monthly_salary', 3952.40));
    }

    /** First day of the month this survey is for (`survey_month` is "Oct-2026"). */
    public function monthStart(): ?\Carbon\Carbon
    {
        try {
            return \Carbon\Carbon::createFromFormat('!M-Y', (string) $this->survey_month)->startOfMonth();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /*
     * Daniel, 2026-10-23: a student may do the survey once per calendar
     * month - it's the only way to change their diet - and a survey done
     * during a month applies from the next month. The very first one (in
     * Citizen Activation, or the first after skipping it there) applies
     * straight away.
     */

    /** The survey in effect this month: the latest one for this month or earlier. */
    public static function inEffect(int $userId): ?self
    {
        $thisMonth = now()->startOfMonth();

        return static::where('user_id', $userId)->orderByDesc('id')->get()
            ->filter(fn ($s) => !$s->monthStart() || $s->monthStart()->lte($thisMonth))
            ->sortByDesc(fn ($s) => [optional($s->monthStart())->timestamp ?? 0, $s->id])
            ->first();
    }

    /** Has the student already answered the survey this calendar month? */
    public static function doneThisMonth(int $userId): bool
    {
        return static::where('user_id', $userId)
            ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)
            ->get()->contains(fn ($s) => !$s->isSkipped());
    }

    /**
     * The month a new survey goes into: this month while the student has
     * no answered survey yet (only nothing or a skipped one), else next
     * month.
     */
    public static function targetMonthFor(int $userId): \Carbon\Carbon
    {
        $answered = static::where('user_id', $userId)->get()->contains(fn ($s) => !$s->isSkipped());

        return $answered ? now()->startOfMonth()->addMonth() : now()->startOfMonth();
    }
}
