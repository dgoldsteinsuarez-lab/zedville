<?php

namespace Tests\Concerns;

use App\Models\CurriculumItem;
use App\Models\ExamSession;
use App\Models\User;

/** A real exam is taken only once the tutor opens it for the class today and the student types the password (2026-10-31). */
trait OpensRealExams
{
    protected function openExamFor(User $student, CurriculumItem $item, string $password = 'ZED123'): ExamSession
    {
        $session = ExamSession::updateOrCreate(['item_id' => $item->id, 'class_id' => $student->grade], [
            'exam_date' => today(), 'password' => $password, 'opened_by' => $student->id, 'closed_at' => null,
        ]);
        $this->withSession([$session->unlockKey() => true]);

        return $session;
    }
}
