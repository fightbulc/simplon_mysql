<?php

namespace Simplon\Mysql\QueryBuilder;

use Simplon\Mysql\Crud\CrudModelInterface;

/**
 * Class ReadQueryBuilder
 * @package Simplon\Mysql\QueryBuilder
 */
class ReadQueryBuilder
{
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $columns = '*';

    /**
     * @var array
     */
    protected $joins;

    /**
     * @var array
     */
    protected $conds;

    /**
     * @var string
     */
    protected $condsQuery;

    /**
     * @var array
     */
    protected $sorting;

    /**
     * @var string
     */
    protected $limit;

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @param string $alias
     *
     * @return ReadQueryBuilder
     */
    public function setTableName($tableName, $alias = null)
    {
        if ($alias !== null)
        {
            $tableName = $tableName . ' AS ' . $alias;
        }

        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $columns
     *
     * @return ReadQueryBuilder
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param string $tableName
     * @param string $alias
     * @param string $condsQuery
     *
     * @return ReadQueryBuilder
     */
    public function addInnerJoin($tableName, $alias, $condsQuery)
    {
        return $this->addJoin('INNER', $tableName, $alias, $condsQuery);
    }

    /**
     * @param string $tableName
     * @param string $alias
     * @param string $condsQuery
     *
     * @return ReadQueryBuilder
     */
    public function addLeftJoin($tableName, $alias, $condsQuery)
    {
        return $this->addJoin('LEFT', $tableName, $alias, $condsQuery);
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @return array
     */
    public function getConds()
    {
        return $this->conds;
    }

    /**
     * @param array $conds
     *
     * @return ReadQueryBuilder
     */
    public function setConds($conds)
    {
        $this->conds = $conds;

        return $this;
    }

    /**
     * @return string
     */
    public function getCondsQuery()
    {
        return $this->condsQuery;
    }

    /**
     * @param string $condsQuery
     *
     * @return ReadQueryBuilder
     */
    public function setCondsQuery($condsQuery)
    {
        $this->condsQuery = $condsQuery;

        return $this;
    }

    /**
     * @return array
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return ReadQueryBuilder
     */
    public function addSorting($field, $direction)
    {
        $this->sorting[] = $field . ' ' . $direction;

        return $this;
    }

    /**
     * @param array $sorting
     *
     * @return ReadQueryBuilder
     */
    public function setSorting(array $sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * @return string
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $rows
     * @param int $offset
     *
     * @return ReadQueryBuilder
     */
    public function setLimit($rows, $offset = 0)
    {
        $this->limit = $offset . ', ' . $rows;

        return $this;
    }

    /**
     * @param string $type
     * @param string $tableName
     * @param string $alias
     * @param string $conds
     *
     * @return ReadQueryBuilder
     */
    private function addJoin($type, $tableName, $alias, $conds)
    {
        if ($this->joins === null)
        {
            $this->joins = [];
        }

        $this->joins[] = $type . ' JOIN ' . $tableName . ' AS ' . $alias . ' ON ' . $conds;

        return $this;
    }
}