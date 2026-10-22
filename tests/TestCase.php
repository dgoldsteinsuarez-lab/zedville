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

    /**
     * Points public_path() at a fresh temporary folder for this test, so a
     * test that uploads files doesn't leave them in the real public/uploads
     * and doesn't depend on that folder's permissions. Deleted afterwards.
     */
    protected function usePublicPathInTemp(): string
    {
        // On Windows a fake upload stays open until the test process ends,
        // so its folder can't always be removed in teardown - clear out
        // earlier runs' leftovers here instead.
        foreach (glob(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zedville-test-public-*', GLOB_ONLYDIR) ?: [] as $old) {
            \Illuminate\Support\Facades\File::deleteDirectory($old);
        }

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zedville-test-public-' . uniqid();
        mkdir($dir, 0777, true);

        $this->app->instance('path.public', $dir);
        $this->beforeApplicationDestroyed(fn () => \Illuminate\Support\Facades\File::deleteDirectory($dir));

        return $dir;
    }
}
