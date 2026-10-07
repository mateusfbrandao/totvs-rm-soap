<?php

namespace mateusfbi\TotvsRmSoap\Tests\Unit;

use mateusfbi\TotvsRmSoap\Connection\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testFromArrayMapsKnownKeysAndDefaults(): void
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

    public function testFromArrayParsesBooleanStringsAndTimeout(): void
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

    public function testNormalizeCompaniesFromArray(): void
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

    public function testNormalizeCompaniesFromEnvString(): void
    {
        $map = Config::normalizeCompanies(
            '01|http://rm-empresa01:8051;02|http://rm-empresa02:8051'
        );

        $this->assertSame([
            '01' => 'http://rm-empresa01:8051',
            '02' => 'http://rm-empresa02:8051',
        ], $map);
    }

    public function testNormalizeCompaniesEmptyValues(): void
    {
        $this->assertSame([], Config::normalizeCompanies(null));
        $this->assertSame([], Config::normalizeCompanies(''));
        $this->assertSame([], Config::normalizeCompanies([]));
    }

    public function testResolveBaseUrlUsesDefaultWhenCompanyMissing(): void
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

    public function testResolveBaseUrlWithoutCompaniesAlwaysReturnsDefault(): void
    {
        $config = new Config(
            url: 'http://default:8051',
            user: 'u',
            pass: 'p',
        );

        $this->assertSame('http://default:8051', $config->resolveBaseUrl('01'));
    }

    public function testFromArrayNormalizesCompaniesString(): void
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
