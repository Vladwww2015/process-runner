<?php

namespace IntegrationHelper\ProcessRunner\Model;

interface ModelInstanceInterface
{
    public function runProcess(array $params = []): bool;
}
