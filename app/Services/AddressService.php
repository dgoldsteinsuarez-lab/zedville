<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Street;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * City Map, Phase 1 (2026-10-19). Assigns each student exactly one
 * street+number in their own Grade's simulated city - never a building
 * from the city-map hotspot list, since addresses are drawn from this
 * pool only, entirely separate from buildings.
 */
class AddressService
{
    public const DEFAULT_MAX_ADDRESSES = 200;

    /**
     * Idempotent: returns the student's existing address if they already
     * have one, otherwise claims the next free one in their Grade's pool
     * (generating the pool on first use). Returns null if the student has
     * no Class/Grade yet, or their Grade's pool is exhausted.
     */
    public static function assignForStudent(User $user): ?Address
    {
        $existing = Address::where('student_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        if (!$user->grade) {
            return null;
        }

        $class = SchoolClass::find($user->grade);
        if (!$class || !$class->grade_id) {
            return null;
        }

        return DB::transaction(function () use ($class, $user) {
            self::ensurePoolGenerated($class->grade_id);

            $address = Address::where('grade_id', $class->grade_id)
                ->whereNull('student_id')
                ->lockForUpdate()
                ->first();

            if (!$address) {
                return null;
            }

            $address->student_id = $user->id;
            $address->save();

            return $address;
        });
    }

    /**
     * Generates a Grade's full address pool the first time it's needed,
     * spread evenly across the 27 streets (round-robin), up to the
     * Grade's own max_addresses (default 200). No-op once any addresses
     * already exist for this Grade.
     */
    private static function ensurePoolGenerated(int $gradeId): void
    {
        if (Address::where('grade_id', $gradeId)->exists()) {
            return;
        }

        $grade = Grade::find($gradeId);
        $max = $grade->max_addresses ?? self::DEFAULT_MAX_ADDRESSES;

        $streetIds = Street::orderBy('sort_order')->pluck('id')->all();
        if (empty($streetIds)) {
            return;
        }

        $streetCount = count($streetIds);
        $houseNumberPerStreet = [];
        $rows = [];

        for ($i = 0; $i < $max; $i++) {
            $streetId = $streetIds[$i % $streetCount];
            $houseNumberPerStreet[$streetId] = ($houseNumberPerStreet[$streetId] ?? 0) + 1;

            $rows[] = [
                'grade_id' => $gradeId,
                'street_id' => $streetId,
                'house_number' => $houseNumberPerStreet[$streetId],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Address::insert($rows);
    }
}
