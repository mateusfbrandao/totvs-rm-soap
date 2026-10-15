# Troubleshooting

## `ConnectionException: Falha na conexão ... na URL: ...`

O `SoapClient` não conseguiu baixar o WSDL. Verifique, nesta ordem:

1. A URL base (`TOTVSRM_WSURL`) alcança `http://host:8051/wsDataServer/MEX?wsdl`
   no navegador/curl da máquina que roda o PHP.
2. Usuário/senha (`TOTVSRM_USER`/`TOTVSRM_PASS`) — o RM usa Basic Auth.
3. `forCompany('XX')` com código inexistente **cai silenciosamente para a URL
   default** (`Config::resolveBaseUrl()`). Confira com `$service->getCompanyCode()`
   e o mapa `companies`.

## SSL: `verify_peer` / self-signed

Erro de certificado em ambiente com HTTPS interno:

```dotenv
TOTVSRM_SSL_VERIFY_PEER=false
TOTVSRM_SSL_VERIFY_PEER_NAME=false
TOTVSRM_SSL_ALLOW_SELF_SIGNED=true
```

> Só para homologação. Em produção mantenha `true` e instale o CA correto.

## Timeout

O default de `connection_timeout` é **1800s** (relatórios longos). Para falhar
rápido em homologação, baixe via `connection_timeout` (segundos):

```php
new Config(url: ..., user: ..., pass: ..., connectionTimeout: 60);
```

`connection_timeout <= 0` lança `InvalidArgumentException` no primeiro uso
(`Config::assertValid()` — de propósito, para não quebrar o boot sem env).

## Tabela de exceções

| Exceção | Quando |
| --- | --- |
| `ConnectionException` | Rede/WSDL, auth, falha SOAP, `Error` interno do `SoapClient` |
| `InvalidArgumentException` | Esqueceu `set*()` obrigatório, parâmetro/tag XML inválida, `Type` de relatório desconhecido, config vazia |
| `UnexpectedValueException` | Resposta do RM com XML inválido (`Serialize::result()`) |

## `generateReport()` não retornou GUID

O RM devolve texto de erro no lugar do GUID. Valide o formato antes de seguir
(padrão usado em `examples/`):

```php
$guid = $rp->generateReport();
if (! preg_match('/^[0-9a-fA-F-]{36}$/', $guid)) {
    throw new RuntimeException('RM recusou o relatório: '.$guid);
}
```

## Warnings do libxml no XML de parâmetros do `Report`

Os namespaces `d3p2/d3p3` replicam a serialização .NET do RM e não são URIs
válidas. O RM aceita; o libxml avisa ao reparsear. Não altere o formato sem
validar contra o RM real.
