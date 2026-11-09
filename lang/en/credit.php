<?php

// FinHero Task 16, "Compare two loans" (App\Services\Finhero\Credit\CompareLoans).
return [
    'compare' => [
        'title' => 'Compare two loans',
        'intro' => 'You need :amount. The bank offers you two loans. Work out which one costs you less in all.',
        'chart' => 'The two offers',
        'offer_a' => 'Loan A',
        'offer_b' => 'Loan B',
        'same' => 'Both the same',
        'row_months' => 'Months',
        'row_tae' => 'APR',
        'row_instalment' => 'Monthly instalment',
        'hint' => 'What you pay in all is the instalment times the months; the interest is that minus what you borrow.',
        'q_lower' => 'Which loan has the lower monthly instalment?',
        'why_lower' => 'B: :b a month, against :a for A, because you pay it over more months.',
        'q_interest_a' => 'How much interest do you pay in all with loan A?',
        'why_interest_a' => ':instalment × :months = :total. Minus the :amount you borrow: :interest of interest.',
        'q_cheaper' => 'Which loan costs you less in all?',
        'why_cheaper' => 'A: :a of interest; B: :b. B has the lower APR and instalment, but you pay interest for twice as long.',
        'q_same_term' => 'If both loans were over 12 months, which would cost less?',
        'why_same_term' => 'With the same term, the lower APR is always cheaper: B at :tae_b would cost :b of interest, A at :tae_a :a. That is why you compare loans by their APR and total cost, never by the instalment.',
        'q_bank' => 'All your instalments together may be at most :max a month. Would the bank give you loan A?',
        'yes' => 'Yes',
        'no' => 'No',
        'why_yes' => 'Its instalment, :instalment, fits within your :max a month.',
        'why_no' => 'With its instalment, :instalment, your instalments would go over :max a month.',
    ],
];
