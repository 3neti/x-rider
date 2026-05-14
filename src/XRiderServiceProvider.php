<?php

namespace LBHurtado\XRider;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LBHurtado\XRider\Contracts\RiderAnalyticsRecorderContract;
use LBHurtado\XRider\Contracts\RiderCampaignResolverContract;
use LBHurtado\XRider\Contracts\RiderExperienceResolverContract;
use LBHurtado\XRider\Contracts\RiderRendererContract;
use LBHurtado\XRider\Contracts\SuccessRedirectResolverContract;
use LBHurtado\XRider\Services\DefaultRiderCampaignResolver;
use LBHurtado\XRider\Services\DefaultRiderExperienceResolver;
use LBHurtado\XRider\Services\DefaultSuccessRedirectResolver;
use LBHurtado\XRider\Services\LogRiderAnalyticsRecorder;
use LBHurtado\XRider\Services\RiderRenderer;

class XRiderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/x-rider.php', 'x-rider');

        $this->app->singleton(RiderCampaignResolverContract::class, DefaultRiderCampaignResolver::class);
        $this->app->singleton(RiderExperienceResolverContract::class, DefaultRiderExperienceResolver::class);
        $this->app->singleton(SuccessRedirectResolverContract::class, DefaultSuccessRedirectResolver::class);
        $this->app->singleton(RiderAnalyticsRecorderContract::class, LogRiderAnalyticsRecorder::class);
        $this->app->singleton(RiderRendererContract::class, RiderRenderer::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/x-rider.php' => config_path('x-rider.php'),
        ], 'x-rider-config');

        $this->publishes([
            __DIR__.'/../resources/js/pages/x-rider' => resource_path('js/pages/x-rider'),
            __DIR__.'/../resources/js/components/x-rider' => resource_path('js/components/x-rider'),
            __DIR__.'/../resources/js/composables' => resource_path('js/composables'),
        ], 'x-rider-ui');

        if ((bool) config('x-rider.routes.enabled', true)) {
            $this->loadRoutes();
        }
    }

    protected function loadRoutes(): void
    {
        Route::prefix(config('x-rider.routes.prefix', 'x-rider'))
            ->middleware(config('x-rider.routes.middleware', ['web']))
            ->group(__DIR__.'/../routes/x-rider.php');
    }
}
