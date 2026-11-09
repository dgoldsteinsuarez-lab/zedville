<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AssignmentScope;
use App\Support\ClassLabel;
use App\Support\ClassColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Admin > Engagement (Daniel, 2026-10-23): Engagement activities an admin
 * assigns to a class, starting with Substitute Goods ("My Shopping Basket").
 * Same shape as the Needs, Wants & Savings Match assignment
 * (MbaPositionController): a row in mba_position per class (required or
 * optional, with dates) and the matching event on that class's calendar.
 * Before this the activity ran for every student every month, unassigned.
 */
class EngagementActivityController extends Controller
{
    public const SUBSTITUTE_GOODS = 'substitute_goods';
    public const SUBSTITUTE_GOODS_TITLE = 'Substitute Goods: My Shopping Basket';

    /** Classes of the selected school (all classes for a super admin with none selected). */
    private function classes()
    {
        $sid = Auth::user()->isSuperAdmin() ? session('selected_school') : Auth::user()->sid;

        return ClassLabel::classes($sid);
    }

    public function substituteGoods()
    {
        $classes = $this->classes();

        $assignments = DB::table('mba_position as mp')
            ->join('classes as c', 'c.id', '=', 'mp.cid')
            ->leftJoin('grades as cg', 'cg.id', '=', 'c.grade_id')
            ->where('mp.activity_key', self::SUBSTITUTE_GOODS)
            ->whereIn('mp.cid', $classes->pluck('id'))
            ->orderBy('c.name')
            ->get(['mp.*', 'c.name as class_name', 'cg.name as class_grade_name']);

        return view('admin.engagement.substitute-goods', compact('classes', 'assignments'));
    }

    public function storeSubstituteGoods(Request $request)
    {
        $classes = $this->classes();

        $data = $request->validate([
            // One class, or "all classes" of this school / of every school.
            'cid' => ['required', function ($attribute, $value, $fail) use ($classes) {
                if (!AssignmentScope::isMany($value) && !$classes->pluck('id')->contains((int) $value)) {
                    $fail('Choose a class.');
                }
            }],
            'position' => ['required', 'in:required,optional'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        $targets = AssignmentScope::isMany($data['cid'])
            ? AssignmentScope::classes($data['cid'], Auth::user()->isSuperAdmin() ? session('selected_school') : Auth::user()->sid)
            : $classes->where('id', (int) $data['cid']);

        if ($targets->isEmpty()) {
            return back()->with('error', 'There are no classes to assign this to.');
        }

        foreach ($targets as $class) {
            $this->assignSubstituteGoods($class, $data);
        }

        return back()->with('success', 'Substitute Goods assigned to ' . ($targets->count() === 1 ? $targets->first()->name : $targets->count() . ' classes') . '.');
    }

    private function assignSubstituteGoods(object $class, array $data): void
    {
        DB::table('mba_position')->updateOrInsert(
            ['activity_key' => self::SUBSTITUTE_GOODS, 'cid' => $class->id],
            [
                'user_id' => Auth::id(),
                'sid' => $class->sid,
                'required' => $data['position'] === 'required' ? 1 : 0,
                'optional' => $data['position'] === 'optional' ? 1 : 0,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // The calendar shows it on the class calendar; `end` is exclusive
        // there, so the last day is stored one day later (as for the others).
        DB::table('calendar_events')->updateOrInsert(
            ['classId' => $class->id, 'title' => self::SUBSTITUTE_GOODS_TITLE],
            [
                'sid' => $class->sid,
                'description' => 'Substitute Goods: swap the unavailable items in your shopping basket',
                'position' => $data['position'],
                'repeatActivity' => '0',
                'backgroundColor' => ClassColor::hex((int) $class->id),
                'borderColor' => ClassColor::hex((int) $class->id),
                'start' => \Carbon\Carbon::parse($data['start_date'])->startOfDay(),
                'end' => \Carbon\Carbon::parse($data['end_date'])->addDay()->startOfDay(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function deleteSubstituteGoods($id)
    {
        $assignment = DB::table('mba_position')
            ->where('id', $id)->where('activity_key', self::SUBSTITUTE_GOODS)
            ->whereIn('cid', $this->classes()->pluck('id'))
            ->first();
        abort_unless($assignment, 404);

        DB::table('calendar_events')->where('classId', $assignment->cid)->where('title', self::SUBSTITUTE_GOODS_TITLE)->delete();
        DB::table('mba_position')->where('id', $assignment->id)->delete();

        return back()->with('success', 'Substitute Goods removed from that class.');
    }

    /** Is the activity running for this student's class today? */
    public static function substituteGoodsActiveFor($user): bool
    {
        if (!$user->grade) {
            return false;
        }

        return DB::table('mba_position')
            ->where('activity_key', self::SUBSTITUTE_GOODS)
            ->where('cid', $user->grade)
            ->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->exists();
    }
}
