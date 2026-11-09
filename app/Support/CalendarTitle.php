<?php

namespace App\Support;

/**
 * Calendar events keep internal prefixes in their titles ("task-Classify
 * Your Salary", "library-Introduction to Budget", "exam-...") because
 * completions are matched by title (ActivityCompletionService). Students
 * should only see the activity's name - and the built-in activities in
 * their own language. The stored title is never changed.
 */
class CalendarTitle
{
    private const PREFIXES = ['task-', 'library-', 'exam-', 'easy-'];

    // Titles the app itself creates (Mba/EmergencyFund/HighBudgetActivity/
    // SpendingActivity admin controllers).
    private const BUILT_IN = [
        'Needs, Wants & Savings Match' => 'efd.needs_wants_savings',
        'Emergency Fund' => 'efd.emergency_fund',
        'Spending budget activity' => 'efd.spending_activity',
        'Low spending activity' => 'efd.low_spending_activity',
        'Substitute Goods: My Shopping Basket' => 'engagement.substitute_goods_title',
        // not on the calendar: the plan of the month, on its fine (MonthEndPenaltyService)
        'Monthly plan' => 'plans.activity',
        'Monthly Donation' => 'npos.activity',
    ];

    public static function forStudent(?string $title): string
    {
        $title = (string) $title;

        if (isset(self::BUILT_IN[$title])) {
            return __(self::BUILT_IN[$title]);
        }

        foreach (self::PREFIXES as $prefix) {
            if (str_starts_with($title, $prefix)) {
                // FinHero tasks, Library modules, exams and easy activities, in the student's language.
                $name = substr($title, strlen($prefix));

                return match ($prefix) {
                    'exam-' => ct('curriculum', $name),
                    'library-' => ct('library', $name),
                    default => ct('activities', $name),
                };
            }
        }

        return ct('activities', $title);
    }

    /** An event's description as the student sees it: an easy activity's says what to do, in their language. */
    public static function descriptionForStudent(?string $title, ?string $description): ?string
    {
        if (str_starts_with((string) $title, \App\Models\EasyActivity::CALENDAR_PREFIX)) {
            $activity = \App\Models\EasyActivity::where('title', substr($title, strlen(\App\Models\EasyActivity::CALENDAR_PREFIX)))->first();

            return $activity ? implode(' ', $activity->steps()) : $description;
        }

        return $description;
    }
}
