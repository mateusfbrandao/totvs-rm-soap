<?php

namespace TotvsRmSoap\DataTransferObjects;

class ReadViewParams
{
    public function __construct(
        public string $dataServerName,
        public ?string $filter = null,
        public ?string $context = null,
    ) {}
}
