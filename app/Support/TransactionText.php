<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * A bank transaction's description in the student's language. The ledger
 * keeps the English text the code writes ("Auto Transfer to Emergency Fund",
 * "Monthly Salary Credit - October 2026", "Purchase: Milk (Order #4)") -
 * the bank looks some of them up by that exact text - so they're translated
 * only when shown, with the texts in lang/<locale>/transactions.php
 * (Daniel, 2026-10-25: "todo tiene que estar en sus respectivos idiomas").
 * A description it doesn't recognise is shown as it is.
 */
class TransactionText
{
    public static function forStudent(?string $description): ?string
    {
        $text = trim((string) $description);
        // a purchase has a clear name in English too
        if ($text !== '' && ($order = self::order($text)) !== null) {
            return $order;
        }
        // and a fine names the activity as the student knows it (the line keeps its calendar title)
        if (str_starts_with($text, \App\Services\ActivityFineService::DESCRIPTION)) {
            return __('transactions.fine', ['activity' => CalendarTitle::forStudent(substr($text, strlen(\App\Services\ActivityFineService::DESCRIPTION)))]);
        }
        if (str_starts_with($text, 'Reversal - ' . \App\Services\ActivityFineService::DESCRIPTION)) {
            return __('transactions.reversal', ['original' => self::forStudent(substr($text, strlen('Reversal - ')))]);
        }
        if ($text === '' || app()->getLocale() === 'en') {
            return $description;
        }

        $lines = __('transactions.lines');
        if (is_array($lines) && isset($lines[$text])) {
            return $lines[$text];
        }

        $patterns = [
            '/^Monthly Salary Credit - (?<month>[A-Za-z]+ \d{4})$/' => fn ($m) => __('transactions.salary', ['month' => self::month($m['month'], 'F Y', __('transactions.month_year'))]),
            '/^Autopilot - (?<month>[A-Za-z]+)$/' => fn ($m) => __('transactions.autopilot', ['month' => self::month($m['month'], 'F', 'MMMM')]),
            '/^Grocery – (?<diet>.+)$/u' => fn ($m) => __('transactions.grocery', ['diet' => __('transactions.diets')[strtolower($m['diet'])] ?? $m['diet']]),
            '/^Transfer to (?<name>.+) \((?<account>[^()]+)\)$/' => fn ($m) => __('transactions.transfer_to', $m),
            '/^Received from (?<name>.+) \((?<account>[^()]+)\)$/' => fn ($m) => __('transactions.received_from', $m),
            '/^Bill payment to (?<name>.+) \((?<account>[^()]+)\)$/' => fn ($m) => __('transactions.bill_payment', $m),
            // A bill paid by auto-debit (AutoDebitController): "Transfer to Utility - Water 4455".
            '/^Transfer to (?<name>.+) (?<account>\S+)$/' => fn ($m) => __('transactions.bill_debit', ['name' => $lines[$m['name']] ?? $m['name'], 'account' => $m['account']]),
            '/^Donation to (?<name>.+)$/' => fn ($m) => __('transactions.donation', ['name' => ct('activities', $m['name'])]),
            '/^Purchase: (?<item>.+) \(Order #(?<order>\d+)\)$/' => fn ($m) => __('transactions.purchase', ['item' => ct('products', $m['item']), 'order' => $m['order']]),
            '/^(?<place>.+) \(Order #(?<order>\d+)\)$/' => fn ($m) => __('transactions.order', ['place' => $lines[$m['place']] ?? $m['place'], 'order' => $m['order']]),
            '/^Entertainment: (?<items>.+?)(?<more> & more)?$/' => fn ($m) => __('transactions.entertainment', [
                'items' => implode(', ', array_map(fn ($item) => ct('products', trim($item)), explode(', ', $m['items']))),
            ]) . (!empty($m['more']) ? ' ' . __('transactions.and_more') : ''),
            '/^Monthly plan: (?<name>.+)$/' => fn ($m) => __('transactions.monthly_plan', ['name' => \App\Models\WantsPlan::translatedName($m['name'])]),
            // the Money Factory (CurrencyExchangeService::lineText): "Currency exchange: 1,234.56 USD"
            '/^Currency exchange: (?<amount>[\d,]+(?:\.\d+)?) (?<code>[A-Z]{3})$/' => fn ($m) => __('transactions.currency_exchange', [
                'amount' => Money::number((float) str_replace(',', '', $m['amount']), str_contains($m['amount'], '.') ? 2 : 0) . "\u{00A0}" . $m['code'],
            ]),
            '/^Reversal - (?<original>.+)$/' => fn ($m) => __('transactions.reversal', ['original' => self::forStudent($m['original'])]),
        ];

        foreach ($patterns as $pattern => $translate) {
            if (preg_match($pattern, $text, $m)) {
                return $translate(array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY));
            }
        }

        return $description;
    }

    /**
     * An order's one line (App\Support\OrderLine): "Compra en el supermercado",
     * "Compra: fiesta de cumpleaños" - generic and clear, without the order
     * number (Daniel, 2026-11-02: "un nombre genérico y claro ... por ejemplo,
     * Compra Supermercado").
     */
    private static function order(string $text): ?string
    {
        if (preg_match('/^Spending Activity: (?<activity>.+) \(Order #\d+\)$/', $text, $m)) {
            $names = __('transactions.activity_purchases');

            return __('transactions.order_activity', ['activity' => is_array($names) && isset($names[$m['activity']]) ? $names[$m['activity']] : ct('activities', $m['activity'])]);
        }
        if (preg_match('/^(?<place>Supermarket|City Mall|Spending Activity) \(Order #\d+\)$/', $text, $m)) {
            return __('transactions.order_places.' . strtolower(str_replace(' ', '_', $m['place'])));
        }

        return null;
    }

    /**
     * "October 2026" written the local way ("octubre de 2026" ...). The "!" starts
     * from the 1st: without it the parse took today's day, so on the 31st
     * "September" became October (and February March on the 29th-31st).
     */
    private static function month(string $english, string $format, string $iso): string
    {
        try {
            return Carbon::createFromFormat('!' . $format, $english)->locale(app()->getLocale())->isoFormat($iso);
        } catch (\Throwable $e) {
            return $english;
        }
    }
}
