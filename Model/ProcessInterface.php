<?php

namespace IntegrationHelper\ProcessRunner\Model;

interface ProcessInterface
{
    public function isMultiProcess(): bool;

    public function getMultiProcessCount(): int;
}
