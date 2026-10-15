<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use mateusfbi\TotvsRmSoap\Connection\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function test_from_array_maps_known_keys_and_defaults(): void
    {
        $config = Config::fromArray([
            'url' => 'http://rm.local:8051',
            'user' => 'mestre',
            'pass' => 'secret',
        ]);

        $this->assertSame('http://rm.local:8051', $config->url);
        $this->assertSame('mestre', $config->user);
        $this->assertSame('secret', $config->pass);
        $this->assertSame([], $config->companies);
        $this->assertSame(1800, $config->connectionTimeout);
        $this->assertTrue($config->verifyPeer);
        $this->assertTrue($config->verifyPeerName);
        $this->assertFalse($config->allowSelfSigned);
    }

    public function test_from_array_parses_boolean_strings_and_timeout(): void
    {
        $config = Config::fromArray([
            'url' => 'http://rm.local',
            'user' => 'u',
            'pass' => 'p',
            'connection_timeout' => '90',
            'verify_peer' => 'false',
            'verify_peer_name' => '0',
            'allow_self_signed' => 'true',
        ]);

        $this->assertSame(90, $config->connectionTimeout);
        $this->assertFalse($config->verifyPeer);
        $this->assertFalse($config->verifyPeerName);
        $this->assertTrue($config->allowSelfSigned);
    }

    public function test_normalize_companies_from_array(): void
    {
        $map = Config::normalizeCompanies([
            '01' => 'http://a',
            '02' => 'http://b',
            '' => 'http://ignored',
            '03' => '',
        ]);

        $this->assertSame([
            '01' => 'http://a',
            '02' => 'http://b',
        ], $map);
    }

    public function test_normalize_companies_from_env_string(): void
    {
        $map = Config::normalizeCompanies(
            '01|http://rm-empresa01:8051;02|http://rm-empresa02:8051'
        );

        $this->assertSame([
            '01' => 'http://rm-empresa01:8051',
            '02' => 'http://rm-empresa02:8051',
        ], $map);
    }

    public function test_normalize_companies_empty_values(): void
    {
        $this->assertSame([], Config::normalizeCompanies(null));
        $this->assertSame([], Config::normalizeCompanies(''));
        $this->assertSame([], Config::normalizeCompanies([]));
    }

    public function test_resolve_base_url_uses_default_when_company_missing(): void
    {
        $config = new Config(
            url: 'http://default:8051',
            user: 'u',
            pass: 'p',
            companies: ['01' => 'http://empresa01:8051'],
        );

        $this->assertSame('http://default:8051', $config->resolveBaseUrl(null));
        $this->assertSame('http://default:8051', $config->resolveBaseUrl(''));
        $this->assertSame('http://default:8051', $config->resolveBaseUrl('99'));
        $this->assertSame('http://empresa01:8051', $config->resolveBaseUrl('01'));
    }

    public function test_resolve_base_url_without_companies_always_returns_default(): void
    {
        $config = new Config(
            url: 'http://default:8051',
            user: 'u',
            pass: 'p',
        );

        $this->assertSame('http://default:8051', $config->resolveBaseUrl('01'));
    }

    public function test_from_array_normalizes_companies_string(): void
    {
        $config = Config::fromArray([
            'url' => 'http://default',
            'user' => 'u',
            'pass' => 'p',
            'companies' => '01|http://a;02|http://b',
        ]);

        $this->assertSame('http://a', $config->resolveBaseUrl('01'));
        $this->assertSame('http://b', $config->resolveBaseUrl('02'));
    }
}
