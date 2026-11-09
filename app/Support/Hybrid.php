<?php

namespace App\Support;

/**
 * Hybrid classes (Daniel, 2026-10-29: "for students that speak little
 * Spanish, French or German ... complicated explanation in English, some
 * double, the language to learn bigger, and some in Spanish, like
 * supermarket products"). A class's language level "hybrid" mixes the class
 * language (the language to learn) with English, text by text:
 *
 *   - words and names (up to 2 words: products, buttons, short labels) in the
 *     language to learn only;
 *   - short texts (up to 14 words, one sentence) in both: the language to
 *     learn, and English smaller next to it;
 *   - longer explanations in English.
 *
 * A text shown in both is marked while the page is built and turned into
 * HTML at the end (App\Http\Middleware\RenderHybridText), which knows
 * whether the text sits in the page, in an attribute or in a script.
 */
class Hybrid
{
    private const OPEN = '[[zvhy:';
    private const SEP = '::zven::';
    private const CLOSE = ':zvhy]]';
    private const PATTERN = '/\[\[zvhy:(.*?)::zven::(.*?):zvhy\]\]/is';

    /** Texts that are formats or codes, not words. */
    private const SKIP = ['transactions.month_year'];

    private static bool $enabled = false;

    public static function enable(bool $enabled = true): void
    {
        self::$enabled = $enabled;
    }

    /** On for this request, and the language to learn isn't English. */
    public static function on(): bool
    {
        return self::$enabled && app()->getLocale() !== 'en';
    }

    public static function skips(string $key): bool
    {
        return in_array($key, self::SKIP, true) || str_contains($key, 'format');
    }

    /** Runs $callback with mixing off (e.g. to get one language only). */
    public static function without(callable $callback)
    {
        $was = self::$enabled;
        self::$enabled = false;
        try {
            return $callback();
        } finally {
            self::$enabled = $was;
        }
    }

    /** One text: the language to learn, English, or both - see the class comment. */
    public static function mix(string $target, string $english): string
    {
        $target = self::side($target, 'target');
        $english = self::side($english, 'english');

        if (trim($english) === '' || trim($target) === trim($english)) {
            return $target;
        }

        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($english)));
        $words = $plain === '' ? 0 : count(explode(' ', $plain));
        $sentences = preg_match_all('/[.!?](\s|$)/u', $plain);

        if ($words <= 2) {
            return $target;
        }
        if ($words <= 14 && $sentences <= 1) {
            return self::OPEN . $target . self::SEP . $english . self::CLOSE;
        }

        return $english;
    }

    /** A translation that may be a list of texts: each text mixed with its English. */
    public static function mixAny($target, $english)
    {
        if (is_string($target) && is_string($english)) {
            return self::mix($target, $english);
        }
        if (is_array($target) && is_array($english)) {
            foreach ($target as $key => $value) {
                if (array_key_exists($key, $english)) {
                    $target[$key] = self::mixAny($value, $english[$key]);
                }
            }
        }

        return $target;
    }

    /** A text with marks as one language only ('target' or 'english'). */
    public static function side(string $text, string $side): string
    {
        while (stripos($text, self::OPEN) !== false) {
            $next = preg_replace_callback(self::PATTERN, fn ($m) => $side === 'target' ? $m[1] : $m[2], $text);
            if ($next === null || $next === $text) {
                break;
            }
            $text = $next;
        }

        return $text;
    }

    public static function has(string $content): bool
    {
        return stripos($content, self::OPEN) !== false;
    }

    /** "Target (English)" - for attributes, scripts, JSON and plain text. */
    public static function plain(string $content): string
    {
        return preg_replace_callback(self::PATTERN, fn ($m) => $m[1] . ' (' . strip_tags($m[2]) . ')', $content) ?? $content;
    }

    /**
     * A page: a text in the page becomes the language to learn with English
     * smaller after it; inside a tag (an attribute), a script, a style, a
     * title, a textarea or a select's option it becomes "Target (English)".
     */
    public static function html(string $html): string
    {
        [$starts, $maxEnds] = self::plainZones($html);
        if ($starts === null) {
            return self::plain($html);
        }

        $out = preg_replace_callback(self::PATTERN, function ($m) use ($starts, $maxEnds) {
            if (self::inZone($m[0][1], $starts, $maxEnds)) {
                return $m[1][0] . ' (' . strip_tags($m[2][0]) . ')';
            }

            return '<span class="zv-hy"><span class="zv-hy-t">' . $m[1][0] . '</span> <span class="zv-hy-e" lang="en">' . $m[2][0] . '</span></span>';
        }, $html, -1, $count, PREG_OFFSET_CAPTURE);

        if ($out === null) {
            return self::plain($html);
        }

        // English in orange (Daniel, 2026-10-31: "inglés debería tener un color más
        // distintivo ... en ese fondo oscuro, creo que pegaría un naranja fuerte"):
        // a strong orange on a dark background (#FF922B, 7:1 on navy), a burnt
        // orange on a light one (#C2410C, 5:1 on white) - the same hue, readable on
        // both - and the text's own colour where no orange reads (a green button).
        $style = '<style id="zv-hybrid">.zv-hy-e{font-size:.8em;font-style:italic;font-weight:600;margin-left:.3em;color:#C2410C}'
            . '.zv-hy-e.is-on-dark{color:#FF922B}.zv-hy-e.is-on-colour{color:inherit;font-weight:400;opacity:.85}</style>'
            . '<script id="zv-hybrid-colour">' . self::COLOUR_SCRIPT . '</script>';

        return stripos($out, '</head>') !== false ? preg_replace('~</head>~i', $style . '</head>', $out, 1) : $out;
    }

    /**
     * Picks each English part's orange from what's behind it: the first
     * background up the page (a colour, or a gradient's first colour) that
     * isn't see-through. Burnt orange if it reads there (4.5:1), else the
     * strong orange, else the text's colour.
     */
    private const COLOUR_SCRIPT = <<<'JS'
