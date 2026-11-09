<?php

namespace App\Support;

/**
 * A FinHero Library module's icon as HTML: its emoji, or - for the names
 * below - a drawn icon where no emoji shows the thing. Introduction to
 * Budget is a piggy bank (Daniel, 2026-10-29: "change the icon of a head of
 * the pig to a piggy bank instead"); Unicode has a pig's face, no piggy
 * bank. Drawn icons are 1.2em square - an emoji draws a little larger than
 * its font size - so they match the emoji beside them.
 */
class ModuleIcon
{
    private const DRAWN = [
        'piggy-bank' => '<svg class="zv-drawn-icon" viewBox="4 1 57 57" width="1.2em" height="1.2em" aria-hidden="true" style="display:inline-block;vertical-align:-.2em">'
            . '<rect x="17" y="43" width="8" height="13" rx="3" fill="#E0758F"/>'
            . '<rect x="38" y="43" width="8" height="13" rx="3" fill="#E0758F"/>'
            . '<path d="M10.5 33c-4-.5-5.5-4-3.5-6.5 1.6-2 4.2-.8 3.8 1.4" fill="none" stroke="#E0758F" stroke-width="2.6" stroke-linecap="round"/>'
            . '<path d="M39 22.5 43 11.5l7.5 10.5z" fill="#E0758F"/>'
            . '<ellipse cx="31" cy="35" rx="21.5" ry="16.5" fill="#F7A3BA" stroke="#D45F80" stroke-width="1.6"/>'
            . '<ellipse cx="25" cy="29" rx="9" ry="5" fill="#FBC6D4" opacity=".7"/>'
            . '<rect x="47.5" y="29" width="10.5" height="11.5" rx="4.5" fill="#E0758F" stroke="#C24E70" stroke-width="1.4"/>'
            . '<circle cx="51.3" cy="33.4" r="1.25" fill="#9C3A57"/>'
            . '<circle cx="51.3" cy="36.6" r="1.25" fill="#9C3A57"/>'
            . '<circle cx="43" cy="29.5" r="2.1" fill="#3B1F2B"/>'
            . '<rect x="24.5" y="20.2" width="12" height="3" rx="1.5" fill="#9C3A57"/>'
            . '<circle cx="30.5" cy="9.5" r="7.2" fill="#F6BE3B" stroke="#C98A0B" stroke-width="2"/>'
            . '<path d="M30.5 6v7" stroke="#C98A0B" stroke-width="2" stroke-linecap="round"/>'
            . '</svg>',
    ];

    public static function html(?string $icon): string
    {
        return self::DRAWN[$icon] ?? e($icon ?? '');
    }
}
