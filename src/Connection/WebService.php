<?php

namespace mateusfbi\TotvsRmSoap\Connection;

use mateusfbi\TotvsRmSoap\Exceptions\ConnectionException;
use SoapClient;

/**
 * Cria instâncias de SoapClient a partir de Config.
 *
 * Framework-agnóstico: não depende de helpers do Laravel.
 */
class WebService
{
    /** @var array<string, SoapClient> Cache por URL+path para evitar refetch do WSDL. */
    private array $clients = [];

    public function __construct(
        private readonly Config $config
    ) {}

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param string $path Caminho relativo do WSDL (ex.: /wsDataServer/MEX?wsdl)
     */
    public function getClient(string $path, ?string $companyCode = null): SoapClient
    {
        $this->config->assertValid();

        $baseUrl = $this->config->resolveBaseUrl($companyCode);
        $url = rtrim($baseUrl, '/') . $path;

        return $this->clients[$url] ??= $this->createSoapClient($url, $this->buildOptions());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOptions(): array
    {
        return [
            'login' => $this->config->user,
            'password' => $this->config->pass,
            'authentication' => SOAP_AUTHENTICATION_BASIC,
            'soap_version' => SOAP_1_1,
            'trace' => true,
            'exceptions' => true,
            'connection_timeout' => $this->config->connectionTimeout,
            'stream_context' => stream_context_create([
                'ssl' => [
                    // Em produção, mantenha verify_peer/verify_peer_name como true
                    // e configure os certificados CA corretamente.
                    'verify_peer' => $this->config->verifyPeer,
                    'verify_peer_name' => $this->config->verifyPeerName,
                    'allow_self_signed' => $this->config->allowSelfSigned,
                ],
            ]),
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createSoapClient(string $url, array $options): SoapClient
    {
        try {
            return new SoapClient($url, $options);
        } catch (\Throwable $e) {
            throw ConnectionException::forUrl($url, $e->getMessage());
        }
    }
}
