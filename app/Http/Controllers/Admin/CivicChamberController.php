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
        return view('admin.civic-chamber.referendum.create');
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

        ]);

        Referendum::create([

            'question'    => $request->question,

            'description' => $request->description,

            'status'      => $request->status,

            'start_date'  => $request->start_date,

            'end_date'    => $request->end_date,

            'created_by'  => Auth::id(),

            'sid'         => Auth::user()->sid,

            // Tutor: scoped to their (first) assigned class. Admins: null = school-wide.
            'class_id'    => Auth::user()->isTutor()
                ? Auth::user()->tutoredClasses()->value('classes.id')
                : null,

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
    | STUDENT : CAST REFERENDUM VOTE
    |--------------------------------------------------------------------------
    */

    public function castVote(Request $request)
    {

        $request->validate([

            'referendum_id' => 'required|exists:referendums,id',

            'vote' => 'required|in:yes,no',

        ]);

        $studentId = Auth::id();

        $alreadyVoted = ReferendumVote::where('referendum_id', $request->referendum_id)
                            ->where('student_id', $studentId)
                            ->exists();

        if ($alreadyVoted) {

            return response()->json([

                'status' => false,

                'message' => 'You have already voted.'

            ]);

        }

        ReferendumVote::create([

            'referendum_id' => $request->referendum_id,

            'student_id' => $studentId,

            'vote' => $request->vote,

            'created_at' => now(),

        ]);

        return response()->json([

            'status' => true,

            'message' => 'Vote submitted successfully.'

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT : CREATE PETITION
    |--------------------------------------------------------------------------
    */

    public function submitPetition(Request $request)
    {

        $request->validate([

            'title' => 'required|max:255',

            'description' => 'required',

        ]);

        Petition::create([

            'title' => $request->title,

            'description' => $request->description,

            'created_by' => Auth::id(),

            'status' => 'pending',
            'created_at' => now(),

        ]);

        return response()->json([

            'status' => true,

            'message' => 'Petition submitted successfully.'

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT : SIGN PETITION
    |--------------------------------------------------------------------------
    */

    public function signPetition($id)
    {
        // Student-facing action, not admin/tutor — scopePetitionsToViewer()
        // assumes an admin-tier role and must not be applied here.
        $petition = Petition::with('signatures')->findOrFail($id);

        $studentId = Auth::id();

        if ($petition->created_by == $studentId) {

            return response()->json([

                'status' => false,

                'message' => 'You cannot sign your own petition.'

            ]);

        }

        $alreadySigned = PetitionSignature::where('petition_id', $id)
                            ->where('student_id', $studentId)
                            ->exists();

        if ($alreadySigned) {

            return response()->json([

                'status' => false,

                'message' => 'You have already signed this petition.'

            ]);

        }

        PetitionSignature::create([

            'petition_id' => $id,

            'student_id' => $studentId,

            'created_at' => now(),

        ]);

        return response()->json([

            'status' => true,

            'message' => 'Petition signed successfully.'

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT : CIVIC CHAMBER PAGE
    |--------------------------------------------------------------------------
    */

    public function civicChamber()
    {

        $studentId = Auth::id();

        $openReferendums = Referendum::where('status','open')
                            ->latest()
                            ->get();

        $closedReferendums = Referendum::where('status','closed')
                            ->latest()
                            ->get();

        $activePetitions = Petition::whereIn('status',['pending','approved'])
                            ->latest()
                            ->get();

        $pastPetitions = Petition::whereIn('status',['rejected','closed'])
                            ->latest()
                            ->get();

        return view(
            'education.city-hall.civic-chamber',
            compact(
                'studentId',
                'openReferendums',
                'closedReferendums',
                'activePetitions',
                'pastPetitions'
            )
        );

    }

    /*
    |--------------------------------------------------------------------------
    | REFERENDUM RESULT
    |--------------------------------------------------------------------------
    */

    public function referendumResult($id)
    {

        $yes = ReferendumVote::where('referendum_id',$id)
                    ->where('vote','yes')
                    ->count();

        $no = ReferendumVote::where('referendum_id',$id)
                    ->where('vote','no')
                    ->count();

        return response()->json([

            'yes' => $yes,

            'no' => $no,

            'total' => $yes + $no,

        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | PETITION SIGNATURE COUNT
    |--------------------------------------------------------------------------
    */

    public function petitionSignatureCount($id)
    {

        $count = PetitionSignature::where('petition_id',$id)->count();

        return response()->json([

            'count' => $count

        ]);

    }
}
