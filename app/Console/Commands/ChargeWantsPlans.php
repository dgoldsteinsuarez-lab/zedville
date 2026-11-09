<?php

namespace App\Console\Commands;

use App\Services\WantsPlanService;
use Illuminate\Console\Command;

/**
 * Pays the plan-of-the-month payments that are due (every Saturday, every
 * other Saturday): one bank line each, as a Want. A payment there's no money
 * for is never taken below zero: it waits and is tried again every day, and
 * right after the salary (App\Services\WantsPlanService).
 */
class ChargeWantsPlans extends Command
{
    protected $signature = 'plans:charge';
    protected $description = 'Pay the due plan-of-the-month payments';

    public function handle()
    {
        $this->info('Paid ' . WantsPlanService::chargeDue() . ' plan payment(s).');

        return self::SUCCESS;
    }
}
