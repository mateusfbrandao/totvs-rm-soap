# Guia de migração

Histórico completo em [`CHANGELOG.md`](../CHANGELOG.md). Abaixo, só o que pode
exigir ajuste no seu código ao atualizar.

## 4.0.0 — `Connection\Config`

```php
// Antes
use mateusfbi\TotvsRmSoap\Config\ConnectionConfig;
$config = new ConnectionConfig(url: ..., user: ..., pass: ...);

// Depois
use mateusfbi\TotvsRmSoap\Connection\Config;
$config = new Config(url: ..., user: ..., pass: ...);
```

## 4.1.0 — falha de conexão mudou de lugar (lazy)

O `SoapClient` passou a ser criado no **primeiro uso**, não no `new` nem no
`forCompany()`. Falhas de rede/WSDL agora estouram na chamada SOAP:

```php
// Antes: falhava aqui, se a URL estivesse errada
$ds = new DataServer($connection);

// Depois: falha aqui
$result = $ds
    ->setDataServer('GlbColigadaDataBR')
    ->setContexto('CODSISTEMA=G;CODCOLIGADA=0;CODUSUARIO=mestre')
    ->setFiltro('1=1')
    ->readView(); // <- try/catch (ConnectionException) deve estar aqui
```

Outras mudanças da 4.1.0:

- Esquecer `set*()` obrigatório agora lança `InvalidArgumentException` com o
  nome do setter (antes: `Error` de propriedade não-inicializada). Ajuste
  `catch (Error)` para `catch (InvalidArgumentException)`; `catch (Throwable)`
  continua funcionando.
- A mensagem de `ConnectionException` agora traz o nome do serviço
  (`static::class`) em vez do trait.
- O 3º argumento de `callWebServiceMethod()` (`$defaultValue`) é ignorado
  (deprecated, mantido por compatibilidade).

## 4.2.0 — erros silenciosos viraram exceção

- `Serialize::result()` com XML inválido lança `UnexpectedValueException`
  (antes retornava `[]`, confundindo erro com "sem registros"). Trate se você
  passava respostas não-confiáveis diretamente ao `Serialize`.
- `ConsultaSQL::setParametros()` rejeita chave vazia/com `;`/`=` e valor com
  `;` (`InvalidArgumentException`). Valores com `;` precisam ser remodelados
  — o protocolo `P1=V1;P2=V2` do RM não suporta o caractere.
- `Report::setParametros()` rejeita `Type` fora de `String|Int16|Int32|DateTime`
  e `DateTime` inválido.
- `Report::getReportList()` não escreve mais em `error_log` (registros
  malformados são ignorados) e o `nome` com vírgula volta fiel (antes vinha
  com espaço extra após cada vírgula).
- `DataServer::getXML()` sem `setXML()/setXMLFromArray()` lança
  `InvalidArgumentException` (antes: `Error`).

## 4.3.0 — setters fluentes e Provider transient

```php
// Depois (encadeável)
$result = $ds
    ->setDataServer('GlbColigadaDataBR')
    ->setContexto('...')
    ->setFiltro('1=1')
    ->readView();
```

- Todos os `set*(): void` viraram `static`. Ajuste necessário só se você
  **sobrescreveu** algum setter com `: void` ou usava o retorno de
  `DataServer::setXML()` como string — use `getXML()` para ler.
- No Laravel, os services e `totvs-rm` passaram de `singleton` para `bind`:
  cada `app()`/`TotvsRM::` resolve instância nova. Remova workarounds de
  "reset de estado" se você tinha algum.
- `ExecuteWithParams`/`RealizarConsultaSQL`: métodos PHP são
  case-insensitive, qualquer capitalização já chama o mesmo método.

## 4.4.0 — sem breaking

Apenas adições (`examples/`, `readViewWith()`, `Record::get()/has()`,
Pint/PHPStan/CI) e estilo de código.
