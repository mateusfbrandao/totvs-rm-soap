<?php

namespace mateusfbi\TotvsRmSoap\Traits;

use mateusfbi\TotvsRmSoap\Exceptions\ConnectionException;
use Throwable;

trait WebServiceCaller
{
    /**
     * Método auxiliar para chamar métodos do serviço web e tratar exceções.
     *
     * @param  string  $methodName  Nome do método a ser chamado no serviço web.
     * @param  array  $params  Parâmetros a serem passados para o método.
     * @param  mixed  $defaultValue  Deprecated: mantido por compatibilidade, não é mais utilizado.
     * @return mixed O resultado da chamada do método.
     *
     * @throws ConnectionException Quando a chamada SOAP falha (conexão, autenticação, etc.).
     */
    protected function callWebServiceMethod(string $methodName, array $params = [], $defaultValue = null)
    {
        try {
            $client = method_exists($this, 'soap') ? $this->soap() : $this->webService;
            $execute = $client->$methodName($params);
            $resultProperty = $methodName.'Result';

            return $execute->$resultProperty;
        } catch (ConnectionException $e) {
            throw $e;
        } catch (Throwable $e) {
            // Propaga a falha para o chamador tratar como ERRO.
            // Antes o erro era engolido e o $defaultValue (ex.: string vazia) era tratado como sucesso.
            throw new ConnectionException(
                sprintf(
                    "Erro ao chamar o método SOAP '%s' na classe %s: %s",
                    $methodName,
                    static::class,
                    $e->getMessage()
                ),
                (int) $e->getCode(),
                $e
            );
        }
    }
}
