<?php
namespace IntegrationHelper\ProcessRunner\Console\Command;

use IntegrationHelper\ProcessRunner\Model\ProcessRunnerInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Prepare extends Command
{
    public const COMMAND = 'integration:process:runner-prepare';

    public const IDENTITY = 'identity';

    public function __construct(
        protected ProcessRunnerInterface $processRunner,
        string $name = null
    ){
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND)
            ->setDescription('Run Processes Prepare')
            ->addOption(
                self::IDENTITY,
                'i',
                InputOption::VALUE_OPTIONAL,
                'Identity'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $identity = $input->getOption(self::IDENTITY) ?: '';

            $this->processRunner->prepare($identity);
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

}
