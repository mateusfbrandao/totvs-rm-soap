<?php

/**
 * Aliases de compatibilidade para o namespace curto TotvsRmSoap\ (v2.x / v3.0.0).
 * O namespace canônico é mateusfbi\TotvsRmSoap\.
 *
 * Classes Laravel (Provider/Facade) só são aliasadas quando Illuminate está disponível.
 */

$aliases = [
    'TotvsRmSoap\\Config\\ConnectionConfig' => \mateusfbi\TotvsRmSoap\Config\ConnectionConfig::class,
    'TotvsRmSoap\\Connection\\WebService' => \mateusfbi\TotvsRmSoap\Connection\WebService::class,
    'TotvsRmSoap\\DataTransferObjects\\ReadViewParams' => \mateusfbi\TotvsRmSoap\DataTransferObjects\ReadViewParams::class,
    'TotvsRmSoap\\DataTransferObjects\\Record' => \mateusfbi\TotvsRmSoap\DataTransferObjects\Record::class,
    'TotvsRmSoap\\Exceptions\\ConnectionException' => \mateusfbi\TotvsRmSoap\Exceptions\ConnectionException::class,
    'TotvsRmSoap\\Exceptions\\RecordNotFoundException' => \mateusfbi\TotvsRmSoap\Exceptions\RecordNotFoundException::class,
    'TotvsRmSoap\\Services\\ConsultaSQL' => \mateusfbi\TotvsRmSoap\Services\ConsultaSQL::class,
    'TotvsRmSoap\\Services\\DataServer' => \mateusfbi\TotvsRmSoap\Services\DataServer::class,
    'TotvsRmSoap\\Services\\FormulaVisual' => \mateusfbi\TotvsRmSoap\Services\FormulaVisual::class,
    'TotvsRmSoap\\Services\\Process' => \mateusfbi\TotvsRmSoap\Services\Process::class,
    'TotvsRmSoap\\Services\\Report' => \mateusfbi\TotvsRmSoap\Services\Report::class,
    'TotvsRmSoap\\TotvsRM' => \mateusfbi\TotvsRmSoap\TotvsRM::class,
    'TotvsRmSoap\\Utils\\Serialize' => \mateusfbi\TotvsRmSoap\Utils\Serialize::class,
];

foreach ($aliases as $alias => $concrete) {
    if (!class_exists($alias, false)) {
        class_alias($concrete, $alias);
    }
}

if (class_exists(\Illuminate\Support\ServiceProvider::class)) {
    if (!class_exists('TotvsRmSoap\\Providers\\TotvsRmSoapProvider', false)) {
        class_alias(
            \mateusfbi\TotvsRmSoap\Providers\TotvsRmSoapProvider::class,
            'TotvsRmSoap\\Providers\\TotvsRmSoapProvider'
        );
    }
}

if (class_exists(\Illuminate\Support\Facades\Facade::class)) {
    if (!class_exists('TotvsRmSoap\\Facades\\TotvsRM', false)) {
        class_alias(
            \mateusfbi\TotvsRmSoap\Facades\TotvsRM::class,
            'TotvsRmSoap\\Facades\\TotvsRM'
        );
    }
}
