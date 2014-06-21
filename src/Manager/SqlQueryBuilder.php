<?php

namespace Simplon\Mysql\Manager;

class SqlQueryBuilder
{
    /** @var string */
    protected $tableName;

    /** @var string */
    protected $query;

    /** @var bool */
    protected $enableInsertIgnore = false;

    /** @var array */
    protected $conditions = [];

    /** @var  string */
    protected $conditionsQuery;

    /** @var array */
    protected $data = [];

    /**
     * @param $query string
     *
     * @return SqlQueryBuilder
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return (string)$this->query;
    }

    /**
     * @param $conditions array
     *
     * @return SqlQueryBuilder
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return (array)$this->conditions;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    protected function removeCondition($key)
    {
        if (isset($this->conditions[$key]))
        {
            unset($this->conditions[$key]);

            return true;
        }

        return false;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return SqlQueryBuilder
     */
    protected function addCondition($key, $value)
    {
        if (!isset($this->conditions))
        {
            $this->conditions = [];
        }

        $this->conditions[$key] = $value;

        return $this;
    }

    /**
     * @param string $conditionsQuery
     *
     * @return SqlQueryBuilder
     */
    public function setConditionsQuery($conditionsQuery)
    {
        $this->conditionsQuery = $conditionsQuery;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getConditionsQuery()
    {
        if ($this->conditionsQuery)
        {
            return (string)$this->conditionsQuery;
        }

        return null;
    }

    /**
     * @param array $data
     *
     * @return SqlQueryBuilder
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return (array)$this->data;
    }

    /**
     * @return bool
     */
    public function hasMultiData()
    {
        return isset($this->data[0]) && is_array($this->data[0]);
    }

    /**
     * @param $tableName string
     *
     * @return SqlQueryBuilder
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return (string)$this->tableName;
    }

    /**
     * @param $insertIgnore
     *
     * @return SqlQueryBuilder
     */
    public function enableInsertIgnore($insertIgnore)
    {
        $this->enableInsertIgnore = $insertIgnore;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasInsertIgnore()
    {
        return $this->enableInsertIgnore !== false ? true : false;
    }
}
