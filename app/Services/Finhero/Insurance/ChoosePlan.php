<?php

namespace App\Services\Finhero\Insurance;

use App\Models\User;
use App\Services\InsuranceService;
use App\Services\MoneyFactory\Activities;
use App\Services\PriceRise;

/**
 * FinHero Task 15, «Elige tu seguro médico» (Intermediate; proposal «El seguro
 * médico»: "Compara los dos planes con tres casos y elige uno; puede cambiarlo
 * una vez al año", Required): the two plans, the student works out a year with
 * nothing, a filling, a broken arm and an appendicitis operation, and then
 * chooses (App\Services\InsuranceService). The Task is done when the answers
 * are right and the student has chosen a plan themselves.
 */
class ChoosePlan
{
    public static function stage(int $studentId): array
    {
        $policy = InsuranceService::current($studentId);
        $from = InsuranceService::changeFrom($policy);
        $plans = [];
        foreach (InsuranceService::PLANS as $key => $p) {
            $plans[$key] = [
                'premium' => zeds($p['premium']),
                'deductible' => zeds($p['deductible']),
                'copay' => PriceRise::pct($p['copay'] * 100, false),
                'wait' => $p['dentist_wait'] ? __('insurance.wait_dentist', ['months' => $p['dentist_wait']]) : __('insurance.wait_none'),
            ];
        }

        return [
            'plans' => $plans,
            'current' => $policy ? ['plan' => $policy->plan, 'auto' => $policy->auto, 'since' => $policy->started_on, 'from' => $from] : null,
            'can_choose' => InsuranceService::offered(User::find($studentId)) && InsuranceService::canChoose($studentId),
        ];
    }

    public static function questions(int $studentId): array
    {
        $rand = Activities::random('task_15', $studentId);
        $basic = InsuranceService::PLANS['basic'];
        $complete = InsuranceService::PLANS['complete'];
        $part = fn (string $plan, string $case) => InsuranceService::studentPart($plan, InsuranceService::CASES[$case]);
        $pct = fn (float $share) => PriceRise::pct($share * 100, false);
        $big = InsuranceService::CASES['appendicitis'];

        return [
            Activities::choice($rand, __('insurance.choose.q_year'),
                [zeds($basic['premium'] * 12), zeds($basic['premium']), zeds($basic['deductible']), zeds($complete['premium'] * 12)],
                ['why' => __('insurance.choose.why_year', ['basic' => zeds($basic['premium']), 'year' => zeds($basic['premium'] * 12),
                    'complete' => zeds($complete['premium']), 'complete_year' => zeds($complete['premium'] * 12)])]),
            Activities::choice($rand, __('insurance.choose.q_filling', ['cost' => zeds(InsuranceService::CASES['filling'])]),
                [zeds($part('basic', 'filling')), zeds(InsuranceService::CASES['filling'] * $basic['copay']), zeds($part('complete', 'filling')), zeds(0)],
                ['why' => __('insurance.choose.why_filling', ['deductible' => zeds($basic['deductible']), 'cost' => zeds(InsuranceService::CASES['filling']),
                    'copay' => $pct($complete['copay']), 'complete' => zeds($part('complete', 'filling'))])]),
            Activities::choice($rand, __('insurance.choose.q_arm', ['cost' => zeds(InsuranceService::CASES['arm'])]),
                [zeds($part('basic', 'arm')), zeds(InsuranceService::CASES['arm'] * $basic['copay']), zeds($basic['deductible']), zeds(InsuranceService::CASES['arm'])],
                ['why' => __('insurance.choose.why_arm', ['deductible' => zeds($basic['deductible']), 'copay' => $pct($basic['copay']),
                    'rest' => zeds(InsuranceService::CASES['arm'] - $basic['deductible']),
                    'share' => zeds((InsuranceService::CASES['arm'] - $basic['deductible']) * $basic['copay']),
                    'total' => zeds($part('basic', 'arm')), 'complete_copay' => $pct($complete['copay']),
                    'cost' => zeds(InsuranceService::CASES['arm']), 'complete' => zeds($part('complete', 'arm'))])]),
            Activities::choice($rand, __('insurance.choose.q_big', ['cost' => zeds($big)]),
                [__('insurance.choose.a_complete'), __('insurance.choose.a_basic'), __('insurance.choose.a_same')],
                ['why' => __('insurance.choose.why_big', [
                    'basic_year' => zeds($basic['premium'] * 12), 'deductible' => zeds($basic['deductible']), 'copay' => $pct($basic['copay']),
                    'rest' => zeds($big - $basic['deductible']), 'basic' => zeds(InsuranceService::yearCost('basic', $big)),
                    'complete_year' => zeds($complete['premium'] * 12), 'complete_copay' => $pct($complete['copay']), 'cost' => zeds($big),
                    'complete' => zeds(InsuranceService::yearCost('complete', $big)),
                ])]),
            Activities::choice($rand, __('insurance.choose.q_when'),
                [__('insurance.choose.a_nothing'), __('insurance.choose.a_operation'), __('insurance.choose.a_always')],
                ['why' => __('insurance.choose.why_when')]),
        ];
    }

    /**
     * All the answers right: with a plan in the request, the student chooses it.
     * Done only once they have chosen a plan themselves; until then, 'choose'.
     */
    public static function finish(User $user, array $input): array
    {
        $chosen = null;
        if (!empty($input['plan'])) {
            if ($refusal = InsuranceService::choose($user, (string) $input['plan'])) {
                $policy = InsuranceService::current($user->id);

                return ['complete' => false, 'error' => __('flash.' . $refusal, ['date' => self::date(InsuranceService::changeFrom($policy))])];
            }
            $chosen = InsuranceService::current($user->id);
        }
        $policy = InsuranceService::current($user->id);
        $mine = $policy && !$policy->auto;

        return ['complete' => $mine, 'extra' => [
            'choose' => !$mine,
            'chosen' => $chosen ? __('insurance.choose.chosen', ['plan' => __('insurance.plan_' . $chosen->plan),
                'premium' => zeds(InsuranceService::PLANS[$chosen->plan]['premium']), 'date' => self::date($chosen->started_on->copy()->addYear())]) : null,
        ] + ($mine ? [] : ['message' => __('insurance.choose.now_choose')])];
    }

    public static function date(?\Carbon\Carbon $date): string
    {
        return $date ? $date->copy()->locale(app()->getLocale())->isoFormat('LL') : '';
    }
}
