<?php

namespace mateusfbi\TotvsRmSoap\Services;

use InvalidArgumentException;

/**
 * Classe Process
 *
 * Responsável por gerenciar a execução de processos via serviço SOAP no projeto TotvsRmSoap.
 *
 * A classe prepara os parâmetros da requisição, invoca o serviço SOAP através do WebService e
 * trata a resposta utilizando a utilidade de serialização. Ela é projetada para centralizar
 * toda lógica de processamento, facilitando a manutenção e extensibilidade do código.
 *
 * @package mateusfbi\TotvsRmSoap\Services
 */
class Process extends AbstractService
{
    protected string $endpointPath = '/wsProcess/MEX?wsdl';
    private string $process;
    private string $xml;
    private string $jobId;
    private string $execId;

    /**
     * Define o nome do processo que será executado.
     *
     * @param string $process Nome do processo a ser chamado no serviço SOAP.
     * @return void
     */
    public function setProcess(string $process): static
    {
        $this->process = $process;
        return $this;
    }

    /**
     * Define o XML com os parâmetros da requisição.
     *
     * @param string $xml XML contendo os parâmetros do processo.
     * @return void
     */
    public function setXML(string $xml): static
    {
        $this->xml = $xml;
        return $this;
    }

    /**
     * Define o identificador do job do processo.
     *
     * @param string $jobId Identificador único do job.
     * @return void
     */
    public function setJobId(string $jobId): static
    {
        $this->jobId = $jobId;
        return $this;
    }

    /**
     * Define o identificador da execução do processo.
     *
     * @param string $execId Identificador da execução.
     * @return void
     */
    public function setExecId(string $execId): static
    {
        $this->execId = $execId;
        return $this;
    }

    /**
     * Executa o processo utilizando os parâmetros em formato XML.
     *
     * Prepara a requisição SOAP com o nome do processo e o XML de parâmetros configurado,
     * envia a requisição e retorna o resultado da execução.
     *
     * @return int Resultado retornado pelo serviço SOAP para a execução com XML.
     */
    public function executeWithXmlParams(): int
    {
        $this->requireSet('process', 'setProcess');
        $this->requireSet('xml', 'setXML');
        $params = [
            'ProcessServerName' => $this->process,
            'strXmlParams'      => $this->xml
        ];
        return (int) $this->callWebServiceMethod('ExecuteWithXmlParams', $params);
    }

    /**
     * Executa o processo utilizando os parâmetros em formato padrão.
     *
     * Prepara a requisição SOAP com o nome do processo e o XML de parâmetros configurado,
     * envia a requisição e retorna o resultado da execução.
     *
     * @return int Resultado retornado pelo serviço SOAP para a execução com parâmetros padrão.
     */
    public function ExecuteWithParams(): int
    {
        $this->requireSet('process', 'setProcess');
        $this->requireSet('xml', 'setXML');
        $params = [
            'ProcessServerName' => $this->process,
            'strXmlParams'      => $this->xml
        ];
        return (int) $this->callWebServiceMethod('ExecuteWithParams', $params);
    }

    /**
     * Obtém o status do processo.
     *
     * Envia uma requisição SOAP contendo o jobId e execId configurados e retorna o status atual do processo.
     *
     * @return int Status atual do processo, conforme retornado pelo serviço SOAP.
     */
    public function getProcessStatus(): int
    {
        $this->requireSet('jobId', 'setJobId');
        $this->requireSet('execId', 'setExecId');
        $params = [
            'jobId'  => $this->jobId,
            'execId' => $this->execId
        ];
        return (int) $this->callWebServiceMethod('getProcessStatus', $params);
    }

    private function requireSet(string $property, string $setter): void
    {
        if (!isset($this->$property)) {
            throw new InvalidArgumentException(
                sprintf('Process: chame %s() antes de executar a operação.', $setter)
            );
        }
    }
}
