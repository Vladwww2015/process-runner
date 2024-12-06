<?php

namespace IntegrationHelper\ProcessRunner\Model;

class Process implements ProcessInterface
{
    public function __construct(
        protected bool $isMultiProcess = false,
        protected string $multiProcessCount = '1'
    ) {}

    public function isMultiProcess(): bool
    {
        return $this->isMultiProcess;
    }

    public function getMultiProcessCount(): int
    {
        return (int) $this->multiProcessCount;
    }
}
