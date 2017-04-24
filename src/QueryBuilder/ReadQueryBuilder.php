<?php

namespace Simplon\Mysql\QueryBuilder;

/**
 * @package Simplon\Mysql\QueryBuilder
 */
class ReadQueryBuilder
{
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    /**
     * @var string
     */
    protected $from;
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
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @param string $alias
     *
     * @return ReadQueryBuilder
     */
    public function setFrom(string $from, ?string $alias = null): self
    {
        if ($alias !== null)
        {
            $from = $from . ' AS ' . $alias;
        }

        $this->from = $from;

        return $this;
    }

    /**
     * @deprecated Use getSelect
     *
     * @return string
     */
    public function getColumns(): string
    {
        return implode(', ', $this->getSelect());
    }

    /**
     * @deprecated Use setSelect or addSelect
     *
     * @param string $fields
     *
     * @return ReadQueryBuilder
     */
    public function setColumns(string $fields): self
    {
        return $this->setSelect([$fields]);
    }

    /**
     * @return array
     */
    public function getSelect(): array
    {
        return empty($this->select) ? ['*'] : $this->select;
    }

    /**
     * @param string $column
     *
     * @return ReadQueryBuilder
     */
    public function addSelect(string $column): self
    {
        $this->select[] = $column;

        return $this;
    }

    /**
     * @param array $select
     *
     * @return ReadQueryBuilder
     */
    public function setSelect(array $select): self
    {
        foreach ($select as $column)
        {
            $this->addSelect($column);
        }

        return $this;
    }

    /**
     * @param string $tableName
     * @param string $alias
     * @param string $condsQuery
     *
     * @return ReadQueryBuilder
     */
    public function addInnerJoin(string $tableName, string $alias, string $condsQuery): self
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
    public function addLeftJoin(string $tableName, string $alias, string $condsQuery): self
    {
        return $this->addJoin('LEFT', $tableName, $alias, $condsQuery);
    }

    /**
     * @return array|null
     */
    public function getJoins(): ?array
    {
        return $this->joins;
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
     * @return ReadQueryBuilder
     */
    public function addCondition(string $key, $val): self
    {
        $this->conditions[$key] = $val;

        return $this;
    }

    /**
     * @param array $conds
     *
     * @return ReadQueryBuilder
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
     * @return ReadQueryBuilder
     */
    public function setCondsQuery(string $condsQuery): self
    {
        $this->condsQuery = $condsQuery;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getSorting(): ?array
    {
        return $this->sorting;
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return ReadQueryBuilder
     */
    public function addSorting(string $field, string $direction): self
    {
        $field = strpos($field, '.') !== false ? $field : '`' . $field . '`';
        $this->sorting[] = $field . ' ' . $direction;

        return $this;
    }

    /**
     * @param array $sorting
     *
     * @return ReadQueryBuilder
     */
    public function setSorting(array $sorting): self
    {
        foreach ($sorting as $val)
        {
            list($field, $direction) = explode(' ', $val);
            $this->addSorting($field, $direction);
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getGroup(): ?array
    {
        return $this->group;
    }

    /**
     * @param string $column
     *
     * @return ReadQueryBuilder
     */
    public function addGroup(string $column): self
    {
        $this->group[] = strpos($column, '.') !== false ? $column : '`' . $column . '`';

        return $this;
    }

    /**
     * @param array $columns
     *
     * @return ReadQueryBuilder
     */
    public function setGroup(array $columns): self
    {
        foreach ($columns as $column)
        {
            $this->addGroup($column);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLimit(): ?string
    {
        return $this->limit;
    }

    /**
     * @param int $rows
     * @param int $offset
     *
     * @return ReadQueryBuilder
     */
    public function setLimit(int $rows, int $offset = 0): self
    {
        $this->limit = $offset . ', ' . $rows;

        return $this;
    }

    /**
     * @return string
     */
    public function renderQuery(): string
    {
        $query = ['SELECT', join(', ', $this->getSelect()), 'FROM ' . $this->getFrom()];

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
                    // handle db named columns e.g. "db.id"
                    $formattedKey = str_replace('.', '', $key);
                    $resetConds[$formattedKey] = $value;

                    // handle only columns (non-column conds are prepend with _)
                    if (substr($key, 0, 1) !== '_')
                    {
                        $key = strpos($key, '.') !== false ? $key : '`' . $key . '`';
                        $condQuery = $key . ' = :' . $formattedKey;

                        if (is_array($value))
                        {
                            $condQuery = $key . ' IN(:' . $formattedKey . ')';
                        }

                        $conds[] = $condQuery;
                    }
                }

                $this->setConditions($resetConds);
            }

            if (empty($conds) === false)
            {
                $query[] = 'WHERE ' . join(' AND ', $conds);
            }
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
    private function addJoin(string $type, string $tableName, string $alias, string $conds): self
    {
        if ($this->joins === null)
        {
            $this->joins = [];
        }

        $this->joins[] = $type . ' JOIN ' . $tableName . ' AS ' . $alias . ' ON ' . $conds;

        return $this;
    }
}