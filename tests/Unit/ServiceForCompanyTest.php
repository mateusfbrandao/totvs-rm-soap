<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\Services\FormulaVisual;
use mateusfbi\TotvsRmSoap\Services\Process;
use mateusfbi\TotvsRmSoap\Services\Report;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SoapClient;

class ServiceForCompanyTest extends TestCase
{
    #[DataProvider('servicesProvider')]
    public function test_construct_is_lazy_and_for_company_defers_client_creation(string $serviceClass, string $endpoint): void
    {
        $soapClient = $this->createMock(SoapClient::class);
        $calls = [];

        $webService = $this->createMock(WebService::class);
        $webService->expects($this->exactly(2))
            ->method('getClient')
            ->willReturnCallback(function (string $path, ?string $companyCode = null) use (&$calls, $soapClient) {
                $calls[] = [$path, $companyCode];

                return $soapClient;
            });

        /** @var DataServer|ConsultaSQL|Report|Process|FormulaVisual $service */
        $service = new $serviceClass($webService);

        // Lazy: nada criado no construct nem no forCompany
        $this->assertSame([], $calls);
        $this->assertNull($service->getCompanyCode());

        $returned = $service->forCompany('01');

        $this->assertSame($service, $returned);
        $this->assertSame('01', $service->getCompanyCode());
        $this->assertSame([], $calls);

        // Primeiro uso cria o client; segundo uso reutiliza o cache
        $this->invokeSoap($service);
        $this->invokeSoap($service);

        $this->assertSame([[$endpoint, '01']], $calls);

        // Trocar de empresa invalida o cache
        $service->forCompany('02');
        $this->invokeSoap($service);

        $this->assertSame([
            [$endpoint, '01'],
            [$endpoint, '02'],
        ], $calls);
    }

    #[DataProvider('servicesProvider')]
    public function test_set_soap_client_avoids_web_service_call(string $serviceClass): void
    {
        $webService = $this->createMock(WebService::class);
        $webService->expects($this->never())->method('getClient');

        $service = new $serviceClass($webService);
        $service->setSoapClient($this->createMock(SoapClient::class));

        $this->invokeSoap($service);
    }

    private function invokeSoap(object $service): void
    {
        $method = new ReflectionMethod($service, 'soap');
        $method->invoke($service);
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    public static function servicesProvider(): array
    {
        return [
            'DataServer' => [DataServer::class, '/wsDataServer/MEX?wsdl'],
            'ConsultaSQL' => [ConsultaSQL::class, '/wsConsultaSQL/MEX?wsdl'],
            'Report' => [Report::class, '/wsReport/MEX?wsdl'],
            'Process' => [Process::class, '/wsProcess/MEX?wsdl'],
            'FormulaVisual' => [FormulaVisual::class, '/wsFormulaVisual/MEX?wsdl'],
        ];
    }
}
