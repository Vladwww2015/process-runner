<?php

namespace IntegrationHelper\ProcessRunner\Model;

class ProcessPool
{
    protected $processes;

    public function __construct(
        protected ProcessInterface $defaultProcess,
        array $processes = []
    ){
        $this->processes = array_filter($processes, fn($process) => $process instanceof ProcessInterface);
    }

    public function getProcessByIdentity(string $identity): ProcessInterface
    {
        return $this->processes[$identity] ?? $this->defaultProcess;
    }

    public function getProcesses(): array
    {
        return $this->processes;
    }
}
