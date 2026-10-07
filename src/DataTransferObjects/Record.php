<?php

namespace mateusfbi\TotvsRmSoap\DataTransferObjects;

class Record
{
    public function __construct(
        public readonly array $data
    ) {}

    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
