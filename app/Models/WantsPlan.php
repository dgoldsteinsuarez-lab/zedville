<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One of the plans a student can choose for the month (App\Services\
 * WantsPlanService, Daniel 2026-10-31): cost per time, times_per_month 1, 2
 * (every other Saturday) or 4 (every Saturday); name and description in
 * English, with their translations.
 */
class WantsPlan extends Model
{
    // kind: low (Low Spending 4), high (High Spending 4) or mid (the Intermediate's, about 15%) - App\Services\WantsPlanService
    protected $fillable = ['kind', 'name', 'description', 'translations', 'cost', 'times_per_month', 'image', 'is_active', 'sort_order'];

    protected $casts = ['translations' => 'array', 'cost' => 'float', 'times_per_month' => 'integer', 'is_active' => 'boolean'];

    /** The name or description in the student's language (a hybrid class gets both). */
    public function text(string $field): string
    {
        $english = (string) $this->$field;
        $translated = $this->translations[app()->getLocale()][$field] ?? null;
        if (!$translated || app()->getLocale() === 'en') {
            return $english;
        }

        return \App\Support\Hybrid::on() ? \App\Support\Hybrid::mix($translated, $english) : $translated;
    }

    /** A full month of it (for sorting the plans into price levels). */
    public function monthlyCost(): float
    {
        return round($this->cost * max(1, $this->times_per_month), 2);
    }

    public function imageUrl(): string
    {
        return $this->image ? asset($this->image) : asset('asset/front/images/wants-plans/subscriptions.svg');
    }

    /** "Monthly plan: Popcorn Standing Order" in the bank, in the reader's language. */
    public static function translatedName(string $english): string
    {
        $plan = self::where('name', $english)->first();

        return $plan ? $plan->text('name') : $english;
    }
}
