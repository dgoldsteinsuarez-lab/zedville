<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Referendum;
use App\Models\Petition;
use App\Models\ReferendumVote;
use App\Models\PetitionSignature;
use Illuminate\Support\Facades\DB;

class CivicChamberController extends Controller
{
    /**
     * Petitions have no school/class scoping enforced elsewhere, so every
     * query that lists or shows them must go through this. Super Admin sees
     * everything; School Admin is scoped to their own school; Tutor is
     * scoped to only the classes they're actually assigned to (via
     * User::tutoredClasses(), the same source used by the Tutor's student
     * list).
     */
    /**
     * Same idea for referendums: Super Admin everything, School Admin their
     * school, Tutor only referendums attached to their own classes.
     */
    private function scopeReferendumsToViewer($query)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isSchoolAdmin()) {
            return $query->where('sid', $user->sid);
        }

        if ($user->isTutor()) {
            return $query->whereIn('class_id', $user->tutoredClasses()->pluck('classes.id'));
        }

        return $query->whereRaw('1 = 0');
    }

    private function scopePetitionsToViewer($query)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isSchoolAdmin()) {
            return $query->where('sid', $user->sid);
        }

        if ($user->isTutor()) {
            $classIds = $user->tutoredClasses()->pluck('classes.id');
            return $query->whereIn('class_id', $classIds);
        }

        // Unknown/other role reaching this point (shouldn't happen given the
        // route middleware) — show nothing rather than everything.
        return $query->whereRaw('1 = 0');
    }

    /*
    |--------------------------------------------------------------------------
    | REFERENDUM LIST
    |--------------------------------------------------------------------------
    */

    public function referendumIndex()
    {
        $referendums = $this->scopeReferendumsToViewer(Referendum::query())->latest()->paginate(15);

        return view('admin.civic-chamber.referendum.index', compact('referendums'));
    }

    /*
    |--------------------------------------------------------------------------
    | REFERENDUM CREATE PAGE
    |--------------------------------------------------------------------------
    */

    public function referendumCreate()
    {
        $tutoredClasses = Auth::user()->isTutor()
            ? Auth::user()->tutoredClasses()->with('grade')->get()
            : collect();

        return view('admin.civic-chamber.referendum.create', compact('tutoredClasses'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE REFERENDUM
    |--------------------------------------------------------------------------
    */

    public function referendumStore(Request $request)
    {

        $request->validate([

            'question'    => 'required|max:255',

            'description' => 'nullable',

            'status'      => 'required|in:open,closed',

            'start_date'  => 'nullable|date',

            'end_date' => 'nullable|date|after_or_equal:start_date',

            // 'school' = school-wide (admins only), 'grade' = the picked Grade,
            // 'class' = one specific Class the Tutor is assigned to.
            'scope'       => 'nullable|in:school,grade,class',

            'scope_id'    => 'nullable|integer',

        ]);

        $user = Auth::user();
        $classId = null;
        $gradeId = null;

        if ($user->isTutor()) {
            // scope_id is always one of the Tutor's own Class ids, chosen from
            // the picker - never trust it blindly. "grade" scope resolves that
            // Class's parent Grade instead of using the Class itself.
            $class = $user->tutoredClasses()->with('grade')->where('classes.id', $request->scope_id)->first()
                ?? $user->tutoredClasses()->with('grade')->first();

            if ($request->input('scope', 'class') === 'grade') {
                $gradeId = $class?->grade_id;
            } else {
                $classId = $class?->id;
            }
        }

        Referendum::create([

            'question'    => $request->question,

            'description' => $request->description,

            'status'      => $request->status,

            'start_date'  => $request->start_date,

            'end_date'    => $request->end_date,

            'created_by'  => Auth::id(),

            'sid'         => $user->sid,

            'class_id'    => $classId,

            'grade_id'    => $gradeId,

        ]);

        return redirect()
            ->route('admin.referendum.index')
            ->with('success','Referendum Created Successfully');

    }

    /*
    |--------------------------------------------------------------------------
    | EDIT REFERENDUM
    |--------------------------------------------------------------------------
    */

    public function referendumEdit($id)
    {

        $referendum = $this->scopeReferendumsToViewer(Referendum::query())->findOrFail($id);

        return view(
            'admin.civic-chamber.referendum.edit',
            compact('referendum')
        );

    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE REFERENDUM
    |--------------------------------------------------------------------------
    */

    public function referendumUpdate(Request $request, $id)
    {

        $request->validate([

            'question'    => 'required|max:255',

            'description' => 'nullable',

            'status'      => 'required|in:open,closed',

            'start_date'  => 'nullable|date',

            'end_date'    => 'nullable|date',

        ]);

        $referendum = $this->scopeReferendumsToViewer(Referendum::query())->findOrFail($id);

        $referendum->update([

            'question'    => $request->question,

            'description' => $request->description,

            'status'      => $request->status,

            'start_date'  => $request->start_date,

            'end_date'    => $request->end_date,

        ]);

        return redirect()
            ->route('admin.referendum.index')
            ->with('success','Referendum Updated Successfully');

    }

    /*
    |--------------------------------------------------------------------------
    | DELETE REFERENDUM
    |--------------------------------------------------------------------------
    */

    public function referendumDestroy($id)
    {

        $referendum = $this->scopeReferendumsToViewer(Referendum::query())->findOrFail($id);

        ReferendumVote::where('referendum_id', $id)->delete();

        $referendum->delete();

        return back()->with('success','Referendum Deleted Successfully');

    }
        /*
    |--------------------------------------------------------------------------
    | PETITION LIST
    |--------------------------------------------------------------------------
    */

    public function petitionIndex()
    {
        $petitions = $this->scopePetitionsToViewer(
            Petition::with('signatures', 'creator')
        )
        ->latest()
        ->paginate(15);

        return view(
            'admin.civic-chamber.petition.index',
            compact('petitions')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PETITION DETAILS
    |--------------------------------------------------------------------------
    */

    public function petitionShow($id)
    {
        $petition = $this->scopePetitionsToViewer(
            Petition::with('signatures', 'creator')
        )->findOrFail($id);

        return view(
            'admin.civic-chamber.petition.show',
            compact('petition')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE PETITION
    |--------------------------------------------------------------------------
    */

    public function petitionApprove(Request $request, $id)
    {

        $petition = $this->scopePetitionsToViewer(Petition::with('signatures'))->findOrFail($id);

        $petition->update([

            'status' => 'approved',

            'tutor_feedback' => $request->tutor_feedback,

        ]);

        return back()->with(
            'success',
            'Petition Approved Successfully'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | REJECT PETITION
    |--------------------------------------------------------------------------
    */

    public function petitionReject(Request $request, $id)
    {

        $petition = $this->scopePetitionsToViewer(Petition::with('signatures'))->findOrFail($id);

        $petition->update([

            'status' => 'rejected',

            'tutor_feedback' => $request->tutor_feedback,

        ]);

        return back()->with(
            'success',
            'Petition Rejected Successfully'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | CLOSE PETITION
    |--------------------------------------------------------------------------
    */

    public function petitionClose($id)
    {

        $petition = $this->scopePetitionsToViewer(Petition::with('signatures'))->findOrFail($id);

        $petition->update([

            'status' => 'closed',

        ]);

        return back()->with(
            'success',
            'Petition Closed Successfully'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE TUTOR FEEDBACK
    |--------------------------------------------------------------------------
    */

    public function petitionFeedback(Request $request, $id)
    {

        $request->validate([

            'tutor_feedback' => 'required',

        ]);

        $petition = $this->scopePetitionsToViewer(Petition::with('signatures'))->findOrFail($id);

        $petition->update([

            'tutor_feedback' => $request->tutor_feedback,

        ]);

        return back()->with(
            'success',
            'Tutor Feedback Updated Successfully'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PETITION
    |--------------------------------------------------------------------------
    */

    public function petitionDestroy($id)
    {

        $petition = $this->scopePetitionsToViewer(Petition::with('signatures'))->findOrFail($id);

        PetitionSignature::where('petition_id', $id)->delete();

        $petition->delete();

        return back()->with(
            'success',
            'Petition Deleted Successfully'
        );

    }
        /*
    |--------------------------------------------------------------------------
    | CITY NAME / QUOTE PROPOSALS (Admin Roles & Permissions doc §2.4/§3)
    |--------------------------------------------------------------------------
    | Scoped by Grade (never class, never whole-school) - changing the city
    | name/quote is always Grade-wide. A Tutor may approve for any Grade
    | they have at least one Class in, not just their own sid.
    */

    private function scopeCityProposalsToViewer($query)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isSchoolAdmin()) {
            return $query->where('sid', $user->sid);
        }

        if ($user->isTutor()) {
            $gradeIds = $user->tutoredClasses()->pluck('classes.grade_id')->filter()->unique();
            return $query->whereIn('grade_id', $gradeIds);
        }

        return $query->whereRaw('1 = 0');
    }

    public function cityProposalsIndex()
    {
        $proposals = $this->scopeCityProposalsToViewer(
            \App\Models\CityProposal::with(['proposer:id,name,nickname', 'grade:id,name'])->orderByDesc('id')
        )->paginate(20);

        return view('admin.civic-chamber.city-proposals.index', compact('proposals'));
    }

    public function cityProposalApprove($id)
    {
        $proposal = $this->scopeCityProposalsToViewer(\App\Models\CityProposal::query())->findOrFail($id);

        $proposal->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Proposal approved - it is now live in City Hall.');
    }

    public function cityProposalReject($id)
    {
        $proposal = $this->scopeCityProposalsToViewer(\App\Models\CityProposal::query())->findOrFail($id);

        $proposal->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Proposal rejected.');
    }
}
