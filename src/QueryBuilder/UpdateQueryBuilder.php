<?php

namespace Simplon\Mysql\QueryBuilder;

use Simplon\Mysql\Crud\CrudModelInterface;

/**
 * @package Simplon\Mysql\QueryBuilder
 */
class UpdateQueryBuilder
{
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
     * @var null|array
     */
    protected $conditions;
    /**
     * @var string
     */
    protected $condsQuery;
    /**
     * @var array
     */
    protected $data;

    /**
     * @return CrudModelInterface
     */
    public function getModel(): CrudModelInterface
    {
        return $this->model;
    }

    /**
     * @param CrudModelInterface $model
     *
     * @return UpdateQueryBuilder
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
     * @return UpdateQueryBuilder
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
     * @return UpdateQueryBuilder
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    /**
     * @param string $key
     * @param mixed $val
     *
     * @return UpdateQueryBuilder
     */
    public function addCondition(string $key, $val): self
    {
        $this->conditions[$key] = $val;

        return $this;
    }

    /**
     * @param array $conds
     *
     * @return UpdateQueryBuilder
     */
    public function setConditions(array $conds): self
    {
        $this->conditions = $conds;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCondsQuery(): ?string
    {
        return $this->condsQuery;
    }

    /**
     * @param string $condsQuery
     *
     * @return UpdateQueryBuilder
     */
    public function setCondsQuery(string $condsQuery): self
    {
        $this->condsQuery = $condsQuery;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if ($this->getModel() instanceof CrudModelInterface)
        {
            return $this->getModel()->toArray();
        }

        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return UpdateQueryBuilder
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}