<?php

// FinHero Task 5, "How much have I saved?" (resources/views/finhero/task-5.blade.php): the Intermediate's
// monthly look at what the student saved, which replaces the Needs, Wants & Savings Match.
return [
    'title' => 'Combien ai-je épargné ?',
    'saved' => 'Ce mois-ci, tu as épargné :amount : :percent de ton salaire.',
    'saved_none' => 'Tu n\'as encore rien épargné ce mois-ci.',
    'last' => 'Le mois dernier, :amount (:percent).',
    'share' => 'Chaque jour de salaire, :percent de ton salaire va sur ton livret.',
    'change_share' => 'Le changer à la banque',
    'q1' => 'Qu\'est-ce qui t\'a le plus aidé ce mois-ci ?',
    'q1_options' => [
        'Mettre de l\'argent de côté le jour du salaire',
        'Choisir un plan du mois pas cher',
        'Dépenser moins pour le reste',
        'Je n\'ai pas pu épargner ce mois-ci',
    ],
    'q2' => 'Que feras-tu le mois prochain ?',
    'q2_options' => [
        'Mettre un peu plus de côté sur mon livret',
        'Virer de l\'argent sur mon compte à terme',
        'Choisir un plan moins cher',
        'Continuer pareil',
    ],
    'choose_both' => 'Choisis une réponse à chaque question.',
    'finish' => 'Terminer',
    'done' => 'C\'est fait ! Reviens le mois prochain pour voir combien tu as épargné.',
];
