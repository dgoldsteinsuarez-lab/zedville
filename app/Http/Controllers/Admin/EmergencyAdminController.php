<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Models\EmergencySetting;
use App\Models\Fraud;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\EmergencyService;
use App\Support\AdminLevel;
use App\Support\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin > level > Emergencies (proposal step 13: "Activarlo por nivel; los
 * meses según el escenario; la lista de imprevistos"): whether the level's
 * students get them, in which of their months for each scenario, the list of
 * emergencies and what each student got and how they paid it
 * (App\Services\EmergencyService). And the frauds (step 15: "Activarlos y
 * elegir los meses"): on or off, the months of the call and the fake email,
 * and what each student did (App\Services\FraudService).
 */
class EmergencyAdminController extends Controller
{
    public function index()
    {
        $level = AdminLevel::current();
        abort_unless($level && (int) $level->sort_order >= Level::INTERMEDIATE, 404);
        $sid = $this->school();
        $classes = SchoolClass::with('grade')->when($sid, fn ($q) => $q->where('sid', $sid))
            ->whereHas('grade', fn ($g) => $g->where('grade_level_id', $level->id))->get()->keyBy('id');
        $students = User::where('role', 4)->whereIn('grade', $classes->keys())->get(['id', 'name', 'nickname', 'grade'])->keyBy('id');
        $emergencies = Emergency::whereIn('user_id', $students->keys())->latest('arrived_at')->get();

        return view('admin.emergencies.index', [
            'level' => $level,
            'settings' => EmergencyService::settings($level->id),
            'emergencies' => $emergencies,
            // the frauds (App\Services\FraudService): what each student did
            'frauds' => Fraud::whereIn('user_id', $students->keys())->latest('arrived_at')->get(),
            'students' => $students,
            'classes' => $classes,
        ]);
    }

    public function update(Request $request)
    {
        $level = AdminLevel::current();
        abort_unless($level && (int) $level->sort_order >= Level::INTERMEDIATE, 404);
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'months_a' => ['required', 'string', 'max:50'],
            'months_b' => ['required', 'string', 'max:50'],
            'frauds_enabled' => ['nullable', 'boolean'],
            'call_month' => ['required', 'integer', 'between:1,12'],
            'email_month' => ['required', 'integer', 'between:1,12'],
        ]);
        $a = EmergencyService::months($data['months_a']);
        $b = EmergencyService::months($data['months_b']);
        if (!$a || !$b) {
            return back()->withInput()->with('error', 'Write at least one month (1 to 12) for each scenario, e.g. 3, 6, 9.');
        }
        EmergencySetting::updateOrCreate(['grade_level_id' => $level->id], [
            'enabled' => (bool) ($data['enabled'] ?? false), 'months_a' => implode(',', $a), 'months_b' => implode(',', $b),
            'frauds_enabled' => (bool) ($data['frauds_enabled'] ?? false), 'call_month' => (int) $data['call_month'], 'email_month' => (int) $data['email_month'],
        ]);

        return back()->with('success', 'Emergencies and frauds saved for ' . $level->name . '.');
    }

    /** The school being managed; a school admin's own. */
    private function school()
    {
        return session('selected_school') ?: (Auth::user()->isSuperAdmin() ? null : Auth::user()->sid);
    }
}
