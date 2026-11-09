<?php

// Bell notifications of the bank, the mood and the badges, written from their source when shown (App\Support\NotificationText).
return [
    'salary_title' => 'Salary received',
    'money_received_title' => 'Money received',
    'payment_title' => 'Payment from your account',
    'large_payment_title' => 'Large payment from your account',
    'credit_body' => ':description: :amount has been added to your current account.',
    'debit_body' => ':description: :amount has been taken from your current account.',
    'transfer_done_title' => 'Transfer sent',
    'transfer_done_body' => 'Your transfer of :amount to :name has been sent.',
    'transfer_scheduled_title' => 'Transfer scheduled',
    'transfer_scheduled_body' => 'Your transfer of :amount to :name will be sent on :date.',
    'standing_order_body' => 'Your standing order of :amount to :name starts on :date.',
    // mood and badges (2026-10-30)
    'mood_title' => 'Mood saved',
    'mood_body' => 'Your mood for today has been saved: :mood.',
    'engagement_badge_title' => 'Engagement badge earned',
    'engagement_badge_body' => 'You earned the :badge Engagement badge this month!',
    'finhero_badge_title' => 'FinHero badge earned',
    'finhero_badge_body' => 'You earned the :badge FinHero badge this month!',
];
