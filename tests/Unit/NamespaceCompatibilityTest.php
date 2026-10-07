<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use mateusfbi\TotvsRmSoap\Config\ConnectionConfig;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use PHPUnit\Framework\TestCase;

class NamespaceCompatibilityTest extends TestCase
{
    public function testShortNamespaceAliasesAreInterchangeable(): void
    {
        $this->assertTrue(class_exists(\TotvsRmSoap\Config\ConnectionConfig::class));
        $this->assertTrue(class_exists(\TotvsRmSoap\Connection\WebService::class));
        $this->assertTrue(class_exists(\TotvsRmSoap\Services\DataServer::class));

        $canonical = new ConnectionConfig(url: 'http://a', user: 'u', pass: 'p');
        $this->assertInstanceOf(\TotvsRmSoap\Config\ConnectionConfig::class, $canonical);

        $viaAlias = new \TotvsRmSoap\Config\ConnectionConfig(url: 'http://b', user: 'u', pass: 'p');
        $this->assertInstanceOf(ConnectionConfig::class, $viaAlias);

        $webService = new \TotvsRmSoap\Connection\WebService($viaAlias);
        $this->assertInstanceOf(WebService::class, $webService);
        $this->assertInstanceOf(\TotvsRmSoap\Connection\WebService::class, $webService);
    }
}
