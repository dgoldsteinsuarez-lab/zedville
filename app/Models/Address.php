<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One street+number in a Grade's own simulated city (Address Scope:
 * per-Grade, not per-school - see bugs.md, 2026-10-19). A student only
 * ever gets one of these in the Residential Quarter; the "never live in
 * the Bank" rule is automatic since addresses are never drawn from the
 * building-hotspot list at all.
 */
class Address extends Model
{
    protected $fillable = ['grade_id', 'street_id', 'house_number', 'student_id'];

    public function street()
    {
        return $this->belongsTo(Street::class);
    }

    /** e.g. "1 Adam Smith Avenue, Zedville" - as written on the bank account. */
    public function label(): string
    {
        return $this->house_number . ' ' . $this->street->name . ', Zedville';
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
