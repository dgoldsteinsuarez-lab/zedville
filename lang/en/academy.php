<?php

// Daniel, 2026-10-21: Academy screens. Content authored by admins
// (CurriculumCategory.name, CurriculumItem.title/description, GradeLevel
// names like "Rookie") is NOT translated by this - that's stored data,
// not UI chrome, and translating admin-authored content is a separate,
// bigger piece than this infrastructure-first pass. Only the surrounding
// page text (headings, empty states, buttons) is.
return [
    'title' => 'Academy',
    'level_suffix' => 'Level',
    'nothing_here_yet' => 'Nothing here yet - check back soon.',
    'item_count' => '{0} No items|{1} :count item|[2,*] :count items',
    'not_attempted_yet' => 'Not attempted yet',
    'best_score' => 'Best score',
    'real_exam' => 'Real exam',
    'real_exam_final_note' => 'This is a one-time exam - your result is final.',
    'real_exam_section' => 'Real Exam',
    'practice_exams_section' => 'Practice Exams',
    'back_to_academy' => 'Academy',
    'try_again' => 'Try again',
    'back_to' => 'Back to',
    'correct' => 'Correct',
    'you_answered' => 'You answered',
    'not_answered' => 'Not answered',
    'question_removed' => '(question removed)',
    'no_questions_yet' => 'This item has no questions yet - check back soon.',
    'question_count_ready' => '{1} :count question - ready when you are.|[2,*] :count questions - ready when you are.',
    'start' => 'Start',
    'question' => 'Question',
    'previous' => 'Previous',
    'next' => 'Next',
    'submit' => 'Submit',

    // Monthly Questions (the Log In questions, month by month)
    'monthly_title' => 'Monthly Questions',
    'monthly_card_text' => 'Practise the questions from each month in Zedville.',
    'monthly_intro' => 'A new set of questions opens each month, about what you are doing in Zedville. Earlier months stay open, so you can practise any of them.',
    'month_n' => 'Month :n',
    'question_count' => '{1} :count question|[2,*] :count questions',
    'open' => 'Open',
    'opens_in_month' => 'Opens in Month :n',
    'final_review' => 'Final review',
    'final_review_text' => ':count random questions from every month.',
    'final_review_locked' => 'Opens in Month :n, when every month is open.',
    'correct_answer' => 'Correct answer',
];
