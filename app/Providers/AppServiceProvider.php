<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

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
        // Medida de seguridad y rendimiento para Eloquent (Solo alerta en entornos locales o de desarrollo)
        // - Evita "N+1 query problems" (Consultas innecesarias a la BD)
        // - Evita que se ignoren propiedades en asignación masiva preventivamente
        // - Evita acceder a propiedades del modelo que no han sido cargadas o no existen
        Model::shouldBeStrict(! app()->isProduction());
    }
}
