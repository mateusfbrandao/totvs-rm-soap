# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

## [3.0.1] - 2026-10-07
### Alterado
- Namespace canônico volta a ser `mateusfbi\TotvsRmSoap\` (mesmo do antigo `-laravel`), reduzindo impacto na migração.
- Aliases de compatibilidade para `TotvsRmSoap\` (código da v2.x / v3.0.0).

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