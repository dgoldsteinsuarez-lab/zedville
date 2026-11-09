<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * Daniel, 2026-10-22: French and German, plus a beginner (A2/B1) version of
 * every language. Keeps the language files in step with lang/en: a text
 * missing from lang/fr would show French students English in the middle of
 * a French page, and a typo in a beginner file would silently never be used.
 *
 * Every file in lang/en is checked except Laravel's own auth/pagination/
 * passwords/validation files, which exist in English only for now.
 */
class TranslationFilesTest extends TestCase
{
    private const ENGLISH_ONLY_FILES = ['auth', 'pagination', 'passwords', 'validation'];

    private function studentFiles(): array
    {
        return array_values(array_diff($this->filesIn('en'), self::ENGLISH_ONLY_FILES));
    }

    private function lines(string $locale, string $file): array
    {
        $path = lang_path("{$locale}/{$file}.php");

        return is_file($path) ? Arr::dot(require $path) : [];
    }

    private function filesIn(string $locale): array
    {
        return array_map(fn ($path) => basename($path, '.php'), glob(lang_path("{$locale}/*.php")));
    }

    private function placeholders(string $text): array
    {
        preg_match_all('/:([a-zA-Z_]+)/', $text, $matches);
        $names = array_values(array_unique($matches[1]));
        sort($names);

        return $names;
    }

    private function translations(): array
    {
        return array_values(array_diff(array_keys(config('zedville.languages')), ['en']));
    }

    public function test_every_language_has_a_standard_and_a_beginner_folder(): void
    {
        foreach (array_keys(config('zedville.languages')) as $code) {
            $this->assertDirectoryExists(lang_path($code));
            $this->assertDirectoryExists(lang_path("{$code}_easy"));
        }
    }

    public function test_every_language_has_every_english_text(): void
    {
        foreach ($this->translations() as $code) {
            foreach ($this->studentFiles() as $file) {
                $missing = array_diff(array_keys($this->lines('en', $file)), array_keys($this->lines($code, $file)));

                $this->assertSame([], array_values($missing), "lang/{$code}/{$file}.php is missing: " . implode(', ', $missing));
            }
        }
    }

    public function test_no_language_has_texts_that_english_does_not(): void
    {
        foreach ($this->translations() as $code) {
            foreach ($this->filesIn($code) as $file) {
                $extra = array_diff(array_keys($this->lines($code, $file)), array_keys($this->lines('en', $file)));

                $this->assertSame([], array_values($extra), "lang/{$code}/{$file}.php has texts lang/en doesn't: " . implode(', ', $extra));
            }
        }
    }

    public function test_beginner_files_only_simplify_texts_that_exist(): void
    {
        foreach (array_keys(config('zedville.languages')) as $code) {
            foreach ($this->filesIn("{$code}_easy") as $file) {
                $unknown = array_diff(array_keys($this->lines("{$code}_easy", $file)), array_keys($this->lines($code, $file)));

                $this->assertSame([], array_values($unknown), "lang/{$code}_easy/{$file}.php has texts lang/{$code} doesn't: " . implode(', ', $unknown));
            }
        }
    }

    public function test_translations_keep_the_same_placeholders_as_english(): void
    {
        foreach (array_keys(config('zedville.languages')) as $code) {
            foreach ([$code, "{$code}_easy"] as $locale) {
                foreach ($this->filesIn($locale) as $file) {
                    $english = $this->lines('en', $file);

                    foreach ($this->lines($locale, $file) as $key => $text) {
                        if (! is_string($text) || ! is_string($english[$key] ?? null)) {
                            continue;
                        }

                        $this->assertSame(
                            $this->placeholders($english[$key]),
                            $this->placeholders($text),
                            "lang/{$locale}/{$file}.php '{$key}' doesn't keep the :placeholders of the English text"
                        );
                    }
                }
            }
        }
    }
}
