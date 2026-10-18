<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // EnsureCitizenActivation (global 'web' middleware) redirects any
        // student (role 4) to the onboarding wizard until they have a bank
        // account, salary, auto-debit and consumer survey on file. Most
        // feature tests create a bare student via User::factory() and don't
        // care about onboarding state at all - disabled by default so
        // existing/unrelated tests don't all need that fixture. Tests that
        // actually exercise the gate (CitizenActivationGateTest) turn it
        // back on explicitly.
        $this->withoutMiddleware(\App\Http\Middleware\EnsureCitizenActivation::class);
    }
}
