<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\Services\Report;
use mateusfbi\TotvsRmSoap\Utils\Serialize;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class RobustnessTest extends TestCase
{
    private function service(string $class): object
    {
        $config = new Config(url: 'http://localhost:8051', user: 'u', pass: 'p');

        return new $class(new WebService($config));
    }

    public function testSerializeEmptyReturnsEmptyArray(): void
    {
        $this->assertSame([], Serialize::result(null));
        $this->assertSame([], Serialize::result(''));
        $this->assertSame([], Serialize::result([]));
    }

    public function testSerializeValidXml(): void
    {
        $result = Serialize::result('<root><Resultado><item>1</item></Resultado></root>');

        $this->assertSame('1', $result['Resultado']['item']);
    }

    public function testSerializeInvalidXmlThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);

        Serialize::result('<root><unclosed>');
    }

    public function testSerializeNonStringThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);

        Serialize::result(123);
    }

    public function testDataServerXmlEscapesSpecialChars(): void
    {
        /** @var DataServer $ds */
        $ds = $this->service(DataServer::class);
        $ds->setXMLFromArray([
            'root' => 'R',
            'TABELA' => ['TAB' => ['NOME' => 'A&B<C>D"E\'F']],
        ]);

        $xml = $ds->getXML();

        $this->assertStringContainsString('A&amp;B', $xml);
        $this->assertStringContainsString('&lt;C&gt;', $xml);
        // Round-trip: o XML gerado é válido
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function testDataServerXmlRejectsInvalidTag(): void
    {
        /** @var DataServer $ds */
        $ds = $this->service(DataServer::class);

        $this->expectException(InvalidArgumentException::class);
        $ds->setXMLFromArray(['root' => 'R', 'TABELA' => ['1NV' => ['A' => 'b']]]);
    }

    public function testConsultaSQLRejectsSemicolonInValue(): void
    {
        /** @var ConsultaSQL $sql */
        $sql = $this->service(ConsultaSQL::class);

        $this->expectException(InvalidArgumentException::class);
        $sql->setParametros(['P1' => 'A;B']);
    }

    public function testConsultaSQLRejectsInvalidKey(): void
    {
        /** @var ConsultaSQL $sql */
        $sql = $this->service(ConsultaSQL::class);

        $this->expectException(InvalidArgumentException::class);
        $sql->setParametros(['A=B' => 'x']);
    }

    public function testReportRejectsUnknownType(): void
    {
        /** @var Report $rp */
        $rp = $this->service(Report::class);

        $this->expectException(InvalidArgumentException::class);
        $rp->setParametros([['Description' => 'D', 'ParamName' => 'P', 'Type' => 'Float', 'Value' => '1']]);
    }

    public function testReportRejectsInvalidDateTime(): void
    {
        /** @var Report $rp */
        $rp = $this->service(Report::class);

        $this->expectException(InvalidArgumentException::class);
        $rp->setParametros([['Description' => 'D', 'ParamName' => 'P', 'Type' => 'DateTime', 'Value' => 'not-a-date']]);
    }

    public function testReportParametrosEscapesSpecialChars(): void
    {
        /** @var Report $rp */
        $rp = $this->service(Report::class);
        $rp->setParametros([['Description' => 'A&B', 'ParamName' => 'P', 'Type' => 'String', 'Value' => 'x<y']]);

        $ref = new \ReflectionProperty(Report::class, 'parametros');
        $xml = $ref->getValue($rp);

        $this->assertStringContainsString('A&amp;B', $xml);
        $this->assertStringContainsString('x&lt;y', $xml);
    }

    public function testParseReportListHandlesSerializedWrapperAndCommaInName(): void
    {
        $raw = 's:99:"0,G,4587,COD,Relat, com, virgula,2026-01-01,uuid-1;,1,P,2,X,Y,2026-01-02,uuid-2";';

        $parsed = Report::parseReportList($raw);

        $this->assertCount(2, $parsed);
        $this->assertSame('Relat, com, virgula', $parsed[0]['nome']);
        $this->assertSame('uuid-1', $parsed[0]['uuid']);
        $this->assertSame('Y', $parsed[1]['nome']);
    }

    public function testParseReportListSkipsMalformedWithoutSideEffects(): void
    {
        $parsed = Report::parseReportList('incompleto,sem,campos');

        $this->assertSame([], $parsed);
    }

    public function testConfigAssertValidFailsFast(): void
    {
        $config = new Config(url: '', user: 'u', pass: 'p');

        $this->expectException(InvalidArgumentException::class);
        $config->assertValid();
    }

    public function testConfigDebugInfoHidesPassword(): void
    {
        $config = new Config(url: 'http://x', user: 'u', pass: 'secret');
        $debug = $config->__debugInfo();

        $this->assertSame('***', $debug['pass']);
        $this->assertStringNotContainsString('secret', var_export($debug, true));
    }

    public function testSettersAreFluent(): void
    {
        /** @var DataServer $ds */
        $ds = $this->service(DataServer::class);
        $this->assertSame($ds, $ds->setDataServer('X'));
        $this->assertSame($ds, $ds->setContexto('C'));
        $this->assertSame($ds, $ds->setFiltro('1=1'));
        $this->assertSame($ds, $ds->setXML('<a/>'));

        /** @var ConsultaSQL $sql */
        $sql = $this->service(ConsultaSQL::class);
        $this->assertSame($sql, $sql->setSentenca('S')->setColigada(1)->setSistema('G'));

        /** @var Report $rp */
        $rp = $this->service(Report::class);
        $this->assertSame($rp, $rp->setColigada(0)->setId(1)->setContexto('C'));
    }
}
