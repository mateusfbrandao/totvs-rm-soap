<?php

namespace mateusfbi\TotvsRmSoap;

use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\Services\FormulaVisual;
use mateusfbi\TotvsRmSoap\Services\Process;
use mateusfbi\TotvsRmSoap\Services\Report;

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
