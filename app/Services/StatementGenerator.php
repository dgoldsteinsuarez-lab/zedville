<?php
namespace App\Services;

use App\Models\Transaction1;
use App\Models\BankStatement;
use App\Models\PenaltyWant;
use App\Models\GroceryCost;
use App\Models\BudgetConfig;
use App\Models\BankAccount;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Exception;

class StatementGenerator
{
    protected $attemptLimit = 50;

    public function getActiveBudgetConfig()
    {
        // get latest effective config
        return BudgetConfig::orderBy('effective_date', 'desc')->first();
    }

    public function generateForUser(int $userId, float $manualWants = 0.0)
    {
        return DB::transaction(function () use ($userId, $manualWants) {

            $user = User::findOrFail($userId);
            $bankAccount = BankAccount::where('student_id', $userId)->first();
            $now = Carbon::now();
            //Monthaly Gurd
            // 🔐 MONTHLY GUARD (ADD HERE)

            // 🔍 Has a statement ever been generated?
            $hasAnyStatement = BankStatement::where('user_id', $userId)->exists();

            // 🚫 Has a statement been generated for CURRENT month?
            $hasStatementThisMonth = BankStatement::where('user_id', $userId)
                ->where('year', $now->year)
                ->where('month', $now->month)
                ->first();

            // ❌ Never generate twice in the same month
            if ($hasStatementThisMonth) {
                return null;
            }

            // 🆕 FIRST EVER STATEMENT → generate immediately
            if (!$hasAnyStatement) {
                // allow generation
            }
            // 📅 AFTER FIRST STATEMENT → only on/after 6th
            elseif ($now->day < 6) {
                return null;
            }

            // Prevent duplicate statement for same month
            $alreadyGenerated = BankStatement::where('user_id', $userId)
                ->where('month', $now->month)
                ->where('year', $now->year)
                ->exists();

            if ($alreadyGenerated) {
                return null;
            }

            //Monthaly Gurd
            $config = $this->getActiveBudgetConfig();
            $monthlySalary = $config->monthly_salary ?? config('zedville.monthly_salary', 3952.40);

            // get grocery cost based on diet type
            /*$dietType = $user->diet_type ?? (['vegetarian','vegan','omnivore','pescatarian'][array_rand(range(0,3))]);
            $grocery = GroceryCost::find($dietType);
            $groceryCost = $grocery->monthly_cost ?? 129.14;*/

            $statement = [
                'studentId' => $user->id,
                'studentName' => $user->nickname,
                'month' => $now->month,
                'year' => $now->year,
                'transactions' => [],
                'summary' => [
                    'totalIncome' => 0,
                    'totalNeeds' => 0,
                    'totalWants' => 0,
                    'totalSavings' => 0,
                    'needsPercentage' => 0,
                    'wantsPercentage' => 0,
                    'savingsPercentage' => 0
                ]
            ];

            $runningBalance = 0.0;

            // 1) Salary credit
            //$salaryDate = $this->getFirstFridayOfMonth($now->year, $now->month);
            //$salaryDate = Carbon::create($now->year, $now->month, 10, 9, 0, 0);
            //$salaryDate = Carbon::create($now->year, $now->month, 6, 9, 0, 0);
            if (!$hasAnyStatement) {
                // 🆕 First time salary → current / account creation date
                $salaryDate = $user->created_at
                    ? $user->created_at->copy()->setTime(9, 0)
                    : Carbon::now();
            } else {
                // 📅 Normal monthly salary → first Friday (6th in your case)
                //$salaryDate = $this->getFirstFridayOfMonth($now->year, $now->month);
                $salaryDate = Carbon::create($now->year, $now->month, 6, 9, 0, 0);
            }


            $statement['transactions'][] = [
                'date' => $salaryDate->toDateTimeString(),
                'description' => 'Monthly Salary Credit - ' . $now->format('F Y'),
                'type' => 'credit',
                'amount' => $monthlySalary,
                'category' => 'Income'
            ];
            $runningBalance += $monthlySalary;
            $statement['summary']['totalIncome'] = $monthlySalary;

            Transaction1::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'bank_account_id' => $bankAccount->id ?? null,
                'transaction_date' => $salaryDate->toDateTimeString(),
                'description' => 'Monthly Salary Credit - ' . $now->format('F Y'),
                'type' => 'credit',
                'category' => 'Income',
                'amount' => $monthlySalary,
                'balance' => $runningBalance,
                'is_penalty' => false
            ]);
            //Automatic emmfund
            // ✅ Emergency Fund AFTER salary (correct order)
            if ($bankAccount && $bankAccount->is_open_emergency_account == 1) {

                $emergencyPercent = 20;
                $emergencyAmount = round(($monthlySalary * $emergencyPercent) / 100, 2);

                $runningBalance -= $emergencyAmount;

                $previousEmergency = $bankAccount->emergency_fund_account_amount ?? 0;
                $newEmergencyTotal = $previousEmergency + $emergencyAmount;

                $bankAccount->update([
                    'emergency_fund_account_amount' => $newEmergencyTotal
                ]);

                // Add to statement
                $statement['transactions'][] = [
                    'date' => $salaryDate->toDateTimeString(),
                    'description' => 'Auto Transfer to Emergency Fund',
                    'type' => 'debit',
                    'amount' => $emergencyAmount,
                    'category' => 'Savings'
                ];

                $statement['summary']['totalSavings'] += $emergencyAmount;

                // ✅ DB insert AFTER salary
                Transaction1::create([
                    'user_id' => $user->id,
                    'sid' => $user->sid,
                    'bank_account_id' => $bankAccount->id ?? null,
                    'transaction_date' => $salaryDate->toDateTimeString(),
                    'description' => 'Auto Transfer to Emergency Fund',
                    'type' => 'debit',
                    'category' => 'Savings',
                    'amount' => $emergencyAmount,
                    'balance' => $runningBalance,
                    'is_penalty' => false
                ]);
            }
            //Automatic emmfund
            // 2) Fixed needs
            $fixedNeeds = DB::table('city_mall_items')->where('item_type', 'needs_fixed')->get();
            foreach ($fixedNeeds as $need) {
                $date = $this->randomDateInMonth($now->year, $now->month, $need->due_date ?? 6, $need->due_date ?? 6);
                $runningBalance -= (float) $need->price;

                $statement['transactions'][] = [
                    'date' => $date->toDateTimeString(),
                    'description' => $need->bank_statement_tag ?? $need->item_name,
                    'type' => 'debit',
                    'amount' => (float) $need->price,
                    'category' => 'Needs'
                ];
                $statement['summary']['totalNeeds'] += (float) $need->price;

                Transaction1::create([
                    'user_id' => $user->id,
                    'sid' => $user->sid,
                    'bank_account_id' => $bankAccount->id ?? null,
                    'transaction_date' => $date->toDateTimeString(),
                    'description' => $need->bank_statement_tag ?? $need->item_name,
                    'type' => 'debit',
                    'category' => 'Needs',
                    'amount' => (float) $need->price,
                    'balance' => $runningBalance,
                    'is_penalty' => false
                ]);
            }

            // 3) Groceries
            /*$startDay = $salaryDate->day + 1;
            $endDay = min($startDay + 6, $now->daysInMonth);
            $gDate = $this->randomDateInMonth($now->year, $now->month, $startDay, $endDay);

            $runningBalance -= $groceryCost;
            $statement['transactions'][] = [
                'date' => $gDate->toDateTimeString(),
                'description' => $grocery->bank_statement_tag ?? 'Groceries',
                'type' => 'debit',
                'amount' => $groceryCost,
                'category' => 'Needs'
            ];
            $statement['summary']['totalNeeds'] += $groceryCost;

            Transaction1::create([
                'user_id' => $user->id,
                'bank_account_id' => $bankAccount->id ?? null,
                'transaction_date' => $gDate->toDateTimeString(),
                'description' => $grocery->bank_statement_tag ?? 'Groceries',
                'type' => 'debit',
                'category' => 'Needs',
                'amount' => $groceryCost,
                'balance' => $runningBalance,
                'is_penalty' => false
            ]);*/

            // Final percentages
            $statement['summary']['needsPercentage'] = round(($statement['summary']['totalNeeds'] / $monthlySalary) * 100, 2);
            $statement['summary']['wantsPercentage'] = round(($statement['summary']['totalWants'] / $monthlySalary) * 100, 2);
            $statement['summary']['savingsPercentage'] = round(($statement['summary']['totalSavings'] / $monthlySalary) * 100, 2);

            // Store bank statement JSON **without any penalty**
            $bs = BankStatement::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'month' => $now->month,
                'year' => $now->year,
                'statement_data' => json_encode($statement),
                'penalty_applied' => 0
            ]);

            return [
                'statement' => $statement,
                'bank_statement_id' => $bs->id
            ];
        });
    }


    protected function getFirstFridayOfMonth($year, $month)
    {
        //$dt = Carbon::create($year, $month, 1, 0,0,0);
        $dt = Carbon::create($year, $month, 1, 9, 0, 0);
        $dow = $dt->dayOfWeek; // 0 Sun .. 6 Sat
        $daysUntilFriday = (5 - $dow + 7) % 7;
        return $dt->addDays($daysUntilFriday);
    }

    protected function randomDateInMonth($year, $month, $minDay = 1, $maxDay = 28)
    {
        $day = rand($minDay, $maxDay);
        return Carbon::create($year, $month, $day, rand(8, 20), rand(0, 59), 0);
    }
}
