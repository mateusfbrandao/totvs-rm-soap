<?php

use Illuminate\Container\Container;

require __DIR__.'/../vendor/autoload.php';

// Polyfill do helper config() (do illuminate/foundation) apenas para os testes,
// com fallback para o container global. Não afeta o pacote em produção.
if (! function_exists('config')) {
    function config(mixed $key = null, mixed $default = null): mixed
    {
        $config = Container::getInstance()->make('config');

        if (is_null($key)) {
            return $config->all();
        }

        return $config->get($key, $default);
    }
}
