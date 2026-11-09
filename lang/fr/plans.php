<?php

// The plan of the month: once a month, after the question of the day, the student
// chooses what they will do (App\Services\WantsPlanService, 2026-10-31).
return [
    'title' => 'Ton plan pour :month',
    'subtitle' => 'Que vas-tu faire ce mois-ci ? Choisis un plan. C\'est une envie : tu la paies avec ton compte courant.',
    'choose' => 'Choisir ce plan',
    'choosing' => 'Enregistrement…',
    'cost_line' => ':cost :freq',
    'freq' => [
        '1' => 'une fois',
        '2' => 'un samedi sur deux',
        '4' => 'chaque samedi',
    ],
    'month_total' => 'Ce mois-ci : :n × :cost = :total',
    'today' => 'aujourd\'hui',
    'and' => 'et',
    'day' => ':d',
    'on_day' => 'le :days :month',
    'on_days' => 'les :days :month',
    'paid_on' => 'Tu paies :dates.',
    'chosen_title' => 'Bon choix !',
    'chosen_text' => 'Ce mois-ci : :name.',
    // not enough money: the payment waits ("prélèvement", "salaire versé")
    'waiting_salary' => 'Ton salaire n\'a pas encore été versé, donc il n\'y a pas assez d\'argent sur ton compte courant aujourd\'hui. Les :amount seront prélevés automatiquement quand ton salaire sera versé, :date.',
    'waiting_salary_soon' => 'Ton salaire n\'a pas encore été versé, donc il n\'y a pas assez d\'argent sur ton compte courant aujourd\'hui. Les :amount seront prélevés automatiquement quand ton salaire sera versé.',
    'waiting_money' => 'Il n\'y a pas assez d\'argent sur ton compte courant aujourd\'hui. Les :amount seront prélevés automatiquement dès qu\'il y en aura assez, au plus tard quand ton prochain salaire sera versé, :date.',
    'ok' => 'D\'accord',
    'error' => 'Un problème est survenu. Réessaie.',
    'bank_line' => 'Plan du mois : :name',
    'catch_up' => 'Les samedis déjà passés sont payés aujourd\'hui.',
    'paid_today_amount' => 'Payé aujourd\'hui : :amount',
    'cost_once' => ':cost ce mois-ci',
    // the plan of the month as an activity: its fine when it isn't chosen (MonthEndPenaltyService)
    'activity' => 'Plan du mois',
];
