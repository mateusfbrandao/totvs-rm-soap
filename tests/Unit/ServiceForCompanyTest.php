<?php

namespace TotvsRmSoap\Tests\Unit;

use TotvsRmSoap\Connection\WebService;
use TotvsRmSoap\Services\ConsultaSQL;
use TotvsRmSoap\Services\DataServer;
use TotvsRmSoap\Services\FormulaVisual;
use TotvsRmSoap\Services\Process;
use TotvsRmSoap\Services\Report;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SoapClient;

class ServiceForCompanyTest extends TestCase
{
    #[DataProvider('servicesProvider')]
    public function testForCompanyReusesSameWebServiceInstance(string $serviceClass, string $endpoint): void
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
        $returned = $service->forCompany('01');

        $this->assertSame($service, $returned);
        $this->assertSame([
            [$endpoint, null],
            [$endpoint, '01'],
        ], $calls);
    }

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
