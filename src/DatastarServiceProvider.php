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
use Putyourlightson\Datastar\Services\SseService;

class DatastarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/datastar.php', 'datastar');

        $this->app->singleton(SseService::class, function() {
            return new SseService();
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
            Route::get('/datastar-controller', [DatastarController::class, 'view']);
            Route::post('/datastar-controller', [DatastarController::class, 'action']);
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
     * @uses SseService::patchElements()
     * @uses SseService::removeElements()
     * @uses SseService::patchSignals()
     * @uses SseService::executeScript()
     * @uses SseService::location()
     * @uses SseService::setSseInProcess
     */
    private function registerDirectives(): void
    {
        Blade::directive('patchelements', function(string $expression) {
            return $this->getDirective("setSseInProcess('patchElements', $expression); ob_start()");
        });

        Blade::directive('endpatchelements', function() {
            return $this->getDirective("patchElements(ob_get_clean())");
        });

        Blade::directive('removeelements', function(string $expression) {
            return $this->getDirective("removeElements($expression)");
        });

        Blade::directive('patchsignals', function(string $expression) {
            return $this->getDirective("patchSignals($expression)");
        });

        Blade::directive('executescript', function(string $expression) {
            return $this->getDirective("setSseInProcess('executeScript', $expression); ob_start()");
        });

        Blade::directive('endexecutescript', function() {
            return $this->getDirective("executeScript(ob_get_clean())");
        });

        Blade::directive('location', function(string $expression) {
            return $this->getDirective("location($expression)");
        });
    }

    private function getDirective(string $expression): string
    {
        return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->$expression ?>";
    }
}
