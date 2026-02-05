<?php

return [
    'url' => env('TOTVSRM_WSURL'),
    'user' => env('TOTVSRM_USER'),
    'pass' => env('TOTVSRM_PASS'),
    // Mapeamento de URLs por empresa (coligada).
    // Recomenda-se configurar diretamente como array.
    // Ex.: 'companies' => [ '01' => 'http://rm-empresa01:8051', '02' => 'http://rm-empresa02:8051' ]
    // Caso prefira usar .env, ainda é suportado via TOTVSRM_COMPANIES ("01|http://host1;02|http://host2")
    'companies' => [
        // '01' => 'http://rm-empresa01:8051',
        // '02' => 'http://rm-empresa02:8051',
    ],
    'connection_timeout' => env('TOTVSRM_CONNECTION_TIMEOUT', 1800), // 30 minutos padrão
];