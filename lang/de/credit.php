<?php

// FinHero-Aufgabe 16, „Vergleiche zwei Kredite“ (App\Services\Finhero\Credit\CompareLoans).
return [
    'compare' => [
        'title' => 'Vergleiche zwei Kredite',
        'intro' => 'Du brauchst :amount. Die Bank bietet dir zwei Kredite an. Rechne aus, welcher dich insgesamt weniger kostet.',
        'chart' => 'Die zwei Angebote',
        'offer_a' => 'Kredit A',
        'offer_b' => 'Kredit B',
        'same' => 'Beide gleich',
        'row_months' => 'Monate',
        'row_tae' => 'Effektiver Jahreszins',
        'row_instalment' => 'Monatsrate',
        'hint' => 'Was du insgesamt zahlst, ist die Rate mal die Monate; die Zinsen sind das minus das, was du leihst.',
        'q_lower' => 'Welcher Kredit hat die niedrigere Monatsrate?',
        'why_lower' => 'B: :b im Monat, gegen :a bei A, weil du ihn über mehr Monate zurückzahlst.',
        'q_interest_a' => 'Wie viel Zinsen zahlst du insgesamt mit Kredit A?',
        'why_interest_a' => ':instalment × :months = :total. Minus die :amount, die du leihst: :interest Zinsen.',
        'q_cheaper' => 'Welcher Kredit kostet dich insgesamt weniger?',
        'why_cheaper' => 'A: :a Zinsen; B: :b. B hat den niedrigeren Zins und die niedrigere Rate, aber du zahlst doppelt so lange Zinsen.',
        'q_same_term' => 'Wenn beide Kredite 12 Monate liefen, welcher wäre billiger?',
        'why_same_term' => 'Bei gleicher Laufzeit ist der niedrigere effektive Jahreszins immer billiger: B mit :tae_b würde :b Zinsen kosten, A mit :tae_a :a. Deshalb vergleicht man Kredite nach effektivem Jahreszins und Gesamtkosten, nie nach der Rate.',
        'q_bank' => 'Alle deine Raten zusammen dürfen höchstens :max im Monat sein. Würde dir die Bank Kredit A geben?',
        'yes' => 'Ja',
        'no' => 'Nein',
        'why_yes' => 'Seine Rate, :instalment, passt in deine :max im Monat.',
        'why_no' => 'Mit seiner Rate, :instalment, lägen deine Raten über :max im Monat.',
    ],
];
