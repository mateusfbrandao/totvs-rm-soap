<?php

namespace TotvsRmSoap\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \TotvsRmSoap\Services\DataServer dataServer()
 * @method static \TotvsRmSoap\Services\ConsultaSQL consultaSQL()
 * @method static \TotvsRmSoap\Services\Report report()
 * @method static \TotvsRmSoap\Services\Process process()
 * @method static \TotvsRmSoap\Services\FormulaVisual formulaVisual()
 */
class TotvsRM extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'totvs-rm';
    }
}
