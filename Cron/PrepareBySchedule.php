<?php

namespace IntegrationHelper\ProcessRunner\Cron;

use IntegrationHelper\ProcessRunner\Console\Command\Prepare;

class PrepareBySchedule extends AbstractSchedule
{
    public function execute()
    {
        $this->runCommand(Prepare::COMMAND);
    }
}
