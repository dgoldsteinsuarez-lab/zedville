<?php

// Bell notifications of the bank, the mood and the badges, written from their source when shown (App\Support\NotificationText).
return [
    'salary_title' => 'Salaire reçu',
    'money_received_title' => 'Argent reçu',
    'payment_title' => 'Débit sur ton compte',
    'large_payment_title' => 'Débit important sur ton compte',
    'credit_body' => ':description : :amount ont été crédités sur ton compte courant.',
    'debit_body' => ':description : :amount ont été débités de ton compte courant.',
    'transfer_done_title' => 'Virement envoyé',
    'transfer_done_body' => 'Ton virement de :amount à :name a été envoyé.',
    'transfer_scheduled_title' => 'Virement programmé',
    'transfer_scheduled_body' => 'Ton virement de :amount à :name sera envoyé le :date.',
    'standing_order_body' => 'Ton virement permanent de :amount à :name commence le :date.',
    // mood and badges (2026-10-30)
    'mood_title' => 'Humeur enregistrée',
    'mood_body' => 'Ton humeur du jour est enregistrée : :mood.',
    'engagement_badge_title' => 'Badge Participation obtenu',
    'engagement_badge_body' => 'Ce mois-ci, tu as obtenu le badge Participation de niveau :badge !',
    'finhero_badge_title' => 'Badge FinHero obtenu',
    'finhero_badge_body' => 'Ce mois-ci, tu as obtenu le badge FinHero de niveau :badge !',
];