(function () {
    function rgb(s) { var m = s && s.match(/rgba?\(([^)]+)\)/); if (!m) return null; var p = m[1].split(',').map(parseFloat); return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 }; }
    function lum(c) { function f(v) { v /= 255; return v <= .03928 ? v / 12.92 : Math.pow((v + .055) / 1.055, 2.4); } return .2126 * f(c.r) + .7152 * f(c.g) + .0722 * f(c.b); }
    function behind(el) {
        for (; el && el.nodeType === 1; el = el.parentElement) {
            var cs = getComputedStyle(el), c = rgb(cs.backgroundColor);
            if (c && c.a >= .5) return c;
            var g = cs.backgroundImage && cs.backgroundImage.indexOf('gradient') > -1 ? rgb(cs.backgroundImage) : null;
            if (g && g.a >= .5) return g;
        }
        return { r: 255, g: 255, b: 255, a: 1 };
    }
    function ratio(a, b) { return (Math.max(a, b) + .05) / (Math.min(a, b) + .05); }
    var BURNT = lum({ r: 194, g: 65, b: 12 }), STRONG = lum({ r: 255, g: 146, b: 43 });
    function paint() {
        document.querySelectorAll('.zv-hy-e:not([data-zv-bg])').forEach(function (e) {
            var l = lum(behind(e));
            e.setAttribute('data-zv-bg', '');
            if (ratio(BURNT, l) >= 4.5) return;
            e.classList.add(ratio(STRONG, l) >= 4.5 ? 'is-on-dark' : 'is-on-colour');
        });
    }
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', paint) : paint();
    window.addEventListener('load', paint);
})();
JS;

    /**
     * Where HTML can't go: every tag (quoted attribute values respected) and
     * the content of script, style, textarea, title and option. Sorted by
     * start, with the furthest end so far, for a quick lookup.
     */
    private static function plainZones(string $html): array
    {
        $zones = [];
        $raw = preg_match_all('~<(script|style|textarea|title|option)\b[^>]*>.*?</\1\s*>~is', $html, $rawMatches, PREG_OFFSET_CAPTURE);
        $tags = preg_match_all('~<[a-zA-Z!/][^>"\']*(?:(?:"[^"]*"|\'[^\']*\')[^>"\']*)*>~s', $html, $tagMatches, PREG_OFFSET_CAPTURE);
        if ($raw === false || $tags === false) {
            return [null, null];
        }
        foreach ([$rawMatches[0], $tagMatches[0]] as $matches) {
            foreach ($matches as [$text, $start]) {
                $zones[] = [$start, $start + strlen($text)];
            }
        }
        usort($zones, fn ($a, $b) => $a[0] <=> $b[0]);

        $starts = [];
        $maxEnds = [];
        $furthest = -1;
        foreach ($zones as [$start, $end]) {
            $furthest = max($furthest, $end);
            $starts[] = $start;
            $maxEnds[] = $furthest;
        }

        return [$starts, $maxEnds];
    }

    private static function inZone(int $offset, array $starts, array $maxEnds): bool
    {
        // the last zone starting at or before $offset
        $low = 0;
        $high = count($starts) - 1;
        $found = -1;
        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);
            if ($starts[$mid] <= $offset) {
                $found = $mid;
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return $found >= 0 && $maxEnds[$found] > $offset;
    }
}
