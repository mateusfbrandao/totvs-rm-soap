<?php

namespace mateusfbi\TotvsRmSoap\Config;

/**
 * Configuração de conexão SOAP, independente de framework.
 */
final class ConnectionConfig
{
    /**
     * @param array<string, string> $companies Mapa coligada => URL base
     */
    public function __construct(
        public readonly string $url,
        public readonly string $user,
        public readonly string $pass,
        public readonly array $companies = [],
        public readonly int $connectionTimeout = 1800,
        public readonly bool $verifyPeer = true,
        public readonly bool $verifyPeerName = true,
        public readonly bool $allowSelfSigned = false,
    ) {}

    /**
     * Cria a config a partir de um array (ex.: config do Laravel ou arquivo PHP).
     *
     * Chaves aceitas: url, user, pass, companies, connection_timeout,
     * verify_peer, verify_peer_name, allow_self_signed.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            url: (string) ($config['url'] ?? ''),
            user: (string) ($config['user'] ?? ''),
            pass: (string) ($config['pass'] ?? ''),
            companies: self::normalizeCompanies($config['companies'] ?? []),
            connectionTimeout: (int) ($config['connection_timeout'] ?? 1800),
            verifyPeer: self::toBool($config['verify_peer'] ?? true),
            verifyPeerName: self::toBool($config['verify_peer_name'] ?? true),
            allowSelfSigned: self::toBool($config['allow_self_signed'] ?? false),
        );
    }

    /**
     * @param array<string, string>|string|null $companies
     * @return array<string, string>
     */
    public static function normalizeCompanies(array|string|null $companies): array
    {
        if (empty($companies)) {
            return [];
        }

        if (is_array($companies)) {
            $map = [];
            foreach ($companies as $code => $url) {
                if ($code !== '' && $code !== null && $url !== '' && $url !== null) {
                    $map[(string) $code] = (string) $url;
                }
            }
            return $map;
        }

        $map = [];
        $pairs = array_filter(array_map('trim', explode(';', $companies)));
        foreach ($pairs as $pair) {
            [$code, $url] = array_map('trim', explode('|', $pair, 2)) + [null, null];
            if (!empty($code) && !empty($url)) {
                $map[$code] = $url;
            }
        }

        return $map;
    }

    public function resolveBaseUrl(?string $companyCode): string
    {
        if ($companyCode === null || $companyCode === '' || empty($this->companies)) {
            return $this->url;
        }

        return $this->companies[$companyCode] ?? $this->url;
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
