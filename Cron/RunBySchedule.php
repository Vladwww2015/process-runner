<?php

namespace IntegrationHelper\ProcessRunner\Cron;

use IntegrationHelper\ProcessRunner\Console\Command\Run;

class RunBySchedule extends AbstractSchedule
{
    public function execute()
    {
        $this->runCommand(Run::COMMAND);
    }
}
