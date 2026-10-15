<?php

namespace mateusfbi\TotvsRmSoap\DataTransferObjects;

class Record
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly array $data
    ) {}

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
