# TotvsRmSoap

Biblioteca PHP para integração SOAP com o TOTVS RM. Funciona com **Laravel** (Provider/Facade) ou **PHP puro** / qualquer framework.

> **Nota:** o pacote `mateusfbi/totvs-rm-soap-laravel` foi unificado neste. Migre para `mateusfbi/totvs-rm-soap`.

## Requisitos

- PHP 8.2 ou superior
- Extensões SOAP e XML do PHP
- Composer
- Laravel (opcional — apenas se for usar Provider/Facade)

## Instalação

```bash
composer require mateusfbi/totvs-rm-soap
```

### Migrando de `totvs-rm-soap-laravel`

```bash
composer remove mateusfbi/totvs-rm-soap-laravel
composer require mateusfbi/totvs-rm-soap
```

O namespace **`mateusfbi\TotvsRmSoap\`** permanece o mesmo — em geral não é preciso alterar os `use`.

### Laravel

```bash
php artisan vendor:publish --tag=config
```

## Configuração (Laravel)

Variáveis no `.env`:

```
TOTVSRM_WSURL=http://localhost:8051
TOTVSRM_USER=usuario
TOTVSRM_PASS=senha
TOTVSRM_CONNECTION_TIMEOUT=1800
```

URL por empresa em `config/totvsrmsoap.php`:

```php
'companies' => [
    '01' => 'http://rm-empresa01:8051',
    '02' => 'http://rm-empresa02:8051',
],
```

Ou via `.env`:

```
TOTVSRM_COMPANIES="01|http://rm-empresa01:8051;02|http://rm-empresa02:8051"
```

## Uso com PHP puro

```php
use mateusfbi\TotvsRmSoap\Config\ConnectionConfig;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;

$config = new ConnectionConfig(
    url: 'http://localhost:8051',
    user: 'usuario',
    pass: 'senha',
    companies: [
        '01' => 'http://rm-empresa01:8051',
    ],
);

$connection = new WebService($config);

$ds = new DataServer($connection);
$ds->setDataServer('GlbColigadaDataBR');
$ds->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre');
$ds->setFiltro('1=1');
$result = $ds->readView();

$sql = (new ConsultaSQL($connection))->forCompany('01');
$sql->setSentenca('SENTENCA_EXEMPLO');
$sql->setColigada(1);
$sql->setSistema('G');
$sql->setParametros(['P1' => 'VALOR']);
$res = $sql->RealizarConsultaSQL();
```

Há um exemplo em `index.php`.

## Uso com Laravel

### Injeção de dependência

```php
use mateusfbi\TotvsRmSoap\Services\DataServer;

$ds->setDataServer('GlbColigadaDataBR');
$ds->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre');
$ds->setFiltro('1=1');
$result = $ds->readView();
```

### Helper `app()` / Facade

Aliases: `totvs.data_server`, `totvs.consulta_sql`, `totvs.report`, `totvs.process`, `totvs.formula_visual`

```php
use mateusfbi\TotvsRmSoap\Facades\TotvsRM;

$ds = TotvsRM::dataServer()->forCompany('01');
```

## Testes

```bash
composer install
composer test
```

Integração (RM real):

```bash
export TOTVSRM_RUN_INTEGRATION=1
export TOTVSRM_WSURL=http://localhost:8051
export TOTVSRM_USER=usuario
export TOTVSRM_PASS=senha
composer test:integration
```

## Licença

MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.
