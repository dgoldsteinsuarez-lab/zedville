<?php

namespace App\Http\Controllers;

use App\Services\WantsPlanService;
use Illuminate\Http\Request;

/** The student's choice for the month in Spending 4 (the box after the question of the day, 2026-10-31). */
class WantsPlanController extends Controller
{
    public function choose(Request $request)
    {
        // one plan, nothing else (Daniel, 2026-10-31: "solo eligen una actividad de Spending 4")
        $data = $request->validate(['plan_id' => ['required', 'integer']]);
        abort_unless(WantsPlanService::needsChoice($request->user()), 409);

        return response()->json(WantsPlanService::choose($request->user(), (int) $data['plan_id']));
    }
}
