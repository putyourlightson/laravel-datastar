<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Putyourlightson\Datastar\Http\Controllers\DatastarController;
use Putyourlightson\Datastar\Http\Middleware\RegisterScript;
use Putyourlightson\Datastar\Services\Sse;

class DatastarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/datastar.php', 'datastar');

        $this->app->singleton(Sse::class, function() {
            return new Sse();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/datastar.php' => config_path('datastar.php'),
        ], 'datastar-config');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor'),
        ], 'public');

        $this->registerRoutes();
        $this->registerScript();
        $this->registerDirectives();
    }

    private function registerRoutes(): void
    {
        Route::middleware(['web'])->group(function() {
            Route::any(
                '/datastar-controller',
                [DatastarController::class, 'index'],
            );
        });
    }

    private function registerScript(): void
    {
        if (config('datastar.registerScript', true) === false) {
            return;
        }

        $this->app['router']->pushMiddlewareToGroup('web', RegisterScript::class);
    }

    /**
     * @uses Sse::setSseInProcess
     */
    private function registerDirectives(): void
    {
        Blade::directive('mergefragments', function(string $expression) {
            return $this->getDirective("setSseInProcess('mergeFragments', $expression); ob_start()");
        });

        Blade::directive('endmergefragments', function() {
            return $this->getDirective("mergeFragments(ob_get_clean())");
        });

        Blade::directive('removefragments', function(string $expression) {
            return $this->getDirective("removeFragments($expression)");
        });

        Blade::directive('mergesignals', function(string $expression) {
            return $this->getDirective("mergeSignals($expression)");
        });

        Blade::directive('removesignals', function(string $expression) {
            return $this->getDirective("removeSignals($expression)");
        });

        Blade::directive('executescript', function(string $expression) {
            return $this->getDirective("setSseInProcess('executeScript', $expression); ob_start()");
        });

        Blade::directive('endexecutescript', function() {
            return $this->getDirective("executeScript(ob_get_clean())");
        });
    }

    private function getDirective(string $expression): string
    {
        return "<?php app(\Putyourlightson\Datastar\Services\Sse::class)->$expression ?>";
    }
}
