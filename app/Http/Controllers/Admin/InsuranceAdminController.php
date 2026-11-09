<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsurancePolicy;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Services\InsuranceService;
use Illuminate\Http\Request;

/**
 * Admin > Bank > Health Insurance (proposal step 12: "los dos planes y sus
 * precios"): the two plans and each student's current one - chosen by the
 * student or given by the City Hall - with the premiums paid. The plans are
 * fixed (App\Services\InsuranceService); read-only.
 */
class InsuranceAdminController extends Controller
{
    public function index(Request $request)
    {
        $ids = $request->user()->manageableStudentIds();
        $policies = InsurancePolicy::whereNull('ended_on')
            ->when($ids !== null, fn ($q) => $q->whereIn('user_id', $ids))
            ->latest('started_on')->get();
        $students = \App\Models\User::whereIn('id', $policies->pluck('user_id'))->get(['id', 'name', 'nickname', 'grade'])->keyBy('id');
        $classes = SchoolClass::whereIn('id', $students->pluck('grade')->filter())->pluck('name', 'id');
        $premiums = Transaction1::whereIn('user_id', $policies->pluck('user_id'))->where('description', InsuranceService::LINE)
            ->selectRaw('user_id, SUM(amount) as total')->groupBy('user_id')->pluck('total', 'user_id');

        return view('admin.insurance.index', [
            'policies' => $policies,
            'students' => $students,
            'classes' => $classes,
            'premiums' => $premiums,
            'plans' => InsuranceService::PLANS,
        ]);
    }
}
