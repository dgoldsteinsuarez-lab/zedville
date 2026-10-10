<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Academic Year Start Month
    |--------------------------------------------------------------------------
    |
    | The calendar month (1 = January ... 12 = December) that the academic
    | year rolls over on. Used by badge/point calculations to decide which
    | "academic year" a given month/year belongs to (e.g. month >= this
    | value means the academic year is "$year-" . ($year + 1)).
    |
    | Previously this was hardcoded independently across multiple files
    | with inconsistent values (9 in most places, 4 in FinheroPointService).
    | Confirmed canonical value: April (4).
    |
    */
    'academic_year_start_month' => 4,

    /*
    |--------------------------------------------------------------------------
    | Monthly Salary
    |--------------------------------------------------------------------------
    |
    | The default virtual monthly salary credited to students. Previously
    | hardcoded independently across multiple files with inconsistent values
    | (3952.40 in most places, 4250 in BankController::creditMonthlySalary).
    | Confirmed canonical value: 3952.40.
    |
    */
    'monthly_salary' => 3952.40,


    /*
    |--------------------------------------------------------------------------
    | Automatic month-end penalty for ALL students
    |--------------------------------------------------------------------------
    |
    | The penalty (expenses under 91% of the monthly salary) is currently only
    | applied when a student happens to log in on the last day of the month.
    | When this is true, a scheduled job applies it to every student instead,
    | as the Admin Roles & Permissions doc describes ("system event, no admin
    | action"). OFF by default because it changes real balances: switch it on
    | with ZEDVILLE_AUTO_PENALTY=true in .env only after the owner agrees.
    |
    */
    'auto_penalty' => (bool) env('ZEDVILLE_AUTO_PENALTY', false),

    /*
    |--------------------------------------------------------------------------
    | Inactive Student Alert Threshold
    |--------------------------------------------------------------------------
    |
    | Days with no login and no activity_completions row before a Tutor gets
    | an "inactive student" alert (Admin Roles & Permissions doc §2.3).
    |
    */
    'inactive_days_threshold' => 14,

    /*
    |--------------------------------------------------------------------------
    | Emergency Fund Auto-Transfer Percentage
    |--------------------------------------------------------------------------
    |
    | Previously hardcoded only as a fallback default on each individual
    | config('zedville.emargency_fund_persentage', 20) call site - the key
    | itself was never actually defined here, so there was nothing to
    | centrally change. Confirmed canonical value: 20 (the fallback every
    | call site already agreed on).
    |
    */
    'emargency_fund_persentage' => 20,

    /*
    |--------------------------------------------------------------------------
    | Fixed Monthly Bills
    |--------------------------------------------------------------------------
    |
    | Rent and School Fees amounts, previously hardcoded independently
    | across BankController, CitizenActivationController, and
    | CitizenActivationService (11 separate literals across the three
    | files, all agreeing on the same values). Confirmed canonical values:
    | Rent 300, School Fees 1000.
    |
    */
    'rent' => 300,
    'school_fees' => 1000,

];
