<?php

namespace TotvsRmSoap\Providers;

use Illuminate\Support\ServiceProvider;
use TotvsRmSoap\Config\ConnectionConfig;
use TotvsRmSoap\Connection\WebService;
use TotvsRmSoap\Services\ConsultaSQL;
use TotvsRmSoap\Services\DataServer;
use TotvsRmSoap\Services\FormulaVisual;
use TotvsRmSoap\Services\Process;
use TotvsRmSoap\Services\Report;
use TotvsRmSoap\TotvsRM;

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

        $this->app->singleton(ConnectionConfig::class, function () {
            return ConnectionConfig::fromArray(config('totvsrmsoap'));
        });

        $this->app->singleton(WebService::class, function ($app) {
            return new WebService($app->make(ConnectionConfig::class));
        });

        $this->app->singleton(DataServer::class, fn ($app) => new DataServer($app->make(WebService::class)));
        $this->app->singleton(ConsultaSQL::class, fn ($app) => new ConsultaSQL($app->make(WebService::class)));
        $this->app->singleton(Report::class, fn ($app) => new Report($app->make(WebService::class)));
        $this->app->singleton(Process::class, fn ($app) => new Process($app->make(WebService::class)));
        $this->app->singleton(FormulaVisual::class, fn ($app) => new FormulaVisual($app->make(WebService::class)));

        $this->app->singleton('totvs-rm', function ($app) {
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
