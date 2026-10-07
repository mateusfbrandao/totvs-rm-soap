<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use mateusfbi\TotvsRmSoap\Config\ConnectionConfig;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use PHPUnit\Framework\TestCase;

class WebServiceTest extends TestCase
{
    public function testStoresAndExposesConnectionConfig(): void
    {
        $config = new ConnectionConfig(
            url: 'http://rm.local:8051',
            user: 'mestre',
            pass: 'secret',
            companies: ['01' => 'http://empresa01:8051'],
        );

        $webService = new WebService($config);

        $this->assertSame($config, $webService->getConfig());
        $this->assertSame('http://empresa01:8051', $webService->getConfig()->resolveBaseUrl('01'));
    }

    public function testCanBeConstructedWithoutLaravelHelpers(): void
    {
        $webService = new WebService(new ConnectionConfig(
            url: 'http://localhost',
            user: 'u',
            pass: 'p',
        ));

        $this->assertInstanceOf(WebService::class, $webService);
        $this->assertSame('http://localhost', $webService->getConfig()->url);
    }
}
