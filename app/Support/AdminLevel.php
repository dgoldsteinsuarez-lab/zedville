<?php

namespace App\Support;

use App\Models\GradeLevel;
use Illuminate\Support\Collection;

/**
 * The level an admin is working on (Daniel, 2026-11-02: "hay que dividir ya las
 * actividades que son para cada nivel rookie, intermedio, etc. tendrás que
 * reorganizar el Admin" - he chose a menu by level). The menu has one group per
 * level (Junior, Rookie, Intermediate, Advance, Advance Economist); its links
 * carry ?level=<id>, which is remembered. Only the activity screens use it
 * (the `admin.level` middleware): there the catalogs show that level's
 * activities and the class pickers that level's classes. Elsewhere (Users,
 * Students...) nothing is filtered.
 */
class AdminLevel
{
    public const SESSION = 'admin_level';

    private static ?int $current = null;

    /** The levels, in their order. */
    public static function all(): Collection
    {
        return GradeLevel::orderBy('sort_order')->get();
    }

    /** Set for this request by the middleware. */
    public static function use(?int $levelId): void
    {
        self::$current = $levelId ?: null;
    }

    public static function id(): ?int
    {
        return self::$current;
    }

    public static function current(): ?GradeLevel
    {
        return self::$current ? GradeLevel::find(self::$current) : null;
    }

    /** An activity's levels (ids) as a form sends them: none checked means every level. */
    public static function fromRequest($levels): ?array
    {
        $ids = collect((array) $levels)->map(fn ($id) => (int) $id)->filter()
            ->intersect(GradeLevel::pluck('id'))->unique()->sort()->values()->all();

        return $ids ?: null;
    }

    /** Whether an activity with these levels is for the level (null levels: every level). */
    public static function covers(?array $levels, ?int $levelId = null): bool
    {
        $levelId ??= self::$current;

        return !$levelId || !$levels || in_array($levelId, array_map('intval', $levels), true);
    }

    /** A catalog query limited to the current level's activities (and those for every level). */
    public static function scope($query, string $column = 'levels')
    {
        return $query->when(self::$current, fn ($q, $id) => $q->where(fn ($q) => $q->whereNull($column)->orWhereJsonContains($column, $id)));
    }

    /** "Rookie, Intermediate" - or "All levels". */
    public static function names(?array $levels): string
    {
        return $levels
            ? self::all()->whereIn('id', array_map('intval', $levels))->pluck('name')->implode(', ')
            : 'All levels';
    }
}
