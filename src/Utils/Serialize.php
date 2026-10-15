<?php

namespace mateusfbi\TotvsRmSoap\Utils;

use UnexpectedValueException;

class Serialize
{
    /**
     * Converte o XML de resposta do RM em array.
     *
     * Retorna [] apenas para resposta vazia (null, '' ou []). XML
     * inválido agora lança exceção em vez de ser confundido com
     * "sem registros".
     *
     * @param mixed $response
     * @return array
     *
     * @throws UnexpectedValueException Quando a resposta não é string nem vazia, ou o XML é inválido.
     */

    public static function result($response): array
    {
        if ($response === null || $response === '' || $response === []) {
            return [];
        }

        if (!is_string($response)) {
            throw new UnexpectedValueException(
                sprintf('Resposta SOAP inválida: esperado XML string, recebido %s.', get_debug_type($response))
            );
        }

        // Evita warnings libxml no console em caso de XML inválido
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($response);

            if ($xml === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                $details = [];
                foreach ($errors as $error) {
                    $details[] = trim(sprintf('linha %d: %s', $error->line, $error->message));
                }
                throw new UnexpectedValueException(
                    'Resposta SOAP com XML inválido.' . ($details !== [] ? ' ' . implode('; ', $details) : '')
                );
            }

            $result = json_decode(json_encode($xml), true);

            return is_array($result) ? $result : [];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
