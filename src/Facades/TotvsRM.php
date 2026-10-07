<?php

namespace mateusfbi\TotvsRmSoap\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \mateusfbi\TotvsRmSoap\Services\DataServer dataServer()
 * @method static \mateusfbi\TotvsRmSoap\Services\ConsultaSQL consultaSQL()
 * @method static \mateusfbi\TotvsRmSoap\Services\Report report()
 * @method static \mateusfbi\TotvsRmSoap\Services\Process process()
 * @method static \mateusfbi\TotvsRmSoap\Services\FormulaVisual formulaVisual()
 */
class TotvsRM extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'totvs-rm';
    }
}
