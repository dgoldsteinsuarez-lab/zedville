<?php

// Bell notifications of the bank, the mood and the badges, written from their source when shown (App\Support\NotificationText).
return [
    'salary_title' => 'Gehalt eingegangen',
    'money_received_title' => 'Geld eingegangen',
    'payment_title' => 'Abbuchung von deinem Konto',
    'large_payment_title' => 'Hohe Abbuchung von deinem Konto',
    'credit_body' => ':description: :amount wurden deinem Girokonto gutgeschrieben.',
    'debit_body' => ':description: :amount wurden von deinem Girokonto abgebucht.',
    'transfer_done_title' => 'Überweisung ausgeführt',
    'transfer_done_body' => 'Deine Überweisung über :amount an :name wurde ausgeführt.',
    'transfer_scheduled_title' => 'Überweisung geplant',
    'transfer_scheduled_body' => 'Deine Überweisung über :amount an :name wird am :date ausgeführt.',
    'standing_order_body' => 'Dein Dauerauftrag über :amount an :name beginnt am :date.',
    // mood and badges (2026-10-30)
    'mood_title' => 'Stimmung gespeichert',
    'mood_body' => 'Deine Stimmung von heute ist gespeichert: :mood.',
    'engagement_badge_title' => 'Abzeichen „Beteiligung“ erhalten',
    'engagement_badge_body' => 'Diesen Monat hast du das Abzeichen „Beteiligung“ der Stufe :badge erhalten!',
    'finhero_badge_title' => 'FinHero-Abzeichen erhalten',
    'finhero_badge_body' => 'Diesen Monat hast du das FinHero-Abzeichen der Stufe :badge erhalten!',
];
