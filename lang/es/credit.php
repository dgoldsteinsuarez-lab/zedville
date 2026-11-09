<?php

// Tarea FinHero 16, «Compara dos préstamos» (App\Services\Finhero\Credit\CompareLoans).
return [
    'compare' => [
        'title' => 'Compara dos préstamos',
        'intro' => 'Necesitas :amount. El banco te ofrece dos préstamos. Calcula cuál te cuesta menos en total.',
        'chart' => 'Las dos ofertas',
        'offer_a' => 'Préstamo A',
        'offer_b' => 'Préstamo B',
        'same' => 'Los dos igual',
        'row_months' => 'Meses',
        'row_tae' => 'TAE',
        'row_instalment' => 'Cuota al mes',
        'hint' => 'Lo que pagas en total es la cuota por los meses; los intereses, eso menos lo que te prestan.',
        'q_lower' => '¿Qué préstamo tiene la cuota mensual más baja?',
        'why_lower' => 'El B: :b al mes, frente a :a del A, porque lo pagas en más meses.',
        'q_interest_a' => '¿Cuánto pagas de intereses en total con el préstamo A?',
        'why_interest_a' => ':instalment × :months = :total. Menos los :amount que te prestan: :interest de intereses.',
        'q_cheaper' => '¿Qué préstamo te cuesta menos en total?',
        'why_cheaper' => 'El A: :a de intereses; el B: :b. El B tiene la TAE y la cuota más bajas, pero pagas intereses el doble de tiempo.',
        'q_same_term' => 'Si los dos préstamos fueran a 12 meses, ¿cuál costaría menos?',
        'why_same_term' => 'Con el mismo plazo, la TAE más baja es siempre la más barata: el B al :tae_b costaría :b de intereses, y el A al :tae_a, :a. Por eso los préstamos se comparan por la TAE y el coste total, nunca por la cuota.',
        'q_bank' => 'Todas tus cuotas juntas pueden ser como mucho :max al mes. ¿Te daría el banco el préstamo A?',
        'yes' => 'Sí',
        'no' => 'No',
        'why_yes' => 'Su cuota, :instalment, cabe en tus :max al mes.',
        'why_no' => 'Con su cuota, :instalment, tus cuotas pasarían de :max al mes.',
    ],
];
