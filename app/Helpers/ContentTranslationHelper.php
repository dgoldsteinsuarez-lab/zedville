<?php

use App\Support\ContentTranslations;

if (!function_exists('ct')) {
    /**
     * Database content (stored in English) in the student's language - see
     * App\Support\ContentTranslations. Groups: products, daily_questions,
     * library, curriculum, activities. e.g. {{ ct('curriculum', $item->title) }}
     */
    function ct(string $group, ?string $text): ?string
    {
        return ContentTranslations::get($group, $text);
    }
}

if (!function_exists('txn_text')) {
    /** A bank transaction's description in the student's language - see App\Support\TransactionText. */
    function txn_text(?string $description): ?string
    {
        return \App\Support\TransactionText::forStudent($description);
    }
}

if (!function_exists('zeds')) {
    /** An amount of zeds the way the student's language writes money - see App\Support\Money. */
    function zeds($amount, bool $sign = false): string
    {
        return \App\Support\Money::format($amount, $sign);
    }
}

if (!function_exists('ct_image')) {
    /** The URL of an Academy picture, in the student's language when it has a translated copy. */
    function ct_image(string $file): string
    {
        return asset('uploads/curriculum/' . ContentTranslations::image($file));
    }
}
