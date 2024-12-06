<?php

namespace IntegrationHelper\ProcessRunner\Cron;

use Magento\Framework\ShellInterface;

use IntegrationHelper\BaseConsoleCommandRunner\Model\Traits\CliProcessRunnerTrait;

abstract class AbstractSchedule
{
    use CliProcessRunnerTrait;

    public function __construct(protected ShellInterface $shell)
    {}

    /**
     * @return ShellInterface
     */
    protected function getShell(): ShellInterface
    {
        return $this->shell;
    }
}
