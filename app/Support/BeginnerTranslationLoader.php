<?php

namespace App\Support;

use Illuminate\Translation\FileLoader;

/**
 * Daniel, 2026-10-22: every student language has a beginner version (A2/B1)
 * that the teacher turns on per class (classes.language_level).
 * lang/<code>_easy/ only holds the texts that are written more simply. With
 * beginner texts on, those are laid over lang/<code>/, so every other text
 * still comes from the standard version of the same language - and from
 * English after that, like any missing text.
 *
 * The locale itself stays the real language code ('es', not 'es_easy'), so
 * plural rules, dates and <html lang> work exactly as for standard classes.
 * SetStudentLocale switches this on per request.
 */
class BeginnerTranslationLoader extends FileLoader
{
    protected bool $beginnerTexts = false;

    /**
     * Returns true when the setting changed, so the translator can forget
     * texts it already loaded with the other setting.
     */
    public function useBeginnerTexts(bool $beginnerTexts): bool
    {
        $changed = $this->beginnerTexts !== $beginnerTexts;
        $this->beginnerTexts = $beginnerTexts;

        return $changed;
    }

    public function load($locale, $group, $namespace = null)
    {
        $lines = parent::load($locale, $group, $namespace);

        if ($this->beginnerTexts) {
            $lines = array_replace_recursive($lines, parent::load($locale . '_easy', $group, $namespace));
        }

        return $lines;
    }
}
