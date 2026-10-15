# Referência dos serviços

`DataServer` e `ConsultaSQL` estão no [README](../README.md). Abaixo, os demais.

Todos os `set*()` são fluentes (`: static`) e o `SoapClient` é criado sob
demanda — `new` e `forCompany()` não fazem rede.

## Report — geração e download de relatórios

```php
use mateusfbi\TotvsRmSoap\Services\Report;

$rp = (new Report($connection))
    ->setColigada(0)
    ->setId(4587)
    ->setContexto('CODSISTEMA=P;CODCOLIGADA=1;CODUSUARIO=mateus.brandao')
    ->setNomeArquivo('Teste.pdf')
    ->setFiltro($filtros)    // array de bandas (ver abaixo)
    ->setParametros($params); // array de parâmetros (ver abaixo)

$guid = $rp->generateReport(); // retorna GUID ou mensagem de erro do RM

// Download em chunks
$rp->setGuid($guid);
$size = $rp->getGeneratedReportSize();
$rp->setOffset(0)->setLength($size);
$pdf = base64_decode($rp->getFileChunk());
```

Filtros (bandas):

```php
$filtros = [
    [
        'bandname' => 'SubDetalhe1',
        'mainfilter' => false,
        'filtersbytable' => [
            ['filter' => "PFUNC.CODSITUACAO = 'D'", 'name' => '', 'tablename' => 'PFUNC'],
        ],
    ],
];
```

Parâmetros (`Type`: `String`, `Int16`, `Int32` ou `DateTime` ISO 8601):

```php
$params = [
    ['Description' => 'CODCOLIGADA', 'ParamName' => 'CODCOLIGADA', 'Type' => 'Int16', 'Value' => '1'],
    ['Description' => 'DATA', 'ParamName' => 'DATA', 'Type' => 'DateTime', 'Value' => '2026-05-31T00:00:00'],
];
```

Outros métodos: `getReportList()`, `getReportMetaData()`, `getReportInfo()`,
`generateReportAsynchronous()` + `getGeneratedReportStatus()`, `getFileHash()`.

## Process — execução de processos

```php
use mateusfbi\TotvsRmSoap\Services\Process;

$jobId = (new Process($connection))
    ->setProcess('NomeDoProcesso')
    ->setXML($xmlParams)
    ->executeWithXmlParams();
```

Status: `setJobId($job)->setExecId($exec)->getProcessStatus()`.

## FormulaVisual — execução de fórmulas visuais

```php
use mateusfbi\TotvsRmSoap\Services\FormulaVisual;

$resultado = (new FormulaVisual($connection))
    ->setColigada(1)
    ->setFormula('ID_FORMULA')
    ->setContexto('CODSISTEMA=G;CODCOLIGADA=1;CODUSUARIO=mestre')
    ->setParametersXML($xml)
    ->execute();
```

`setParametersXML()` é opcional (omitido vira `null`).
