<?php

namespace App\Support;

use Illuminate\Translation\Translator;

/**
 * The translator of a hybrid class (App\Support\Hybrid): each text in the
 * language to learn is laid next to its English. Only texts asked for in the
 * page's language are mixed - a text asked for in a given language (e.g.
 * trans('surveys.diets', [], 'en')) comes as it is.
 *
 * A text with a short version - the same key ending in "_hy" - is shown short
 * (Daniel, 2026-11-02: "en híbrido, haz todo más corto y sencillo"); short
 * enough, it comes in both languages instead of English only.
 */
class HybridTranslator extends Translator
{
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        if ($locale !== null || !Hybrid::on() || !is_string($key) || Hybrid::skips($key)) {
            return parent::get($key, $replace, $locale, $fallback);
        }

        $key = $this->shortKey($key);

        return Hybrid::mixAny(parent::get($key, $replace, $locale, $fallback), parent::get($key, $replace, 'en', $fallback));
    }

    public function choice($key, $number, array $replace = [], $locale = null)
    {
        if ($locale !== null || !Hybrid::on()) {
            return parent::choice($key, $number, $replace, $locale);
        }

        $key = $this->shortKey($key);
        // each language picks its own plural form first, then they are mixed
        $target = Hybrid::without(fn () => parent::choice($key, $number, $replace));
        $english = Hybrid::without(fn () => parent::choice($key, $number, $replace, 'en'));

        return Hybrid::mix($target, $english);
    }

    /** The text's short version ("<key>_hy") when it has one. */
    private function shortKey(string $key): string
    {
        $short = $key . '_hy';

        return parent::get($short, [], null, true) !== $short ? $short : $key;
    }
}
