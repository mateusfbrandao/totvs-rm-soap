<?php

namespace mateusfbi\TotvsRmSoap\Exceptions;

use Exception;

class ConnectionException extends Exception
{
    public static function forUrl(string $url, string $originalMessage): self
    {
        $message = sprintf(
            'Falha na conexão com o servidor TOTVS RM na URL: %s. Mensagem original: %s',
            $url,
            $originalMessage
        );

        return new self($message);
    }
}