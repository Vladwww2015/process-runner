<?php
namespace IntegrationHelper\ProcessRunner\Console\Command;

use IntegrationHelper\ProcessRunner\Model\ProcessRunnerInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Clear extends Command
{
    public const COMMAND = 'integration:process:runner-clear';

    public function __construct(
        protected ProcessRunnerInterface $processRunner,
        string $name = null
    ){
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND)
            ->setDescription('Run Processes Clear Completed');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->processRunner->clear();
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

}
