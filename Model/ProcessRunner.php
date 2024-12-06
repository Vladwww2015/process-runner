<?php

namespace IntegrationHelper\ProcessRunner\Model;

use IntegrationHelper\ProcessRunner\Api\ConstraintsInterface;
use IntegrationHelper\ProcessRunner\Console\Command\Run;
use IntegrationHelper\BaseConsoleCommandRunner\Model\Traits\CliProcessRunnerTrait;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\ShellInterface;

class ProcessRunner implements ProcessRunnerInterface
{
    use CliProcessRunnerTrait;

    protected const MAX_PROCESSES = 16;

    protected const MAX_NESTED_LEVEL = 96;

    protected $maxProcessed;

    protected static $currentNestedLevel = 0;

    /**
     * @param ShellInterface $shell
     * @param ProcessPool $processPool
     * @param LockManagerInterface $lockManager
     * @param ProcessProfileManager $processProfileManager
     */
    public function __construct(
        protected ShellInterface $shell,
        protected ProcessPool $processPool,
        protected LockManagerInterface $lockManager,
        protected ProcessProfileManager $processProfileManager,
        string $maxProcessed = self::MAX_PROCESSES
    ){
        $this->maxProcessed = intval($maxProcessed);
    }

    /**
     * @param string $identity
     * @return void
     */
    public function run(string $identity = ''): void
    {
        $list = $this->processProfileManager
            ->getList(
                $identity,
                [ConstraintsInterface::STATUS_WAITING]
            );

        $callback = $this->_runWaiting(count($list));

        foreach ($list as $profile) {
            $profile[ConstraintsInterface::STATUS] = ConstraintsInterface::STATUS_PROCESSING;
            $this->processProfileManager->update($profile);
            [$result, $message] = $this->_run($profile[ConstraintsInterface::MODEL], $profile[ConstraintsInterface::MODEL_METHOD_ARGUMENTS]);

            if($result === true) {
                $profile[ConstraintsInterface::MESSAGE] = '';
                $profile[ConstraintsInterface::STATUS] = ConstraintsInterface::STATUS_COMPLETE;
            } else {
                $profile[ConstraintsInterface::MESSAGE] = $message;
                $profile[ConstraintsInterface::STATUS] = ConstraintsInterface::STATUS_ERROR;
            }
            $this->processProfileManager->update($profile);
            break;
        }
        if(is_callable($callback)) {
            $callback();
        }
    }

    /**
     * @param string $identity
     * @return void
     */
    public function prepare(string $identity = ''): void
    {
        $lockId = 'prepare_process_runner';
        if($this->lockManager->isLocked($lockId)) return;

        $this->lockManager->lock($lockId, 500);

        $numbers = $this->processProfileManager->getNumberOfProcesses();
        $processes = [
            ...$this->processProfileManager->getList($identity, [ConstraintsInterface::STATUS_ERROR]),
            ...$this->processProfileManager->getList($identity)
        ];
        if($numbers < self::MAX_PROCESSES) {
            foreach ($processes as $profile) {
                $process = $this->processPool->getProcessByIdentity($profile['identity']);
                if ($process->isMultiProcess()) {
                    $maxCount = $process->getMultiProcessCount();
                    $count = $this->processProfileManager
                        ->getNumberOfProcessesByIdentity($profile['identity']);

                    if ($count >= $maxCount) continue;

                    $profile['status'] = ConstraintsInterface::STATUS_WAITING;
                    $this->processProfileManager->update($profile);
                    $numbers++;

                } else {
                    $count = $this->processProfileManager
                        ->getNumberOfProcessesByIdentity($profile['identity']);
                    if($count > 0) continue;
                    $profile['status'] = ConstraintsInterface::STATUS_WAITING;
                    $this->processProfileManager->update($profile);
                    $numbers++;
                }
                if ($numbers > self::MAX_PROCESSES) break;
            }
        }
        if ($numbers >= self::MAX_PROCESSES) {
            while (true) {
                sleep(30);
                if($this->processProfileManager->getNumberOfProcesses() < self::MAX_PROCESSES) break;

            }
        }
        $this->lockManager->unlock($lockId);
    }

    public function clear(string $identity = ''): void
    {
        $this->processProfileManager->clear();
    }

    /**
     * @return ShellInterface
     */
    protected function getShell(): ShellInterface
    {
        return $this->shell;
    }

    /**
     * @param string $model
     * @param string $arguments
     * @return array
     */
    private function _run(string $model, string $arguments = ''): array
    {
        $result = false;
        $message = '';
        try {
            $params = json_decode($arguments, true) ?: [];
        } catch (\Exception $e){
            $params = [];
        }

        try {
            $interfaces = class_implements($model);
            if(isset($interfaces[ModelInstanceInterface::class])) {
                $object = ObjectManager::getInstance()->create($model);
                $object->runProcess($params);
                $result = true;
                $message = '';
            }
        } catch (\Throwable $e) {
            $result = false;
            $message = $e->getMessage();
        }

        return [$result, $message];
    }

    private function _runWaiting($count)
    {
        $lockId = 'run_waiting_process';
        if($count && !$this->lockManager->isLocked($lockId)) {
            $this->lockManager->lock($lockId, $count * 30);

            for ($i = 0; $i < $count; $i++) {
                $this->runCommand(Run::COMMAND);
                sleep(15);
            }
            $self = $this;
            return function () use($self, $lockId) {
                $self->lockManager->unlock($lockId);
            };
        }
    }
}
