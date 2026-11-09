<?php

// The plan of the month: once a month, after the question of the day, the student
// chooses what they will do (App\Services\WantsPlanService, 2026-10-31).
return [
    'title' => 'Dein Plan für :month',
    'subtitle' => 'Was machst du diesen Monat? Wähle einen Plan. Er ist ein Wunsch, also zahlst du ihn von deinem Girokonto.',
    'choose' => 'Diesen Plan wählen',
    'choosing' => 'Wird gespeichert …',
    'cost_line' => ':cost :freq',
    'freq' => [
        '1' => 'einmal',
        '2' => 'jeden zweiten Samstag',
        '4' => 'jeden Samstag',
    ],
    'month_total' => 'Diesen Monat: :n × :cost = :total',
    'today' => 'heute',
    'and' => 'und',
    'day' => ':d.',
    'on_day' => 'am :days :month',
    'on_days' => 'am :days :month',
    'paid_on' => 'Du zahlst :dates.',
    'chosen_title' => 'Gute Wahl!',
    'chosen_text' => 'Diesen Monat: :name.',
    // not enough money: the payment waits ("abbuchen", "Gehaltseingang")
    'waiting_salary' => 'Dein Gehalt ist noch nicht eingegangen, deshalb ist heute nicht genug Geld auf deinem Girokonto. Die :amount werden automatisch abgebucht, wenn dein Gehalt :date eingeht.',
    'waiting_salary_soon' => 'Dein Gehalt ist noch nicht eingegangen, deshalb ist heute nicht genug Geld auf deinem Girokonto. Die :amount werden automatisch abgebucht, sobald dein Gehalt eingeht.',
    'waiting_money' => 'Heute ist nicht genug Geld auf deinem Girokonto. Die :amount werden automatisch abgebucht, sobald genug da ist, spätestens wenn dein nächstes Gehalt :date eingeht.',
    'ok' => 'OK',
    'error' => 'Etwas ist schiefgelaufen. Versuch es noch einmal.',
    'bank_line' => 'Monatsplan: :name',
    'catch_up' => 'Die schon vergangenen Samstage werden heute bezahlt.',
    'paid_today_amount' => 'Heute bezahlt: :amount',
    'cost_once' => ':cost diesen Monat',
    // the plan of the month as an activity: its fine when it isn't chosen (MonthEndPenaltyService)
    'activity' => 'Monatsplan',
];
