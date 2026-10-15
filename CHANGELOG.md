# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

## [4.3.0] - 2026-10-15
### Alterado
- 27 setters `set*(): void` → `static` com `return $this` (`DataServer`, `ConsultaSQL`, `Report`, `Process`, `FormulaVisual`); encadeamento fluente liberado.
- `DataServer::setXML(): string` → `static` (use `getXML()` para ler o XML).
- `TotvsRmSoapProvider`: services stateful + `totvs-rm` de `singleton` → `bind` (sem vazamento de estado no Octane/filas); `Config` e `WebService` seguem singleton.

### Notas
- `ExecuteWithParams`/`RealizarConsultaSQL`: métodos PHP são case-insensitive, então qualquer case já chama o mesmo método; sem mudança necessária.

## [4.2.0] - 2026-10-15
### Adicionado
- `Config::assertValid()` (fail-fast no primeiro uso, sem quebrar boot sem env) e `__debugInfo()` ocultando `pass`.
- Cache de `SoapClient` por URL+path em `WebService::getClient()`.
- `Report::parseReportList()` extraído e testável.
- Suite `tests/Unit/RobustnessTest.php` (15 testes).

### Alterado
- `Utils\Serialize::result()` só retorna `[]` para vazio; XML inválido/tipo inesperado lança `UnexpectedValueException` com detalhes do libxml.
- Escaping XML via `createTextNode` em `DataServer` e `Report` (valores com `&<>` agora geram XML válido).
- Validações `InvalidArgumentException`: tags XML, `ConsultaSQL::setParametros` (chave sem `;`/`=`, valor escalar sem `;`), `Report` (`Type` em String/Int16/Int32/DateTime, `DateTime` ISO 8601, chaves obrigatórias).
- `Report::getReportList()` desembrulha `s:N:"...";` dos dois lados, `nome` com vírgula reconstruído com `,` fiel, malformados ignorados sem `error_log`.
- `WebService::createSoapClient()` captura `\Throwable`.

## [4.1.0] - 2026-10-15
### Adicionado
- `Services\AbstractService`: base com `SoapClient` lazy, `forCompany()` sem rede, `setSoapClient()`/`getCompanyCode()` para testes.
- `LICENSE` MIT.
- Validação de campos obrigatórios com `InvalidArgumentException` em `DataServer`, `ConsultaSQL`, `Report`, `Process` e `FormulaVisual`.

### Alterado
- `SoapClient` criado apenas no primeiro uso (antes era no `__construct`/`forCompany`). Falhas de conexão agora ocorrem na chamada SOAP, não na construção.
- `Traits\WebServiceCaller`: visibilidade `protected`, mensagem com `static::class`, `catch (Throwable)` com repasse de `ConnectionException`, `$defaultValue` deprecated (ignorado).

## [4.0.0] - 2026-10-07
### Alterado
- A configuração de conexão agora é `mateusfbi\TotvsRmSoap\Connection\Config`. O arquivo publicado do Laravel continua em `src/config/totvsrmsoap.php`.

### Breaking
- `mateusfbi\TotvsRmSoap\Config\ConnectionConfig` deixou de existir. Use `mateusfbi\TotvsRmSoap\Connection\Config`.

## [3.0.2] - 2026-10-07
### Removido
- Aliases de compatibilidade do namespace curto `TotvsRmSoap\`. Use apenas `mateusfbi\TotvsRmSoap\`.

## [3.0.1] - 2026-10-07
### Alterado
- Namespace canônico volta a ser `mateusfbi\TotvsRmSoap\` (mesmo do antigo `-laravel`), reduzindo impacto na migração.

## [3.0.0] - 2026-10-07
### Alterado
- Core framework-agnóstico via `ConnectionConfig` (sem dependência de `config()`/`env()` helpers).
- Removidas dependências `vlucas/phpdotenv` e `spatie/array-to-xml`.
- PHP mínimo elevado para `^8.2`.
- Pacote `mateusfbi/totvs-rm-soap-laravel` unificado neste (`replace` + abandoned no pacote Laravel).

### Adicionado
- Integração Laravel opcional (Provider, Facade, aliases `totvs.*`).
- Suporte a URL por empresa (`forCompany` / `companies`).
- Suite de testes PHPUnit (unitários + integração opcional).

### Breaking
- `WebService` agora exige `ConnectionConfig` no construtor (PHP puro).
- Consumidores do antigo `-laravel` devem trocar o pacote Composer; o namespace `mateusfbi\TotvsRmSoap\` é preservado a partir da v3.0.1.

## [2.0.2] - 2026-02-05
### Alterado
- Renomeada a classe `TotvsRM` para `WebService` (Root) para melhor semântica.
- Atualizado `TotvsRmSoapProvider` para refletir a mudança e tratar conflitos de nome.

## [2.0.1] - 2026-02-05
### Corrigido
- Verificação de existência do arquivo `.env` antes do carregamento para evitar erros em instalações via Composer (compatibilidade com Laravel).
- Correção de namespaces em `TotvsRmSoapProvider`.
- Adição da classe `TotvsRM` que estava faltando.

## [2.0.0] - 2026-02-05
### Alterado
- Namespace atualizado de `mateusfbi\TotvsRmSoap` para `TotvsRmSoap` (Breaking Change).

### Adicionado
- Método `setXMLFromArray` em `DataServer` para facilitar a criação de XML a partir de arrays.
- Método `getXML` em `DataServer` para visualizar o XML gerado.

### Corrigido
- Removida a redeclaração da propriedade `$webService` nas classes de serviço para corrigir erro fatal de tipagem.
- Ajustes de compatibilidade de tipos no PHP 8.