# TotvsRmSoap

[![CI](https://github.com/mateusfbrandao/totvs-rm-soap/actions/workflows/ci.yml/badge.svg)](https://github.com/mateusfbrandao/totvs-rm-soap/actions)
[![Packagist](https://img.shields.io/packagist/v/mateusfbi/totvs-rm-soap.svg)](https://packagist.org/packages/mateusfbi/totvs-rm-soap)
[![PHP](https://img.shields.io/packagist/php-v/mateusfbi/totvs-rm-soap.svg)](https://packagist.org/packages/mateusfbi/totvs-rm-soap)
[![License](https://img.shields.io/packagist/l/mateusfbi/totvs-rm-soap.svg)](LICENSE)

Biblioteca PHP para integração SOAP com o TOTVS RM. Funciona com **Laravel** (Provider/Facade) ou **PHP puro** / qualquer framework.

> Atualizando de versão antiga? Veja o [guia de migração](docs/MIGRATION.md).
> Referência de `Report`/`Process`/`FormulaVisual`: [docs/SERVICES.md](docs/SERVICES.md).

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
use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\DataServer;
use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;

$config = new Config(
    url: 'http://localhost:8051',
    user: 'usuario',
    pass: 'senha',
    companies: [
        '01' => 'http://rm-empresa01:8051',
    ],
);

$connection = new WebService($config);

// API fluente
$result = (new DataServer($connection))
    ->setDataServer('GlbColigadaDataBR')
    ->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre')
    ->setFiltro('1=1')
    ->readView();

// Ou via DTO
use mateusfbi\TotvsRmSoap\DataTransferObjects\ReadViewParams;

$result = (new DataServer($connection))->readViewWith(new ReadViewParams(
    dataServerName: 'GlbColigadaDataBR',
    filter: '1=1',
    context: 'CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre',
));

// Mapeando linhas para Records
use mateusfbi\TotvsRmSoap\DataTransferObjects\Record;

$records = array_map(fn (array $row) => new Record($row), $result['GlbColigada'] ?? []);
$cod = $records[0]->get('CODCOLIGADA');

$res = (new ConsultaSQL($connection))->forCompany('01')
    ->setSentenca('SENTENCA_EXEMPLO')
    ->setColigada(1)
    ->setSistema('G')
    ->setParametros(['P1' => 'VALOR'])
    ->RealizarConsultaSQL();
```

Exemplos em `examples/php-puro.php` e `examples/laravel.md`.

## Uso com Laravel

### Injeção de dependência

```php
use mateusfbi\TotvsRmSoap\Services\DataServer;

$result = $ds
    ->setDataServer('GlbColigadaDataBR')
    ->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre')
    ->setFiltro('1=1')
    ->readView();
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

Qualidade (Pint + PHPStan + testes, igual ao CI):

```bash
composer ci
```

Integração (RM real):

```bash
export TOTVSRM_RUN_INTEGRATION=1
export TOTVSRM_WSURL=http://localhost:8051
export TOTVSRM_USER=usuario
export TOTVSRM_PASS=senha
composer test:integration
```

## Suporte

| Versão | PHP | Status |
| --- | --- | --- |
| 4.x | ^8.2 | Mantida |
| 3.x e anteriores | — | Descontinuadas, atualize pela [migração](docs/MIGRATION.md) |

Encontrou um bug? Abra uma issue em
[GitHub Issues](https://github.com/mateusfbrandao/totvs-rm-soap/issues) com a
versão do pacote (`composer show mateusfbi/totvs-rm-soap`), a versão do PHP e
o trecho do XML/erro (sem credenciais).

## Licença

MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.
