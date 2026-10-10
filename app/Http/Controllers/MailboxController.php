<?php

namespace App\Http\Controllers;

use App\Models\Mailbox;
use Illuminate\Http\Request;

/**
 * Student mailbox: list, read, and move/star/delete messages. Extracted
 * out of ProfileController (planning.md Phase 3 #4) - fully
 * self-contained, no coupling to any other ProfileController method.
 */
class MailboxController extends Controller
{
    public function mailbox()
    {
        $user = auth()->user();
        $primary = Mailbox::where('type', 'primary')->where('student_id', auth()->id())->orderBy('created_at', 'desc')->get();
        $primarycount = Mailbox::where('type', 'primary')->where('student_id', auth()->id())->where('read', '0')->orderBy('created_at', 'desc')->get();
        $firstPrimaryMail = $primary->first();
        $starred = Mailbox::where('type', 'starred')->where('student_id', auth()->id())->orderBy('created_at', 'desc')->get();
        $firstStarredMail = $starred->first();
        $deleted = Mailbox::where('type', 'deleted')->where('student_id', auth()->id())->orderBy('created_at', 'desc')->get();
        $firstDeletedMail = $deleted->first();

        return view('profile.mailbox', [
            'user' => auth()->user(), // User data
            'primaryMailbox' => $primary, // User data
            'primaryMailboxcount' => count($primarycount), // User data
            'firstPrimaryMail' => $firstPrimaryMail, // User data
            'starredMailbox' => $starred, // User data
            'firstStarredMail' => $firstStarredMail,
            'deletedMailbox' => $deleted, // User data
            'firstDeletedMail' => $firstDeletedMail, // User data
        ]);
    }

    public function showMail($encryptedId)
    {
        try {

            $id = base64_decode($encryptedId);

            $mail = Mailbox::where('id', $id)
                ->where('student_id', auth()->id())
                ->firstOrFail();

            if ($mail->read == 0) {
                $mail->read = 1;
                $mail->save();
            }

            $unreadCount = Mailbox::where('student_id', auth()->id())
                ->where('type', 'primary')
                ->where('read', 0)
                ->count();

            $partial = match ($mail->type) {
                'starred' => 'profile.startedmail',
                'deleted' => 'profile.deletededmail',
                default => 'profile.mail',
            };

            return response()->json([
                'html' => view($partial, compact('mail'))->render(),
                'unreadCount' => $unreadCount
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Invalid request'
            ], 400);

        }
    }

    public function updateMailStatus(Request $request)
    {
        $request->validate([
            'mail_id' => 'required|integer|exists:mailbox,id', // FIXED table name
            'status_type' => 'required|string|in:primary,starred,deleted'
        ]);

        $mail = Mailbox::where('id', $request->mail_id)
            ->where('student_id', auth()->id())
            ->firstOrFail();
        $mail->type = $request->status_type; // Direct assignment
        $mail->save();

        return response()->json(['message' => 'Mail status updated successfully.']);
    }
}
