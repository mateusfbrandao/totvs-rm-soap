<?php

/**
 * Exemplo de uso standalone (PHP puro), sem Laravel.
 *
 * Uso:
 *   TOTVSRM_WSURL=http://localhost:8051 TOTVSRM_USER=u TOTVSRM_PASS=p php examples/php-puro.php
 */

require_once __DIR__.'/../vendor/autoload.php';

use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\DataTransferObjects\ReadViewParams;
use mateusfbi\TotvsRmSoap\DataTransferObjects\Record;
use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;
use mateusfbi\TotvsRmSoap\Services\DataServer;

$config = new Config(
    url: getenv('TOTVSRM_WSURL') ?: 'http://localhost:8051',
    user: getenv('TOTVSRM_USER') ?: 'usuario',
    pass: getenv('TOTVSRM_PASS') ?: 'senha',
    companies: Config::normalizeCompanies(getenv('TOTVSRM_COMPANIES') ?: null),
    connectionTimeout: (int) (getenv('TOTVSRM_CONNECTION_TIMEOUT') ?: 60),
);

$connection = new WebService($config);

try {
    // API fluente + DTO
    $ds = new DataServer($connection);
    $result = $ds->readViewWith(new ReadViewParams(
        dataServerName: 'GlbColigadaDataBR',
        filter: '1=1',
        context: 'CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre',
    ));
    var_dump($result);

    // Mapeando linhas para Records (a chave das linhas varia por DataServer)
    $rows = $result['GlbColigada'] ?? $result;
    $records = array_map(fn (array $row) => new Record($row), is_array($rows) && array_is_list($rows) ? $rows : [$rows]);
    foreach ($records as $record) {
        echo $record->get('CODCOLIGADA'), PHP_EOL;
    }

    $sql = (new ConsultaSQL($connection))->forCompany('01');
    $sql->setSentenca('SENTENCA_EXEMPLO')->setColigada(1)->setSistema('G')->setParametros(['P1' => 'VALOR']);
    var_dump($sql->RealizarConsultaSQL());
} catch (Throwable $e) {
    fwrite(STDERR, 'Erro: '.$e->getMessage().PHP_EOL);
    exit(1);
}
