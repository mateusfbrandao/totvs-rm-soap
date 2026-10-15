<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Providers\TotvsRmSoapProvider;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\TotvsRM;
use PHPUnit\Framework\TestCase;

class ProviderTest extends TestCase
{
    private function app(): Container
    {
        $app = new Container;
        Container::setInstance($app);
        $app->instance('config', new Repository([
            'totvsrmsoap' => [
                'url' => 'http://localhost:8051',
                'user' => 'u',
                'pass' => 'p',
            ],
        ]));

        (new TotvsRmSoapProvider($app))->register();

        return $app;
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);

        parent::tearDown();
    }

    public function test_services_are_transient(): void
    {
        $app = $this->app();

        $this->assertNotSame($app->make(DataServer::class), $app->make(DataServer::class));
        $this->assertNotSame($app->make('totvs.data_server'), $app->make('totvs.data_server'));
    }

    public function test_config_and_web_service_are_shared(): void
    {
        $app = $this->app();

        $this->assertSame($app->make(Config::class), $app->make(Config::class));
        $this->assertSame($app->make(WebService::class), $app->make(WebService::class));
    }

    public function test_totvs_rm_aggregate_resolves(): void
    {
        $app = $this->app();

        $rm = $app->make('totvs-rm');

        $this->assertInstanceOf(TotvsRM::class, $rm);
        $this->assertInstanceOf(DataServer::class, $rm->dataServer());
    }
}
