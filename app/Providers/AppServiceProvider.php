<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\BankStatement;
use App\Models\BankAccount;
use App\Models\CalendarEvent;
use App\Models\StudentBadgeRecord;
use App\Models\FinheroBadgeRecord;
use App\Models\Mailbox;
use App\Models\Donation;
use App\Models\ReferendumVote;
use App\Models\PetitionSignature;
use App\Models\Petition;
use App\Models\MoodLog;

use App\Observers\TransactionNotificationObserver;
use App\Observers\TransferNotificationObserver;
use App\Observers\BankStatementNotificationObserver;
use App\Observers\BankAccountNotificationObserver;
use App\Observers\CalendarEventNotificationObserver;
use App\Observers\StudentBadgeNotificationObserver;
use App\Observers\FinheroBadgeNotificationObserver;
use App\Observers\MailboxNotificationObserver;
use App\Observers\DonationNotificationObserver;
use App\Observers\ReferendumVoteNotificationObserver;
use App\Observers\PetitionSignatureNotificationObserver;
use App\Observers\PetitionNotificationObserver;
use App\Observers\MoodLogNotificationObserver;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Beginner (A2/B1) versions of the student languages - see
        // BeginnerTranslationLoader and SetStudentLocale.
        $this->app->extend('translation.loader', function ($loader, $app) {
            return new \App\Support\BeginnerTranslationLoader($app['files'], $app['path.lang']);
        });

        // Hybrid classes: the language to learn mixed with English, text by
        // text - see App\Support\Hybrid (switched on by SetStudentLocale).
        $this->app->extend('translator', function ($translator, $app) {
            $hybrid = new \App\Support\HybridTranslator($translator->getLoader(), $translator->getLocale());
            $hybrid->setFallback($translator->getFallback());

            return $hybrid;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // ZEDVILLE — real notification system: each Observer turns an
        // existing model write into a notification row, without touching
        // the controller that performs the original action.
        Transaction1::observe(TransactionNotificationObserver::class);
        Transfer::observe(TransferNotificationObserver::class);
        BankStatement::observe(BankStatementNotificationObserver::class);
        BankAccount::observe(BankAccountNotificationObserver::class);
        CalendarEvent::observe(CalendarEventNotificationObserver::class);
        StudentBadgeRecord::observe(StudentBadgeNotificationObserver::class);
        FinheroBadgeRecord::observe(FinheroBadgeNotificationObserver::class);
        Mailbox::observe(MailboxNotificationObserver::class);
        Donation::observe(DonationNotificationObserver::class);
        ReferendumVote::observe(ReferendumVoteNotificationObserver::class);
        PetitionSignature::observe(PetitionSignatureNotificationObserver::class);
        Petition::observe(PetitionNotificationObserver::class);
        MoodLog::observe(MoodLogNotificationObserver::class);

        // Feed the bell icon on every page that extends layouts.profile,
        // without touching each controller individually.
        View::composer('layouts.profile', function ($view) {
            if (! Auth::check()) {
                return;
            }
            $notifications = app(NotificationService::class);
            $view->with('notificationFeed', $notifications->feed(Auth::id(), 20));
            $view->with('notificationUnreadCount', $notifications->unreadCount(Auth::id()));
        });
    }
}
