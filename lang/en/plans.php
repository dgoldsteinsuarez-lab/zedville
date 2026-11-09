<?php

// The plan of the month: once a month, after the question of the day, the student
// chooses what they will do (App\Services\WantsPlanService, 2026-10-31).
return [
    'title' => 'Your plan for :month',
    'subtitle' => 'What will you do this month? Choose one plan. It is a Want, so you pay it from your current account.',
    'choose' => 'Choose this plan',
    'choosing' => 'Saving…',
    'cost_line' => ':cost :freq',
    'freq' => [
        '1' => 'once',
        '2' => 'every other Saturday',
        '4' => 'every Saturday',
    ],
    'month_total' => 'This month: :n × :cost = :total',
    'today' => 'today',
    'and' => 'and',
    'day' => ':d',
    'on_day' => 'on :days :month',
    'on_days' => 'on :days :month',
    'paid_on' => 'You pay :dates.',
    'chosen_title' => 'Good choice!',
    'chosen_text' => 'This month: :name.',
    // not enough money: the payment waits for it (2026-11-02)
    'waiting_salary' => 'Your salary hasn\'t arrived yet, so there isn\'t enough money in your current account today. The :amount will be paid automatically when your salary arrives, :date.',
    'waiting_salary_soon' => 'Your salary hasn\'t arrived yet, so there isn\'t enough money in your current account today. The :amount will be paid automatically when your salary arrives.',
    'waiting_money' => 'There isn\'t enough money in your current account today. The :amount will be paid automatically as soon as there is, at the latest when your next salary arrives, :date.',
    'ok' => 'OK',
    'error' => 'Something went wrong. Please try again.',
    'bank_line' => 'Monthly plan: :name',
    'catch_up' => 'The Saturdays already gone are paid today.',
    'paid_today_amount' => 'Paid today: :amount',
    'cost_once' => ':cost this month',
    // the plan of the month as an activity: its fine when it isn't chosen (MonthEndPenaltyService)
    'activity' => 'Monthly plan',
];
