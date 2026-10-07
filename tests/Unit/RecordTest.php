<?php

namespace TotvsRmSoap\Tests\Unit;

use TotvsRmSoap\DataTransferObjects\Record;
use PHPUnit\Framework\TestCase;

class RecordTest extends TestCase
{
    public function testAccessorsAndToArray(): void
    {
        $record = new Record(['CODCOLIGADA' => '1', 'NOME' => 'Empresa']);

        $this->assertSame('1', $record->CODCOLIGADA);
        $this->assertSame('Empresa', $record->NOME);
        $this->assertNull($record->INEXISTENTE);
        $this->assertSame([
            'CODCOLIGADA' => '1',
            'NOME' => 'Empresa',
        ], $record->toArray());
    }
}
