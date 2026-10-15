<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\Services\FormulaVisual;
use mateusfbi\TotvsRmSoap\Services\Process;
use mateusfbi\TotvsRmSoap\Services\Report;
use PHPUnit\Framework\TestCase;
use SoapClient;

class SuccessPathsTest extends TestCase
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @param  array<string, mixed>  $results  Mapa método SOAP => valor de "<Método>Result"
     * @return T
     */
    private function serviceWithResults(string $class, array $results): object
    {
        $soap = $this->createMock(SoapClient::class);
        $soap->method('__call')->willReturnCallback(
            fn (string $method, array $args): object => (object) [$method.'Result' => $results[$method] ?? null]
        );

        $config = new Config(url: 'http://localhost:8051', user: 'u', pass: 'p');
        $service = new $class(new WebService($config));
        $service->setSoapClient($soap);

        return $service;
    }

    public function test_data_server_crud_success(): void
    {
        $xml = '<NewDataSet><T><A>1</A></T></NewDataSet>';

        /** @var DataServer $ds */
        $ds = $this->serviceWithResults(DataServer::class, [
            'SaveRecord' => '1',
            'ReadRecord' => $xml,
            'ReadView' => $xml,
            'DeleteRecord' => '1',
            'DeleteRecordByKey' => $xml,
        ]);
        $ds->setDataServer('DS')->setContexto('CTX')->setPrimaryKey('K')->setFiltro('1=1')->setXML('<r/>');

        $this->assertSame('1', $ds->saveRecord());
        $this->assertSame(['T' => ['A' => '1']], $ds->readRecord());
        $this->assertSame(['T' => ['A' => '1']], $ds->readView());
        $this->assertSame(1, $ds->deleteRecord());
        $this->assertSame(['T' => ['A' => '1']], $ds->deleteRecordByKey());
    }

    public function test_consulta_sql_success(): void
    {
        /** @var ConsultaSQL $sql */
        $sql = $this->serviceWithResults(ConsultaSQL::class, [
            'RealizarConsultaSQL' => '<root><Resultado><X>1</X></Resultado></root>',
        ]);
        $sql->setSentenca('S')->setColigada(1)->setSistema('G');

        $this->assertSame(['response' => ['X' => '1']], $sql->RealizarConsultaSQL());
    }

    public function test_report_generate_and_download_success(): void
    {
        /** @var Report $rp */
        $rp = $this->serviceWithResults(Report::class, [
            'GenerateReport' => 'guid-123',
            'GetGeneratedReportSize' => '10',
            'GetFileChunk' => base64_encode('0123456789'),
            'GetReportInfo' => null,
        ]);
        $rp->setColigada(0)->setId(1)->setNomeArquivo('T.pdf')->setContexto('CTX');

        $this->assertSame('guid-123', $rp->generateReport());

        $rp->setGuid('guid-123');
        $this->assertSame(10, $rp->getGeneratedReportSize());

        $rp->setOffset(0)->setLength(10);
        $this->assertSame('0123456789', base64_decode($rp->getFileChunk()));
    }

    public function test_process_success(): void
    {
        /** @var Process $p */
        $p = $this->serviceWithResults(Process::class, [
            'ExecuteWithXmlParams' => '7',
            'getProcessStatus' => '2',
        ]);
        $p->setProcess('P')->setXML('<x/>')->setJobId('J')->setExecId('E');

        $this->assertSame(7, $p->executeWithXmlParams());
        $this->assertSame(2, $p->getProcessStatus());
    }

    public function test_formula_visual_success(): void
    {
        /** @var FormulaVisual $f */
        $f = $this->serviceWithResults(FormulaVisual::class, ['Execute' => '3']);
        $f->setColigada(1)->setFormula('F')->setContexto('CTX');

        $this->assertSame(3, $f->execute());
    }
}
