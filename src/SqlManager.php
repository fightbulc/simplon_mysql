<?php

namespace Simplon\Mysql;

class SqlManager
{
    /** @var Mysql */
    protected $mysqlInstance;

    /**
     * @param Mysql $mysqlInstance
     */
    public function __construct(Mysql $mysqlInstance)
    {
        $this->mysqlInstance = $mysqlInstance;
    }

    /**
     * @return Mysql
     */
    protected function getMysqlInstance()
    {
        return $this->mysqlInstance;
    }

    /**
     * @return bool|int
     */
    public function getRowCount()
    {
        return $this
            ->getMysqlInstance()
            ->getRowCount();
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return bool
     */
    public function executeSql(SqlQueryBuilder $sqlBuilder)
    {
        return $this
            ->getMysqlInstance()
            ->executeSql($sqlBuilder->getQuery());
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return bool|string
     */
    public function fetchColumn(SqlQueryBuilder $sqlBuilder)
    {
        $result = $this
            ->getMysqlInstance()
            ->fetchColumn($sqlBuilder->getQuery(), $sqlBuilder->getConditions());

        if ($result !== null)
        {
            return (string)$result;
        }

        return false;
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return array|bool
     */
    public function fetchColumnMany(SqlQueryBuilder $sqlBuilder)
    {
        $result = $this
            ->getMysqlInstance()
            ->fetchColumnMany($sqlBuilder->getQuery(), $sqlBuilder->getConditions());

        if ($result !== null)
        {
            return (array)$result;
        }

        return false;
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return SqlQueryIterator
     */
    public function fetchColumnManyCursor(SqlQueryBuilder $sqlBuilder)
    {
        return $this
            ->getMysqlInstance()
            ->fetchColumnManyCursor($sqlBuilder->getQuery(), $sqlBuilder->getConditions());
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return array|bool
     */
    public function fetchRow(SqlQueryBuilder $sqlBuilder)
    {
        $result = $this
            ->getMysqlInstance()
            ->fetchRow($sqlBuilder->getQuery(), $sqlBuilder->getConditions());

        if ($result !== null)
        {
            return (array)$result;
        }

        return false;
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return array|bool
     */
    public function fetchRowMany(SqlQueryBuilder $sqlBuilder)
    {
        $result = $this
            ->getMysqlInstance()
            ->fetchRowMany($sqlBuilder->getQuery(), $sqlBuilder->getConditions());

        if ($result !== null)
        {
            return (array)$result;
        }

        return false;
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return SqlQueryIterator
     */
    public function fetchRowManyCursor(SqlQueryBuilder $sqlBuilder)
    {
        return $this
            ->getMysqlInstance()
            ->fetchRowManyCursor($sqlBuilder->getQuery(), $sqlBuilder->getConditions());
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return array|null
     */
    public function insert(SqlQueryBuilder $sqlBuilder)
    {
        if ($sqlBuilder->hasMultiData())
        {
            return $this->getMysqlInstance()
                ->insertMany(
                    $sqlBuilder->getTableName(),
                    $sqlBuilder->getData(),
                    $sqlBuilder->hasInsertIgnore()
                );
        }

        return $this->getMysqlInstance()
            ->insert(
                $sqlBuilder->getTableName(),
                $sqlBuilder->getData(),
                $sqlBuilder->hasInsertIgnore()
            );
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return array|null
     */
    public function replace(SqlQueryBuilder $sqlBuilder)
    {
        if ($sqlBuilder->hasMultiData())
        {
            return $this->getMysqlInstance()
                ->replaceMany(
                    $sqlBuilder->getTableName(),
                    $sqlBuilder->getData()
                );
        }

        return $this->getMysqlInstance()
            ->replace(
                $sqlBuilder->getTableName(),
                $sqlBuilder->getData()
            );
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return bool
     */
    public function update(SqlQueryBuilder $sqlBuilder)
    {
        return $this->getMysqlInstance()
            ->update(
                $sqlBuilder->getTableName(),
                $sqlBuilder->getConditions(),
                $sqlBuilder->getData(),
                $sqlBuilder->getConditionsQuery()
            );
    }

    /**
     * @param SqlQueryBuilder $sqlBuilder
     *
     * @return bool
     */
    public function delete(SqlQueryBuilder $sqlBuilder)
    {
        return $this->getMysqlInstance()
            ->delete(
                $sqlBuilder->getTableName(),
                $sqlBuilder->getConditions(),
                $sqlBuilder->getConditionsQuery()
            );
    }
}
