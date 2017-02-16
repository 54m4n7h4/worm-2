<?php
declare(strict_types=1);

namespace WoohooLabs\Worm\Model\Relationship;

use WoohooLabs\Larva\Query\Condition\ConditionBuilder;
use WoohooLabs\Larva\Query\Select\SelectQueryBuilder;
use WoohooLabs\Larva\Query\Select\SelectQueryBuilderInterface;
use WoohooLabs\Worm\Execution\IdentityMap;
use WoohooLabs\Worm\Model\ModelInterface;

class BelongsToOneRelationship extends AbstractRelationship
{
    /**
     * @var ModelInterface
     */
    protected $relatedModel;

    /**
     * @var string
     */
    protected $foreignKey;

    /**
     * @var string
     */
    protected $referencedKey;

    public function __construct(
        ModelInterface $model,
        ModelInterface $relatedModel,
        string $foreignKey,
        string $referencedKey
    ) {
        parent::__construct($model);
        $this->relatedModel = $relatedModel;
        $this->foreignKey = $foreignKey;
        $this->referencedKey = $referencedKey;
    }

    public function getModel(): ModelInterface
    {
        return $this->relatedModel;
    }

    public function getQueryBuilder(array $entities): SelectQueryBuilderInterface
    {
        return SelectQueryBuilder::create()
            ->selectColumn("*", $this->relatedModel->getTable())
            ->from($this->relatedModel->getTable())
            ->join($this->parentModel->getTable())
            ->on(
                ConditionBuilder::create()
                    ->columnToColumn(
                        $this->foreignKey,
                        "=",
                        $this->referencedKey,
                        $this->parentModel->getTable(),
                        $this->relatedModel->getTable()
                    )
            )
            ->where($this->getWhereCondition($this->relatedModel->getTable(), $this->foreignKey, $entities));
    }

    public function matchRelationship(
        array $entities,
        string $relationshipName,
        array $relatedEntities,
        IdentityMap $identityMap
    ): array {
        return $this->insertOneRelationship(
            $entities,
            $relationshipName,
            $this->relatedModel,
            $relatedEntities,
            $this->referencedKey,
            $this->foreignKey,
            $identityMap
        );
    }

    public function getRelatedModel(): ModelInterface
    {
        return $this->relatedModel;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getReferencedKey(): string
    {
        return $this->referencedKey;
    }
}
