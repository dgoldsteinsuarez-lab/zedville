<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sid',
        'name',
        'nickname',
        'must_change_password',
        'email',
        'password',
        'citizenId',
        'avatar',
        'age',
        'grade',
        'address',
        'mascot',
        'role',
        'loginTime',
        'password_changed_at',
        'autopilot_enabled',
        'anonymized_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'autopilot_enabled' => 'boolean',
        'anonymized_at' => 'datetime',
    ];

    /**
     * Classes this user tutors. Many-to-many — a Tutor may be assigned to more
     * than one class (school-configurable).
     */
    public function tutoredClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_tutor', 'user_id', 'class_id');
    }

    /**
     * Ids of the students this admin-tier user may look at / act on:
     * Super Admin = all, School Admin = own school, Tutor = own classes.
     * Returns null for "no restriction" (Super Admin).
     */
    public function manageableStudentIds()
    {
        if ($this->isSuperAdmin()) {
            return null;
        }
        $students = User::where('role', 4);
        if ($this->isSchoolAdmin()) {
            return $students->where('sid', $this->sid)->pluck('id');
        }
        return $students->where('sid', $this->sid)
            ->whereIn('grade', $this->tutoredClasses()->pluck('classes.id'))
            ->pluck('id');
    }

    public function canManageStudent($studentId): bool
    {
        $ids = $this->manageableStudentIds();
        return $ids === null || $ids->contains((int) $studentId);
    }

    public function avatar()
    {
    return $this->belongsTo(\App\Models\Avatar::class, 'avatar'); // avatar is the foreign key in users table
    }
    /** The student's Class (legacy column name `grade` — it has always pointed at a Class, not a Grade). */
    public function gradeRelation()
    {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'grade', 'id');
    }

    public function mascotRelation()
    {
        return $this->belongsTo(\App\Models\Mascot::class, 'mascot', 'id');
    }

    public function avatarRelation()
    {
        return $this->belongsTo(\App\Models\Avatar::class, 'avatar', 'id');
    }
    public function schoolRelation()
    {
        return $this->belongsTo(\App\Models\SchoolDomain::class, 'sid', 'id');
    }

    // ==========================================
    // AUTHORITATIVE ROLE CONSTANTS
    // ==========================================
    const ROLE_SUPER_ADMIN  = 1;
    const ROLE_SCHOOL_ADMIN = 2;
    const ROLE_TUTOR        = 3;
    const ROLE_STUDENT      = 4;

    // ==========================================
    // ROLE HELPER METHODS
    // ==========================================

    /**
     * Check if user is Super Admin (Platform-wide)
     */
    public function isSuperAdmin(): bool
    {
        return in_array($this->role, [1, '1', 0, '0']);
    }

    /**
     * Check if user is School Admin (Scoped to their school)
     */
    public function isSchoolAdmin(): bool
    {
        return in_array($this->role, [2, '2']);
    }

    /**
     * Check if user is any Admin (Super Admin or School Admin)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [0, 1, 2, '0', '1', '2', 'admin']);
    }

    /**
     * Check if user is Tutor
     */
    public function isTutor(): bool
    {
        return in_array($this->role, [3, '3', 'tutor']);
    }

    /**
     * Check if user is Staff (Admin or Tutor)
     */
    public function isAdminOrTutor(): bool
    {
        return in_array($this->role, [0, 1, 2, 3, '0', '1', '2', '3', 'admin', 'tutor']);
    }

    /**
     * Check if user is Student
     */
    public function isStudent(): bool
    {
        return in_array($this->role, [4, '4', 'student']);
    }

    /**
     * Get readable role name
     */
    public function getRoleNameAttribute(): string
    {
        return match ((int) $this->role) {
            1, 0    => 'Super Admin',
            2       => 'School Admin',
            3       => 'Tutor',
            4       => 'Student',
            default => 'Unknown',
        };
    }

    /**
     * Pseudonym Accessor (Uses existing citizenId without extra DB fields)
     * NOT CURRENTLY USED ANYWHERE (confirmed 2026-09-30). The 'nickname' column
     * and User::$nickname is the adopted student-facing identity going forward —
     * use that instead of this accessor in any new code.
     */
    public function getPseudonymAttribute(): string
    {
        if ($this->isStudent()) {
            return $this->citizenId ?? ('Citizen #' . $this->id);
        }
        return $this->name;
    }
}

