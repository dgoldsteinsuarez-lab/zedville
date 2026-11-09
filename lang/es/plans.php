<?php

// The plan of the month: once a month, after the question of the day, the student
// chooses what they will do (App\Services\WantsPlanService, 2026-10-31).
return [
    'title' => 'Tu plan para :month',
    'subtitle' => '¿Qué vas a hacer este mes? Elige un plan. Es un deseo, así que lo pagas desde tu cuenta corriente.',
    'choose' => 'Elegir este plan',
    'choosing' => 'Guardando…',
    'cost_line' => ':cost :freq',
    'freq' => [
        '1' => 'una vez',
        '2' => 'cada dos sábados',
        '4' => 'cada sábado',
    ],
    'month_total' => 'Este mes: :n × :cost = :total',
    'today' => 'hoy',
    'and' => 'y',
    'day' => ':d',
    'on_day' => 'el :days de :month',
    'on_days' => 'los días :days de :month',
    'paid_on' => 'Lo pagas :dates.',
    'chosen_title' => '¡Buena elección!',
    'chosen_text' => 'Este mes: :name.',
    // not enough money: the payment waits ("cargo en cuenta", Banco de España; "nómina" as in the bank)
    'waiting_salary' => 'Tu nómina aún no ha llegado, así que hoy no hay suficiente dinero en tu cuenta corriente. Los :amount se cargarán en tu cuenta automáticamente cuando llegue tu nómina, :date.',
    'waiting_salary_soon' => 'Tu nómina aún no ha llegado, así que hoy no hay suficiente dinero en tu cuenta corriente. Los :amount se cargarán en tu cuenta automáticamente cuando llegue tu nómina.',
    'waiting_money' => 'Hoy no hay suficiente dinero en tu cuenta corriente. Los :amount se cargarán en tu cuenta automáticamente en cuanto haya suficiente, como muy tarde cuando llegue tu próxima nómina, :date.',
    'ok' => 'Vale',
    'error' => 'Algo ha fallado. Inténtalo de nuevo.',
    'bank_line' => 'Plan del mes: :name',
    'catch_up' => 'Los sábados que ya han pasado se pagan hoy.',
    'paid_today_amount' => 'Pagado hoy: :amount',
    'cost_once' => ':cost este mes',
    // the plan of the month as an activity: its fine when it isn't chosen (MonthEndPenaltyService)
    'activity' => 'Plan del mes',
];
