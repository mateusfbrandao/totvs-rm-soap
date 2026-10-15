<?php

namespace mateusfbi\TotvsRmSoap\Providers;

use Illuminate\Support\ServiceProvider;
use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\Services\FormulaVisual;
use mateusfbi\TotvsRmSoap\Services\Process;
use mateusfbi\TotvsRmSoap\Services\Report;
use mateusfbi\TotvsRmSoap\TotvsRM;

class TotvsRmSoapProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/totvsrmsoap.php' => config_path('totvsrmsoap.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/totvsrmsoap.php',
            'totvsrmsoap'
        );

        $this->app->singleton(Config::class, function () {
            return Config::fromArray(config('totvsrmsoap'));
        });

        $this->app->singleton(WebService::class, function ($app) {
            return new WebService($app->make(Config::class));
        });

        // Serviços são stateful (setDataServer/setFiltro/...): transient para
        // evitar vazamento de estado entre usos (Octane/filas).
        $this->app->bind(DataServer::class, fn ($app) => new DataServer($app->make(WebService::class)));
        $this->app->bind(ConsultaSQL::class, fn ($app) => new ConsultaSQL($app->make(WebService::class)));
        $this->app->bind(Report::class, fn ($app) => new Report($app->make(WebService::class)));
        $this->app->bind(Process::class, fn ($app) => new Process($app->make(WebService::class)));
        $this->app->bind(FormulaVisual::class, fn ($app) => new FormulaVisual($app->make(WebService::class)));

        $this->app->bind('totvs-rm', function ($app) {
            return new TotvsRM(
                $app->make(DataServer::class),
                $app->make(ConsultaSQL::class),
                $app->make(Report::class),
                $app->make(Process::class),
                $app->make(FormulaVisual::class)
            );
        });

        // Aliases usados via app('totvs.*')
        $this->app->alias(DataServer::class, 'totvs.data_server');
        $this->app->alias(ConsultaSQL::class, 'totvs.consulta_sql');
        $this->app->alias(Report::class, 'totvs.report');
        $this->app->alias(Process::class, 'totvs.process');
        $this->app->alias(FormulaVisual::class, 'totvs.formula_visual');
    }
}
