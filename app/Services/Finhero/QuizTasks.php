<?php

namespace App\Services\Finhero;

use App\Services\Finhero\CompoundInterest\EarlyStart;
use App\Services\Finhero\CompoundInterest\RuleOf72;
use App\Services\Finhero\CompoundInterest\Snowball;
use App\Services\Finhero\Inflation\LeftToSave;
use App\Services\Finhero\Inflation\MoveTheLine;
use App\Services\Finhero\Inflation\MyBasket;
use App\Services\Finhero\Inflation\MySalary;
use App\Services\Finhero\Inflation\RealInterest;
use App\Services\Finhero\Inflation\WhatRose;
use App\Services\Finhero\Insurance\ChoosePlan;
use App\Services\MoneyFactory\Activities;
use App\Models\User;

/**
 * FinHero Tasks made of a chart and questions to choose an answer for, graded
 * on the server (the answers never reach the page), for the Intermediate:
 * compound interest, Tasks 6-8 (Daniel, 2026-11-08: financial education, "no
 * tanto a la circulación del dinero" - not the Money Factory), and the price
 * rise of its second month, Tasks 9-13 (what went up, my shopping before and
 * after, what my salary is worth, what is left to save, the real interest)
 * and Task 14, the macro chart behind it, where the student moves the line;
 * Task 15, the health insurance: work out the cases, then choose a plan.
 * Each student gets their own numbers; a task is done when every answer is
 * right (and, with a finish() of its own, when that says so). The questions
 * use the Money Factory's format (MoneyFactory\Activities::choice); 'lang' is
 * the file of the task's texts.
 */
class QuizTasks
{
    public const ALL = [
        'task_6' => ['class' => Snowball::class, 'key' => 'snowball', 'icon' => '⛄', 'lang' => 'compound'],
        'task_7' => ['class' => EarlyStart::class, 'key' => 'early', 'icon' => '⏳', 'lang' => 'compound'],
        'task_8' => ['class' => RuleOf72::class, 'key' => 'rule72', 'icon' => '✖️', 'lang' => 'compound'],
        'task_9' => ['class' => WhatRose::class, 'key' => 'rise', 'icon' => '📈', 'lang' => 'inflation'],
        'task_10' => ['class' => MyBasket::class, 'key' => 'basket', 'icon' => '🧺', 'lang' => 'inflation'],
        'task_11' => ['class' => MySalary::class, 'key' => 'salary', 'icon' => '💶', 'lang' => 'inflation'],
        'task_12' => ['class' => LeftToSave::class, 'key' => 'left', 'icon' => '🐷', 'lang' => 'inflation'],
        'task_13' => ['class' => RealInterest::class, 'key' => 'real', 'icon' => '🏦', 'lang' => 'inflation'],
        'task_14' => ['class' => MoveTheLine::class, 'key' => 'shift', 'icon' => '⚖️', 'lang' => 'inflation'],
        'task_15' => ['class' => ChoosePlan::class, 'key' => 'choose', 'icon' => '🩺', 'lang' => 'insurance'],
    ];

    public static function has(string $activityKey): bool
    {
        return isset(self::ALL[$activityKey]);
    }

    /** The questions, with the right answers and the explanations. */
    public static function questions(string $activityKey, int $studentId): array
    {
        return self::ALL[$activityKey]['class']::questions($studentId);
    }

    /** What the page shows above the questions. */
    public static function stage(string $activityKey, int $studentId): array
    {
        return self::ALL[$activityKey]['class']::stage($studentId);
    }

    /** The questions as the page gets them: without the answers and the explanations. */
    public static function forPage(array $questions): array
    {
        return Activities::forPage($questions);
    }

    /** @return array<int, bool> whether each answer is right */
    public static function grade(array $questions, array $answers): array
    {
        return Activities::grade($questions, $answers);
    }

    /**
     * Every answer right: what else the task does with the request (Task 15
     * chooses the plan sent). ['complete' => whether the task is done now,
     * 'extra' => more for the page, 'error' => why the request was refused].
     */
    public static function finish(string $activityKey, User $user, array $input): array
    {
        $class = self::ALL[$activityKey]['class'];

        return method_exists($class, 'finish') ? $class::finish($user, $input) : ['complete' => true];
    }
}
