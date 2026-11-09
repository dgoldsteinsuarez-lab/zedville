<?php

namespace Tests\Feature;

use App\Support\TransactionText;
use Tests\TestCase;

/**
 * The bank keeps its transaction descriptions in English (it looks some of
 * them up by that text); students read them in their language. Found by the
 * Spanish terminology review (2026-10-25): "Auto Transfer to Emergency Fund"
 * and "Monthly Salary Credit - October" showed in English on Spanish statements.
 */
class TransactionTextTest extends TestCase
{
    public function test_the_descriptions_the_bank_writes_are_shown_in_the_students_language(): void
    {
        app()->setLocale('es');
        $this->assertSame('Transferencia periódica al fondo de emergencia', TransactionText::forStudent('Auto Transfer to Emergency Fund'));
        $this->assertSame('Abono de nómina - octubre de 2026', TransactionText::forStudent('Monthly Salary Credit - October 2026'));
        $this->assertSame('Transferencia a Riley (2141322727)', TransactionText::forStudent('Transfer to Riley (2141322727)'));
        $this->assertSame('Domiciliación - Recibo de la luz (4455)', TransactionText::forStudent('Transfer to Utility - Electricity 4455'));
        $this->assertSame('Compra de alimentación (dieta vegana)', TransactionText::forStudent('Grocery – Vegan'));
        $this->assertSame('Anulación - Alquiler', TransactionText::forStudent('Reversal - Rent'));
        $this->assertStringStartsWith('Compra: ', TransactionText::forStudent('Purchase: Milk (Order #4)'));
        $this->assertStringEndsWith('(pedido n.º 4)', TransactionText::forStudent('Purchase: Milk (Order #4)'));

        app()->setLocale('fr');
        $this->assertSame("Virement permanent vers le livret d'épargne de précaution", TransactionText::forStudent('Auto Transfer to Emergency Fund'));

        app()->setLocale('de');
        $this->assertSame('Dauerauftrag auf das Notgroschen-Konto', TransactionText::forStudent('Auto Transfer to Emergency Fund'));
        $this->assertSame('Gehaltseingang - Oktober 2026', TransactionText::forStudent('Monthly Salary Credit - October 2026'));
    }

    public function test_english_and_unknown_descriptions_stay_as_they_are(): void
    {
        app()->setLocale('en');
        $this->assertSame('Auto Transfer to Emergency Fund', TransactionText::forStudent('Auto Transfer to Emergency Fund'));

        app()->setLocale('es');
        $this->assertSame('A note the admin typed', TransactionText::forStudent('A note the admin typed'));
        $this->assertNull(TransactionText::forStudent(null));
    }

    /** Every fixed description and pattern has a text in every language. */
    public function test_every_language_has_every_description(): void
    {
        $english = require lang_path('en/transactions.php');
        foreach (['es', 'fr', 'de'] as $locale) {
            $translated = require lang_path("$locale/transactions.php");
            $this->assertSame(array_keys($english['lines']), array_keys($translated['lines']), $locale);
            $this->assertSame(array_keys($english['diets']), array_keys($translated['diets']), $locale);
        }
    }
}
