# Uso no Laravel

## Configuração

```bash
php artisan vendor:publish --tag=config
```

`.env`:

```dotenv
TOTVSRM_WSURL=http://localhost:8051
TOTVSRM_USER=usuario
TOTVSRM_PASS=senha
TOTVSRM_CONNECTION_TIMEOUT=60
# Opcional, multi-empresa:
TOTVSRM_COMPANIES="01|http://rm-empresa01:8051;02|http://rm-empresa02:8051"
```

Ou edite `config/totvsrmsoap.php` (`companies` como array).

## Via Facade

```php
use mateusfbi\TotvsRmSoap\Facades\TotvsRM;

$result = TotvsRM::dataServer()->forCompany('01')
    ->setDataServer('GlbColigadaDataBR')
    ->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre')
    ->setFiltro('1=1')
    ->readView();
```

## Via injeção de dependência

```php
use mateusfbi\TotvsRmSoap\Services\DataServer;

class ColigadaController
{
    public function __construct(private DataServer $dataServer) {}

    public function index()
    {
        return $this->dataServer
            ->setDataServer('GlbColigadaDataBR')
            ->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre')
            ->setFiltro('1=1')
            ->readView();
    }
}
```

## Via `app()` / aliases

Aliases: `totvs.data_server`, `totvs.consulta_sql`, `totvs.report`,
`totvs.process`, `totvs.formula_visual`.

```php
$sql = app('totvs.consulta_sql')->forCompany('01');
```

> Os services são registrados como `bind` (transient): cada resolução é uma
> instância nova, sem vazamento de `setFiltro`/`setDataServer` entre usos
> (Octane/filas). `Config` e `WebService` seguem `singleton`.
