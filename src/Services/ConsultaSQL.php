<?php

namespace mateusfbi\TotvsRmSoap\Services;

use mateusfbi\TotvsRmSoap\Utils\Serialize;

use InvalidArgumentException;

/**
 * Classe ConsultaSQL
 *
 * Responsável por realizar consultas SQL através do serviço SOAP da Totvs RM.
 * Essa classe prepara os parâmetros da consulta e invoca o serviço SOAP específico
 * para execução da sentença SQL, retornando o resultado processado.
 *
 * @package mateusfbi\TotvsRmSoap\Services
 */

class ConsultaSQL extends AbstractService
{
    protected string $endpointPath = '/wsConsultaSQL/MEX?wsdl';
    private string $sentenca;
    private int $coligada;
    private string $sistema;
    private string $parametros;

    /**
     * Define a sentença SQL para a consulta.
     *
     * @param string $sentenca Código da Sentença SQL a ser utilizada.
     * @return void
     */
    public function setSentenca(string $sentenca): void
    {
        $this->sentenca = $sentenca;
    }

    /**
     * Define o código da coligada para a consulta.
     *
     * @param int $coligada Código da coligada onde a consulta será executada.
     * @return void
     */
    public function setColigada(int $coligada): void
    {
        $this->coligada = $coligada;
    }

    /**
     * Define o código do sistema para a consulta.
     *
     * @param string $sistema Código do sistema associado à consulta.
     * @return void
     */
    public function setSistema(string $sistema): void
    {
        $this->sistema = $sistema;
    }

    /**
     * Configura os parâmetros adicionais da consulta.
     *
     * Recebe um array associativo onde cada par chave-valor é convertido em uma string
     * no formato "chave=valor". Todos os parâmetros são então unidos por ponto e vírgula.
     *
     * Valores escalares são convertidos para string (bool vira 1/0). Chaves vazias
     * ou contendo ';'/'=' e valores contendo ';' lançam exceção, pois corromperiam
     * o protocolo "P1=V1;P2=V2" do RM.
     *
     * @param array $params Array associativo com os parâmetros adicionais.
     * @return void
     */
    public function setParametros(array $params = []): void
    {
        $array = [];

        foreach ($params as $key => $value) {
            $name = (string) $key;
            if ($name === '' || str_contains($name, ';') || str_contains($name, '=')) {
                throw new InvalidArgumentException(
                    sprintf("ConsultaSQL: nome de parâmetro inválido '%s'.", $name)
                );
            }
            if (is_array($value) || is_object($value)) {
                throw new InvalidArgumentException(
                    sprintf("ConsultaSQL: valor do parâmetro '%s' deve ser escalar.", $name)
                );
            }
            $text = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
            if (str_contains($text, ';')) {
                throw new InvalidArgumentException(
                    sprintf("ConsultaSQL: valor do parâmetro '%s' não pode conter ';'.", $name)
                );
            }

            $array[] = "{$name}={$text}";
        }

        $this->parametros = implode(';', $array);
    }

    /**
     * Realiza a consulta SQL através do serviço SOAP.
     *
     * Este método monta os parâmetros da consulta utilizando os dados definidos pelas
     * configurações anteriores (sentença, coligada, sistema e parâmetros), invoca o método
     * RealizarConsultaSQL do serviço SOAP e processa o retorno utilizando a classe Serialize.
     *
     * Em caso de erro durante a consulta, uma mensagem será exibida.
     *
     * @return array Array associativo contendo a resposta da consulta. Se a consulta tiver sucesso,
     *               retorna o resultado encontrado na chave 'Resultado'. Caso contrário, retorna false.
     */
    public function RealizarConsultaSQL(): array
    {
        if (!isset($this->sentenca)) {
            throw new InvalidArgumentException('ConsultaSQL: chame setSentenca() antes de executar a operação.');
        }
        if (!isset($this->coligada)) {
            throw new InvalidArgumentException('ConsultaSQL: chame setColigada() antes de executar a operação.');
        }
        if (!isset($this->sistema)) {
            throw new InvalidArgumentException('ConsultaSQL: chame setSistema() antes de executar a operação.');
        }
        $params = [
            'codSentenca' => $this->sentenca,
            'codColigada' => $this->coligada,
            'codSistema' => $this->sistema,
            'parameters' => isset($this->parametros) && $this->parametros !== '' ? $this->parametros : null,
        ];
        $result = $this->callWebServiceMethod('RealizarConsultaSQL', $params);
        return ['response' => Serialize::result($result)['Resultado'] ?? false];
    }

}
