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
        $baseUrl = $this->config->resolveBaseUrl($companyCode);
        $url = rtrim($baseUrl, '/') . $path;

        $options = [
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

        return $this->createSoapClient($url, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createSoapClient(string $url, array $options): SoapClient
    {
        try {
            return new SoapClient($url, $options);
        } catch (\Exception $e) {
            throw ConnectionException::forUrl($url, $e->getMessage());
        }
    }
}
