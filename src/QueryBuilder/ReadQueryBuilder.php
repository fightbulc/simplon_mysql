<?php

namespace Simplon\Mysql\QueryBuilder;

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
     * @var array
     */
    protected $select = [];

    /**
     * @var array
     */
    protected $joins;

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var string
     */
    protected $condsQuery;

    /**
     * @var array
     */
    protected $sorting;

    /**
     * @var array
     */
    protected $group;

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
     * @deprecated Use getSelect
     *
     * @return string
     */
    public function getColumns()
    {
        return join(', ', $this->getSelect());
    }

    /**
     * @deprecated Use setSelect or addSelect
     *
     * @param string $fields
     *
     * @return ReadQueryBuilder
     */
    public function setColumns($fields)
    {
        return $this->setSelect([$fields]);
    }

    /**
     * @return string
     */
    public function getSelect()
    {
        return empty($this->select) ? ['*'] : $this->select;
    }

    /**
     * @param string $select
     *
     * @return ReadQueryBuilder
     */
    public function addSelect($select)
    {
        $this->select[] = $select;

        return $this;
    }

    /**
     * @param array $select
     *
     * @return ReadQueryBuilder
     */
    public function setSelect(array $select)
    {
        $this->select = $select;

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
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param string $key
     * @param mixed $val
     *
     * @return ReadQueryBuilder
     */
    public function addCondition($key, $val)
    {
        $this->conditions[$key] = $val;

        return $this;
    }

    /**
     * @param array $conds
     *
     * @return ReadQueryBuilder
     */
    public function setConditions($conds)
    {
        $this->conditions = $conds;

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
     * @return array
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $column
     *
     * @return ReadQueryBuilder
     */
    public function addGroup($column)
    {
        $this->group[] = $column;

        return $this;
    }

    /**
     * @param array $columns
     *
     * @return ReadQueryBuilder
     */
    public function setGroup(array $columns)
    {
        $this->group = $columns;

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
     * @return string
     */
    public function renderQuery()
    {
        $query = ['SELECT', join(', ', $this->getSelect()), 'FROM ' . $this->getTableName()];

        if ($this->getJoins())
        {
            $query = array_merge($query, $this->getJoins());
        }

        if ($this->getConditions() || $this->getCondsQuery())
        {
            $conds = [];

            if ($this->getCondsQuery())
            {
                $conds[] = $this->getCondsQuery();
            }
            else
            {
                $resetConds = [];

                foreach ($this->getConditions() as $key => $value)
                {
                    $formattedKey = str_replace('.', '', $key);
                    $resetConds[$formattedKey] = $value;
                    $conds[] = $key . ' = :' . $formattedKey;
                }

                $this->setConditions($resetConds);
            }

            $query[] = 'WHERE ' . join(' AND ', $conds);
        }

        if ($this->getGroup())
        {
            $query[] = 'GROUP BY ' . join(', ', $this->getGroup());
        }

        if ($this->getSorting())
        {
            $query[] = 'ORDER BY ' . join(', ', $this->getSorting());
        }

        if ($this->getLimit())
        {
            $query[] = 'LIMIT ' . $this->getLimit();
        }

        return join(' ', $query);
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