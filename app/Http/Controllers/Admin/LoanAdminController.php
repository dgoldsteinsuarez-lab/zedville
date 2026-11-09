<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\SchoolClass;
use App\Services\LoanService;
use Illuminate\Http\Request;

/**
 * Admin > Bank > Loans (proposal step 11: "préstamos concedidos, impagos y sus
 * condiciones"): every personal loan of the students the admin manages, with
 * its terms, how many instalments are paid and how many came late. The rules
 * themselves are fixed (App\Services\LoanService); read-only.
 */
class LoanAdminController extends Controller
{
    public function index(Request $request)
    {
        $ids = $request->user()->manageableStudentIds();
        $loans = Loan::with(['instalments'])
            ->when($ids !== null, fn ($q) => $q->whereIn('user_id', $ids))
            ->latest()->get();
        $students = \App\Models\User::whereIn('id', $loans->pluck('user_id'))->get(['id', 'name', 'nickname', 'grade'])->keyBy('id');
        $classes = SchoolClass::whereIn('id', $students->pluck('grade')->filter())->pluck('name', 'id');

        return view('admin.loans.index', [
            'loans' => $loans,
            'students' => $students,
            'classes' => $classes,
            'rules' => [
                'min' => LoanService::MIN, 'max' => LoanService::MAX, 'terms' => LoanService::TERMS, 'tae' => LoanService::TAE * 100,
                'share' => LoanService::MAX_SHARE * 100, 'cancel' => LoanService::CANCEL_DAYS, 'late' => LoanService::LATE_LIMIT, 'block' => LoanService::BLOCK_MONTHS,
            ],
        ]);
    }
}
