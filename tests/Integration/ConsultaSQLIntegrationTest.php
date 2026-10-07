<?php

namespace mateusfbi\TotvsRmSoap\Tests\Integration;

use mateusfbi\TotvsRmSoap\Connection\Config;
use mateusfbi\TotvsRmSoap\Connection\WebService;
use mateusfbi\TotvsRmSoap\Services\ConsultaSQL;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class ConsultaSQLIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('TOTVSRM_RUN_INTEGRATION') !== '1') {
            $this->markTestSkipped(
                'Defina TOTVSRM_RUN_INTEGRATION=1 e as credenciais TOTVSRM_* para rodar integração.'
            );
        }

        foreach (['TOTVSRM_WSURL', 'TOTVSRM_USER', 'TOTVSRM_PASS'] as $key) {
            if (!getenv($key)) {
                $this->markTestSkipped("Variável de ambiente {$key} não definida.");
            }
        }
    }

    public function testRealizarConsultaSQLAgainstLiveRm(): void
    {
        $sentenca = getenv('TOTVSRM_TEST_SENTENCA') ?: 'WORKDAY.002';

        $config = new Config(
            url: (string) getenv('TOTVSRM_WSURL'),
            user: (string) getenv('TOTVSRM_USER'),
            pass: (string) getenv('TOTVSRM_PASS'),
            companies: Config::normalizeCompanies(getenv('TOTVSRM_COMPANIES') ?: null),
            connectionTimeout: (int) (getenv('TOTVSRM_CONNECTION_TIMEOUT') ?: 30),
        );

        $sql = new ConsultaSQL(new WebService($config));
        $sql->setSentenca($sentenca);
        $sql->setColigada((int) (getenv('TOTVSRM_TEST_COLIGADA') ?: 0));
        $sql->setSistema((string) (getenv('TOTVSRM_TEST_SISTEMA') ?: 'G'));

        $params = [];
        $paramsEnv = getenv('TOTVSRM_TEST_PARAMS');
        if ($paramsEnv) {
            $decoded = json_decode($paramsEnv, true);
            if (is_array($decoded)) {
                $params = $decoded;
            }
        }
        $sql->setParametros($params);

        $result = $sql->RealizarConsultaSQL();

        $this->assertNotNull($result);
    }
}
