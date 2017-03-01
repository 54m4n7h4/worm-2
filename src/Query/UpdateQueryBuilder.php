<?php
declare(strict_types=1);

namespace WoohooLabs\Worm\Query;

use WoohooLabs\Larva\Query\Condition\ConditionBuilder;
use WoohooLabs\Larva\Query\Condition\ConditionBuilderInterface;
use WoohooLabs\Larva\Query\Update\UpdateQueryBuilder as LarvaUpdateQueryBuilder;
use WoohooLabs\Larva\Query\Update\UpdateQueryBuilderInterface;
use WoohooLabs\Worm\Execution\QueryExecutor;
use WoohooLabs\Worm\Model\ModelInterface;

class UpdateQueryBuilder
{
    /**
     * @var ModelInterface
     */
    private $model;

    /**
     * @var QueryExecutor
     */
    private $queryExecutor;

    /**
     * @var LarvaUpdateQueryBuilder
     */
    private $queryBuilder;

    public function __construct(ModelInterface $model, QueryExecutor $executor)
    {
        $this->model = $model;
        $this->queryExecutor = $executor;
        $this->queryBuilder = new LarvaUpdateQueryBuilder();
        $this->queryBuilder->table($model->getTable());
    }

    public function setFields(array $fields): UpdateQueryBuilder
    {
        $this->queryBuilder->setValues($fields);

        return $this;
    }

    public function where(ConditionBuilderInterface $where): UpdateQueryBuilder
    {
        $this->queryBuilder->where($where);

        return $this;
    }

    public function addWhereGroup(ConditionBuilderInterface $where): UpdateQueryBuilder
    {
        $this->queryBuilder->addWhereGroup($where);

        return $this;
    }

    public function getQueryBuilder(): UpdateQueryBuilderInterface
    {
        return $this->queryBuilder;
    }

    public function execute(): bool
    {
        return $this->queryExecutor->update($this->queryBuilder);
    }

    /**
     * @param mixed $id
     * @return bool
     */
    public function updateById($id): bool
    {
        $this->queryBuilder
            ->addWhereGroup(
                ConditionBuilder::create()
                    ->columnToValue($this->model->getPrimaryKey(), "=", $id)
            );

        return $this->queryExecutor->update($this->queryBuilder);
    }
}