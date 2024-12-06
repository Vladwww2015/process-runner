<?php

namespace IntegrationHelper\ProcessRunner\Model;

interface ProcessRunnerInterface
{
    public function run(string $identity = ''): void;

    public function prepare(string $identity = ''): void;

    public function clear(string $identity = ''): void;
}
