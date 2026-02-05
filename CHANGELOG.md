# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

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