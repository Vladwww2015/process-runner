<?php

namespace IntegrationHelper\ProcessRunner\Cron;

use IntegrationHelper\ProcessRunner\Console\Command\Clear;

class CleanBySchedule extends AbstractSchedule
{
    public function execute()
    {
        $this->runCommand(Clear::COMMAND);
    }
}
