<?php

/**
 * Exemplo de uso no Laravel (via Provider/Facade ou injeção de dependência).
 *
 * ```php
 * use mateusfbi\TotvsRmSoap\Facades\TotvsRM;
 * use mateusfbi\TotvsRmSoap\Services\DataServer;
 *
 * // Via Facade (instância transient — sem vazamento de estado)
 * $result = TotvsRM::dataServer()->forCompany('01')
 *     ->setDataServer('GlbColigadaDataBR')
 *     ->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre')
 *     ->setFiltro('1=1')
 *     ->readView();
 *
 * // Via injeção de dependência
 * class ColigadaController
 * {
 *     public function __construct(private DataServer $dataServer) {}
 *
 *     public function index()
 *     {
 *         return $this->dataServer
 *             ->setDataServer('GlbColigadaDataBR')
 *             ->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre')
 *             ->setFiltro('1=1')
 *             ->readView();
 *     }
 * }
 *
 * // Via helper app() / aliases: totvs.data_server, totvs.consulta_sql,
 * // totvs.report, totvs.process, totvs.formula_visual
 * $sql = app('totvs.consulta_sql')->forCompany('01');
 * ```
 */
