<?php

// FinHero Task 5, "How much have I saved?" (resources/views/finhero/task-5.blade.php): the Intermediate's
// monthly look at what the student saved, which replaces the Needs, Wants & Savings Match.
return [
    'title' => 'How much have I saved?',
    'saved' => 'This month you have saved :amount: :percent of your salary.',
    'saved_none' => 'You haven\'t saved anything yet this month.',
    'last' => 'Last month, :amount (:percent).',
    'share' => 'Every payday, :percent of your salary goes to your fund.',
    'change_share' => 'Change it in the bank',
    'q1' => 'What helped you most this month?',
    'q1_options' => [
        'Putting money aside on payday',
        'Choosing a cheap plan of the month',
        'Spending less on other things',
        'I couldn\'t save this month',
    ],
    'q2' => 'What will you do next month?',
    'q2_options' => [
        'Put a bit more aside for my fund',
        'Move money to my Money Market Account',
        'Choose a cheaper plan',
        'Keep it the same',
    ],
    'choose_both' => 'Choose an answer for each question.',
    'finish' => 'Finish',
    'done' => 'Done! Come back next month to see how much you have saved.',
];
