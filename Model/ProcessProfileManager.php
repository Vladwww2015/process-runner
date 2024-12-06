<?php

namespace IntegrationHelper\ProcessRunner\Model;

use IntegrationHelper\ProcessRunner\Api\ConstraintsInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\InvalidArgumentException;

class ProcessProfileManager
{
    public function __construct(
        protected ResourceConnection $resourceConnection
    ){}

    public function getList(string $identity = '', array $statusCondition = null): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(ConstraintsInterface::TABLE)
            ->order(ConstraintsInterface::CREATED_AT);

        if($statusCondition) {
            $select->where(sprintf('%s IN(?)', ConstraintsInterface::STATUS), $statusCondition);
        } else if($statusCondition === null) {
            $select->where(sprintf('%s IS NULL', ConstraintsInterface::STATUS));
        }

        if($identity) {
            $select->where(sprintf('%s=?', ConstraintsInterface::IDENTITY), $identity);
        }

        return $connection->fetchAssoc($select);
    }

    public function getNumberOfProcessesByIdentity(string $identity)
    {
        return count($this->getList(
                $identity,
                [ConstraintsInterface::STATUS_PROCESSING, ConstraintsInterface::STATUS_WAITING]
            ));
    }

    public function getNumberOfProcesses()
    {
        return count(
            $this->getList('',
                [ConstraintsInterface::STATUS_PROCESSING, ConstraintsInterface::STATUS_WAITING]
            ));
    }

    public function create(array $data = [])
    {
        $this->validate($data);
        if(!array_key_exists(ConstraintsInterface::STATUS, $data)) {
            $data[ConstraintsInterface::STATUS] = null;
        }
        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate(ConstraintsInterface::TABLE, $data);
    }

    public function update(array $data = [])
    {
        $this->validate($data);
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            ConstraintsInterface::TABLE,
            $data,
            sprintf('`%s`=%s', ConstraintsInterface::CODE, $connection->quote($data[ConstraintsInterface::CODE]))
        );
    }

    public function clear()
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(ConstraintsInterface::TABLE)
            ->where(sprintf('`%s`=?', ConstraintsInterface::STATUS), ConstraintsInterface::STATUS_COMPLETE);
        $sql = $connection->deleteFromSelect($select, ConstraintsInterface::TABLE);
        $connection->query($sql);
    }

    public function delete(array $data = [])
    {
        $this->validate($data);
        $connection = $this->resourceConnection->getConnection();
        $connection->delete(ConstraintsInterface::TABLE, [ConstraintsInterface::CODE => $data[ConstraintsInterface::CODE]]);
    }

    private function validate(array $data = [])
    {
        foreach ([
            ConstraintsInterface::IDENTITY,
            ConstraintsInterface::MODEL,
            ConstraintsInterface::CODE,
                 ] as $key) {
            if(!array_key_exists($key, $data)) throw new InvalidArgumentException(sprintf('Argument %s doesn\'t exists', $key));
        }
    }
}
