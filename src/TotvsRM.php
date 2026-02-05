<?php

namespace TotvsRmSoap;

use TotvsRmSoap\Services\ConsultaSQL;
use TotvsRmSoap\Services\DataServer;
use TotvsRmSoap\Services\FormulaVisual;
use TotvsRmSoap\Services\Process;
use TotvsRmSoap\Services\Report;

class TotvsRM
{
    public function __construct(
        protected DataServer $dataServer,
        protected ConsultaSQL $consultaSQL,
        protected Report $report,
        protected Process $process,
        protected FormulaVisual $formulaVisual,
    ) {}

    public function dataServer(): DataServer
    {
        return $this->dataServer;
    }

    public function consultaSQL(): ConsultaSQL
    {
        return $this->consultaSQL;
    }

    public function report(): Report
    {
        return $this->report;
    }

    public function process(): Process
    {
        return $this->process;
    }

    public function formulaVisual(): FormulaVisual
    {
        return $this->formulaVisual;
    }
}
