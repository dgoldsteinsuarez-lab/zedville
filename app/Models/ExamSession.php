<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A real exam opened by a tutor for one class (Daniel, 2026-10-31: "quiero
 * que el tutor pueda abrir o poner el examen para el día concreto del
 * examen ... el tutor les da una contraseña para que puedan abrir el
 * examen. El examen online lo corrige el sistema y da automáticamente el
 * resultado al tutor y al alumno cuando el tutor lo libere").
 *
 * Open on exam_date only (unless closed earlier); the student types the
 * password once; the mark is the system's, seen by the tutor at once and by
 * the student after results_released_at.
 */
class ExamSession extends Model
{
    protected $fillable = ['item_id', 'class_id', 'exam_date', 'password', 'opened_by', 'closed_at', 'results_released_at'];

    protected $casts = ['exam_date' => 'date', 'closed_at' => 'datetime', 'results_released_at' => 'datetime'];

    public function item()
    {
        return $this->belongsTo(CurriculumItem::class, 'item_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public static function forStudent(CurriculumItem $item, User $student): ?self
    {
        return $student->grade ? self::where('item_id', $item->id)->where('class_id', $student->grade)->first() : null;
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null && $this->exam_date->isToday();
    }

    public function passwordMatches(?string $typed): bool
    {
        return $typed !== null && strcasecmp(trim($typed), $this->password) === 0;
    }

    /** Six characters a class can read off the board: no 0/O or 1/I/L. */
    public static function newPassword(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        return collect(range(1, 6))->map(fn () => $chars[random_int(0, strlen($chars) - 1)])->implode('');
    }

    public function unlockKey(): string
    {
        return 'exam_unlocked.' . $this->id . '.' . Str::lower($this->password);
    }
}
