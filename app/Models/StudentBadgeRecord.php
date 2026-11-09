<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ZEDVILLE — Engagement Badge
 * Model: StudentBadgeRecord
 *
 * INSTRUCTIONS FOR IT:
 * Place this file in: app/Models/StudentBadgeRecord.php
 */
class StudentBadgeRecord extends Model
{
    protected $table = 'student_badge_records';

    protected $fillable = [
        'student_id',
        'month',
        'year',
        'academic_year',
        'required_assigned',
        'required_completed',
        'optional_assigned',
        'optional_completed',
        'own_completed',
        'participation_points',
        'monthly_badge',
        'monthly_badge_points',
        'accumulated_points',
        'accumulated_badge',
        'is_overridden',
        'override_badge',
        'overridden_at',
    ];

    protected $casts = [
        'is_overridden'  => 'boolean',
        'overridden_at'  => 'datetime',
    ];

    // Badge point values
    public const BADGE_POINTS = [
        'PLATINUM' => 4,
        'GOLD'     => 3,
        'SILVER'   => 2,
        'BRONZE'   => 1,
        'NONE'     => 0,
    ];

    // Accumulated badge thresholds
    public const ACCUMULATED_THRESHOLDS = [
        'PLATINUM' => 32,
        'GOLD'     => 24,
        'SILVER'   => 16,
        'BRONZE'   => 8,
    ];

    // Badge display labels and colours (for frontend use)
    public const BADGE_META = [
        'PLATINUM' => ['label' => 'Platinum', 'emoji' => '🏅', 'color' => '#E8E8E8', 'text' => '#333333'],
        'GOLD'     => ['label' => 'Gold',     'emoji' => '🥇', 'color' => '#FFF8DC', 'text' => '#7A5C00'],
        'SILVER'   => ['label' => 'Silver',   'emoji' => '🥈', 'color' => '#F0F0F0', 'text' => '#555555'],
        'BRONZE'   => ['label' => 'Bronze',   'emoji' => '🥉', 'color' => '#FDF0E6', 'text' => '#7A3B00'],
        'NONE'     => ['label' => 'No Badge', 'emoji' => '—',  'color' => '#FFE8E8', 'text' => '#AA0000'],
    ];

    /**
     * The effective badge to display — uses override if set by admin.
     */
    public function getEffectiveMonthlyBadgeAttribute(): string
    {
        return $this->is_overridden && $this->override_badge
            ? $this->override_badge
            : $this->monthly_badge;
    }

    // TODO: confirm your users table relationship
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Scope: records for a specific academic year
     */
    public function scopeForYear($query, string $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    /**
     * Scope: records for a specific student
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope: records for a specific month/year
     */
    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
