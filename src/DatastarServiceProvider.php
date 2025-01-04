<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Putyourlightson\Datastar\Http\Controllers\DatastarController;
use Putyourlightson\Datastar\Services\SseService;
use Putyourlightson\Datastar\Variables\DatastarVariable;

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
        $this->registerRoutes();
        $this->registerVariables();
        $this->registerDirectives();
    }

    protected function registerRoutes(): void
    {
        Route::match(
            ['get', 'post', 'put', 'patch', 'delete'],
            '/datastar-controller',
            [DatastarController::class, 'index'],
        );
    }

    protected function registerVariables(): void
    {
        View::composer('*', function(\Illuminate\View\View $view) {
            $view->with('datastar', new DatastarVariable(app(SseService::class)));
        });
    }

    protected function registerDirectives(): void
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

    protected function getDirective(string $expression): string
    {
        return "<?php app(\Putyourlightson\Datastar\Services\SseService::class)->$expression ?>";
    }
}
