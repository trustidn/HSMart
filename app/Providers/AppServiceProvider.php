<?php

namespace App\Providers;

use App\Domains\Accounting\Listeners\RecordPurchaseJournal;
use App\Domains\Accounting\Listeners\RecordSaleJournal;
use App\Domains\Platform\Models\PlatformSetting;
use App\Domains\POS\Events\SaleCompleted;
use App\Domains\POS\Listeners\DeductSaleStock;
use App\Domains\Purchasing\Events\PurchaseCompleted;
use App\Domains\Purchasing\Listeners\AddPurchaseStock;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        View::composer(['welcome', 'layouts.auth.simple'], function ($view): void {
            $view->with('platformSetting', PlatformSetting::current());
        });
        Event::listen(SaleCompleted::class, DeductSaleStock::class);
        Event::listen(SaleCompleted::class, RecordSaleJournal::class);
        Event::listen(PurchaseCompleted::class, AddPurchaseStock::class);
        Event::listen(PurchaseCompleted::class, RecordPurchaseJournal::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
