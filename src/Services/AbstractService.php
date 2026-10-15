<?php

namespace mateusfbi\TotvsRmSoap\Services;

use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Traits\WebServiceCaller;
use SoapClient;

/**
 * Base para os serviços SOAP do TOTVS RM.
 *
 * Centraliza a criação preguiçosa (lazy) do SoapClient e a seleção
 * de empresa (coligada). O cliente só é criado no primeiro uso,
 * evitando download do WSDL no __construct.
 */
abstract class AbstractService
{
    use WebServiceCaller;
    protected WebService $connection;
    protected ?SoapClient $soapClient = null;
    protected ?string $companyCode = null;

    /** Caminho relativo do WSDL (ex.: /wsDataServer/MEX?wsdl). */
    protected string $endpointPath = '';

    public function __construct(WebService $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Seleciona a empresa (coligada) para definir a URL base do serviço.
     * Não cria o SoapClient imediatamente; apenas invalida o atual.
     */
    public function forCompany(string $companyCode): static
    {
        $this->companyCode = $companyCode;
        $this->soapClient = null;

        return $this;
    }

    public function getCompanyCode(): ?string
    {
        return $this->companyCode;
    }

    /**
     * Injeta um SoapClient pronto (útil em testes).
     */
    public function setSoapClient(SoapClient $client): static
    {
        $this->soapClient = $client;

        return $this;
    }

    protected function soap(): SoapClient
    {
        return $this->soapClient ??= $this->connection->getClient(
            $this->endpointPath,
            $this->companyCode
        );
    }
}
