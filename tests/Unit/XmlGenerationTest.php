<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\Services\Report;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class XmlGenerationTest extends TestCase
{
    private function dataServer(): DataServer
    {
        $config = new Config(url: 'http://localhost:8051', user: 'u', pass: 'p');

        return new DataServer(new WebService($config));
    }

    private function report(): Report
    {
        $config = new Config(url: 'http://localhost:8051', user: 'u', pass: 'p');

        return new Report(new WebService($config));
    }

    private static function reportXml(Report $report, string $property): string
    {
        $ref = new ReflectionProperty(Report::class, $property);

        return $ref->getValue($report);
    }

    public function test_set_xml_from_array_with_repeatable_nodes(): void
    {
        $ds = $this->dataServer();
        $ds->setXMLFromArray([
            'root' => 'R',
            'TABELA' => [
                'ITEM' => [
                    ['COD' => '1', 'NOME' => 'A'],
                    ['COD' => '2', 'NOME' => 'B'],
                ],
            ],
        ]);

        $xml = $ds->getXML();

        $this->assertSame(2, substr_count($xml, '<ITEM>'));
        $this->assertStringContainsString('<COD>1</COD>', $xml);
        $this->assertStringContainsString('<NOME>B</NOME>', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_set_xml_from_array_with_single_node(): void
    {
        $ds = $this->dataServer();
        $ds->setXMLFromArray([
            'root' => 'R',
            'TABELA' => ['CAB' => ['COD' => '9']],
        ]);

        $this->assertStringContainsString('<CAB>', $ds->getXML());
        $this->assertStringContainsString('<COD>9</COD>', $ds->getXML());
    }

    public function test_report_set_filtro_builds_bands_with_cdata(): void
    {
        $rp = $this->report();
        $rp->setFiltro([
            ['bandname' => 'RptReport', 'mainfilter' => true, 'filtersbytable' => []],
            [
                'bandname' => 'Sub&Detalhe',
                'mainfilter' => false,
                'filtersbytable' => [
                    ['filter' => "PFUNC.CODSITUACAO = 'D'", 'name' => '', 'tablename' => 'PFUNC'],
                ],
            ],
        ]);

        $xml = self::reportXml($rp, 'filtro');

        $this->assertStringContainsString('<BandName>RptReport</BandName>', $xml);
        $this->assertStringContainsString('<BandName>Sub&amp;Detalhe</BandName>', $xml);
        $this->assertStringContainsString('<MainFilter>true</MainFilter>', $xml);
        $this->assertStringContainsString('<MainFilter>false</MainFilter>', $xml);
        $this->assertStringContainsString('<![CDATA[PFUNC.CODSITUACAO = \'D\']]>', $xml);
        $this->assertStringContainsString('<TableName>PFUNC</TableName>', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_report_set_filtro_empty_is_valid_xml(): void
    {
        $rp = $this->report();
        $rp->setFiltro([]);

        $xml = self::reportXml($rp, 'filtro');

        $this->assertStringContainsString('ArrayOfRptFilterReportPar', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_report_set_parametros_per_type(): void
    {
        $cases = [
            'String' => 'd3p1:string',
            'Int16' => 'd3p1:int',
            'Int32' => 'd3p1:int',
            'DateTime' => 'd3p1:dateTime',
        ];

        foreach ($cases as $type => $itype) {
            $rp = $this->report();
            $rp->setParametros([[
                'Description' => 'D',
                'ParamName' => 'P',
                'Type' => $type,
                'Value' => $type === 'DateTime' ? '2026-05-31T00:00:00' : '1',
            ]]);

            $xml = self::reportXml($rp, 'parametros');

            $this->assertStringContainsString($itype, $xml, "Type {$type}");
            // Nota: os namespaces d3p2/d3p3 replicam a serialização .NET do RM
            // e não são URIs válidas — o RM aceita, mas o libxml avisa.
            $prev = libxml_use_internal_errors(true);
            try {
                $this->assertNotFalse(simplexml_load_string($xml));
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($prev);
            }
        }
    }
}
