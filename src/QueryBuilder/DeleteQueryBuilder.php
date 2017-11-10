<?php

namespace Simplon\Mysql\QueryBuilder;

use Simplon\Mysql\Crud\CrudModelInterface;
use Simplon\Mysql\Utils\ConditionsTrait;

class DeleteQueryBuilder
{
    use ConditionsTrait;

    /**
     * @var CrudModelInterface
     */
    protected $model;
    /**
     * @var string
     */
    protected $tableName;
    /**
     * @var string
     */
    protected $query;

    /**
     * @return null|CrudModelInterface
     */
    public function getModel(): ?CrudModelInterface
    {
        return $this->model;
    }

    /**
     * @param CrudModelInterface $model
     *
     * @return DeleteQueryBuilder
     */
    public function setModel(CrudModelInterface $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     *
     * @return DeleteQueryBuilder
     */
    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     *
     * @return DeleteQueryBuilder
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }
}