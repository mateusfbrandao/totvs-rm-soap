<?php

namespace mateusfbi\TotvsRmSoap\Utils;

class Serialize
{
    /**
     * @param mixed $response
     * @return array
     */

    public static function result($response): array
    {
        if (empty($response)) {
            return [];
        }
        
        // Evita warnings libxml no console em caso de XML inválido
        $use_errors = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);
        libxml_use_internal_errors($use_errors);
        
        if ($xml === false) {
            return [];
        }
        
        $result = json_decode(json_encode($xml), true);
        return is_array($result) ? $result : [];
    }
}
