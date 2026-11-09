<?php

// Tâche FinHero 16, « Compare deux prêts » (App\Services\Finhero\Credit\CompareLoans).
return [
    'compare' => [
        'title' => 'Compare deux prêts',
        'intro' => 'Tu as besoin de :amount. La banque te propose deux prêts. Calcule lequel te coûte le moins en tout.',
        'chart' => 'Les deux offres',
        'offer_a' => 'Prêt A',
        'offer_b' => 'Prêt B',
        'same' => 'Les deux pareil',
        'row_months' => 'Mois',
        'row_tae' => 'TAEG',
        'row_instalment' => 'Mensualité',
        'hint' => 'Ce que tu paies en tout, c\'est la mensualité fois le nombre de mois ; les intérêts, c\'est cela moins ce qu\'on te prête.',
        'q_lower' => 'Quel prêt a la mensualité la plus basse ?',
        'why_lower' => 'Le B : :b par mois, contre :a pour le A, parce que tu le rembourses sur plus de mois.',
        'q_interest_a' => 'Combien paies-tu d\'intérêts en tout avec le prêt A ?',
        'why_interest_a' => ':instalment × :months = :total. Moins les :amount qu\'on te prête : :interest d\'intérêts.',
        'q_cheaper' => 'Quel prêt te coûte le moins en tout ?',
        'why_cheaper' => 'Le A : :a d\'intérêts ; le B : :b. Le B a le TAEG et la mensualité les plus bas, mais tu paies des intérêts deux fois plus longtemps.',
        'q_same_term' => 'Si les deux prêts duraient 12 mois, lequel coûterait le moins ?',
        'why_same_term' => 'À durée égale, le TAEG le plus bas est toujours le moins cher : le B à :tae_b coûterait :b d\'intérêts, le A à :tae_a, :a. C\'est pour ça qu\'on compare les prêts par leur TAEG et leur coût total, jamais par la mensualité.',
        'q_bank' => 'Toutes tes mensualités ensemble peuvent être au maximum de :max par mois. La banque te donnerait-elle le prêt A ?',
        'yes' => 'Oui',
        'no' => 'Non',
        'why_yes' => 'Sa mensualité, :instalment, tient dans tes :max par mois.',
        'why_no' => 'Avec sa mensualité, :instalment, tes mensualités dépasseraient :max par mois.',
    ],
];
