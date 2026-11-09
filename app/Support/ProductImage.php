<?php

namespace App\Support;

/**
 * The picture shown for a shop or supermarket product. No product has an
 * uploaded image yet, and the shop pages showed a broken picture for every
 * item (Daniel's screenshot, 2026-10-25) - so a product without one gets a
 * small icon for its category instead.
 */
class ProductImage
{
    private const ICONS = [
        'Clothing' => '👕', 'Luxury' => '👔', 'Accessories' => '👜', 'Sports' => '⚽',
        'Music Instruments' => '🎸', 'Electronics' => '💻', 'Stationery' => '✏️',
        'Personal Care & Household' => '🧴', 'Home & Comfort' => '🛋️',
    ];

    /** $folder: 'products' (the mall shops) or 'supermarket'. */
    public static function url(object $product, string $folder = 'products'): string
    {
        if (!empty($product->image)) {
            return url("public/uploads/{$folder}/" . $product->image);
        }

        $icon = self::ICONS[$product->category ?? ''] ?? '🛒';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#E9F6F1"/>'
            . '<text x="32" y="43" font-size="30" text-anchor="middle">' . $icon . '</text></svg>';

        return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
    }
}
