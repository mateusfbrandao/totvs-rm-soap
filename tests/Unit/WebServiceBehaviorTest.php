<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use InvalidArgumentException;
use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Exceptions\ConnectionException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SoapClient;

class WebServiceBehaviorTest extends TestCase
{
    public function test_get_client_fails_fast_on_empty_url(): void
    {
        $ws = new WebService(new Config(url: '', user: 'u', pass: 'p'));

        $this->expectException(InvalidArgumentException::class);
        $ws->getClient('/wsDataServer/MEX?wsdl');
    }

    public function test_get_client_fails_fast_on_empty_user(): void
    {
        $ws = new WebService(new Config(url: 'http://localhost:8051', user: '', pass: 'p'));

        $this->expectException(InvalidArgumentException::class);
        $ws->getClient('/wsDataServer/MEX?wsdl');
    }

    public function test_get_client_fails_fast_on_invalid_timeout(): void
    {
        $ws = new WebService(new Config(url: 'http://localhost:8051', user: 'u', pass: 'p', connectionTimeout: 0));

        $this->expectException(InvalidArgumentException::class);
        $ws->getClient('/wsDataServer/MEX?wsdl');
    }

    public function test_get_client_wraps_unreachable_host(): void
    {
        $ws = new WebService(new Config(url: 'http://127.0.0.1:9', user: 'u', pass: 'p', connectionTimeout: 1));

        // SoapClient emite warning de I/O além da exceção; suprime no teste.
        set_error_handler(static function (): bool {
            return true;
        });
        try {
            $ws->getClient('/wsDataServer/MEX?wsdl');
            $this->fail('Deveria lançar ConnectionException.');
        } catch (ConnectionException $e) {
            $this->assertStringContainsString('http://127.0.0.1:9/wsDataServer/MEX?wsdl', $e->getMessage());
        } finally {
            restore_error_handler();
        }
    }

    public function test_get_client_reuses_cached_instance(): void
    {
        $ws = new WebService(new Config(url: 'http://localhost:8051', user: 'u', pass: 'p'));
        $cached = $this->createMock(SoapClient::class);

        $ref = new ReflectionProperty(WebService::class, 'clients');
        $ref->setValue($ws, ['http://localhost:8051/wsDataServer/MEX?wsdl' => $cached]);

        $this->assertSame($cached, $ws->getClient('/wsDataServer/MEX?wsdl'));
    }
}
