<?php

namespace TotvsRmSoap\Providers;

use Illuminate\Support\ServiceProvider;
use TotvsRmSoap\Connection\WebService as SoapConnection;
use TotvsRmSoap\Services\ConsultaSQL;
use TotvsRmSoap\Services\DataServer;
use TotvsRmSoap\Services\FormulaVisual;
use TotvsRmSoap\Services\Process;
use TotvsRmSoap\Services\Report;
use TotvsRmSoap\WebService;

class TotvsRmSoapProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/totvsrmsoap.php' => config_path('totvsrmsoap.php')
        ],'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/totvsrmsoap.php', 'totvsrmsoap'
        );

        // Registra cada serviço individualmente (mantendo a compatibilidade)
        $this->app->singleton(SoapConnection::class, fn() => new SoapConnection());
        $this->app->singleton(DataServer::class, fn($app) => new DataServer($app->make(SoapConnection::class)));
        $this->app->singleton(ConsultaSQL::class, fn($app) => new ConsultaSQL($app->make(SoapConnection::class)));
        $this->app->singleton(Report::class, fn($app) => new Report($app->make(SoapConnection::class)));
        $this->app->singleton(Process::class, fn($app) => new Process($app->make(SoapConnection::class)));
        $this->app->singleton(FormulaVisual::class, fn($app) => new FormulaVisual($app->make(SoapConnection::class)));

        // Registra a classe principal que a Facade irá usar
        $this->app->singleton('totvs-rm', function ($app) {
            return new WebService(
                $app->make(DataServer::class),
                $app->make(ConsultaSQL::class),
                $app->make(Report::class),
                $app->make(Process::class),
                $app->make(FormulaVisual::class)
            );
        });
    }
}