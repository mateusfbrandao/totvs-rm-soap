<?php

namespace mateusfbi\TotvsRmSoap\Traits;

use Exception;
use mateusfbi\TotvsRmSoap\Exceptions\ConnectionException;

trait WebServiceCaller
{
    /**
     * Método auxiliar para chamar métodos do serviço web e tratar exceções.
     *
     * @param  string  $methodName  Nome do método a ser chamado no serviço web.
     * @param  array  $params  Parâmetros a serem passados para o método.
     * @param  mixed  $defaultValue  Mantido por compatibilidade; não é mais retornado em caso de erro.
     * @return mixed O resultado da chamada do método.
     *
     * @throws ConnectionException Quando a chamada SOAP falha (conexão, autenticação, etc.).
     */
    private function callWebServiceMethod(string $methodName, array $params = [], $defaultValue = null)
    {
        try {
            $execute = $this->webService->$methodName($params);
            $resultProperty = $methodName.'Result';

            return $execute->$resultProperty;
        } catch (Exception $e) {
            // Propaga a falha para o middleware marcar a integração como ERRO.
            // Antes o erro era engolido e o $defaultValue (ex.: string vazia) era tratado como sucesso.
            throw new ConnectionException(
                sprintf(
                    "Erro ao chamar o método SOAP '%s' na classe %s: %s",
                    $methodName,
                    __CLASS__,
                    $e->getMessage()
                ),
                (int) $e->getCode(),
                $e
            );
        }
    }
}
