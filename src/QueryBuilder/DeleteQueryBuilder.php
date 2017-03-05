<?php

namespace Simplon\Mysql\QueryBuilder;

use Simplon\Mysql\Crud\CrudModelInterface;

/**
 * @package Simplon\Mysql\QueryBuilder
 */
class DeleteQueryBuilder
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
     * @var array
     */
    protected $conds;
    /**
     * @var string
     */
    protected $condsQuery;

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

    /**
     * @return array
     */
    public function getConds(): array
    {
        return $this->conds;
    }

    /**
     * @param array $conds
     *
     * @return DeleteQueryBuilder
     */
    public function setConds(array $conds): self
    {
        $this->conds = $conds;

        return $this;
    }

    /**
     * @return string
     */
    public function getCondsQuery(): string
    {
        return $this->condsQuery;
    }

    /**
     * @param string $condsQuery
     *
     * @return DeleteQueryBuilder
     */
    public function setCondsQuery(string $condsQuery): self
    {
        $this->condsQuery = $condsQuery;

        return $this;
    }
}