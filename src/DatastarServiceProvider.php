<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Putyourlightson\Datastar\Globals\DatastarGlobal;
use Putyourlightson\Datastar\Http\Controllers\DatastarController;
use Putyourlightson\Datastar\Services\SseService;

class DatastarServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(): void
    {
        $this->app->singleton(SseService::class, function() {
            return new SseService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::match(
            ['get', 'post', 'put', 'patch', 'delete'],
            '/datastar-controller',
            [DatastarController::class, 'index'],
        );

        View::composer('*', function(\Illuminate\View\View $view) {
            $view->with('datastar', new DatastarGlobal(app(SseService::class)));
        });

        Blade::directive('mergefragments', function(string $expression) {
            return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->setSseInProcess('mergeFragments', $expression); ob_start(); ?>";
        });

        Blade::directive('endmergefragments', function() {
            return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->mergeFragments(ob_get_clean()); ?>";
        });

        Blade::directive('removefragments', function(string $expression) {
            return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->removeFragments($expression); ?>";
        });

        Blade::directive('mergesignals', function(string $expression) {
            return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->mergeSignals($expression); ?>";
        });

        Blade::directive('removesignals', function(string $expression) {
            return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->removeSignals($expression); ?>";
        });

        Blade::directive('executescript', function(string $expression) {
            return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->setSseInProcess('executeScript', $expression); ob_start(); ?>";
        });

        Blade::directive('endexecutescript', function() {
            return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->executeScript(ob_get_clean()); ?>";
        });
    }
}
