<?php

namespace Simplon\Mysql\Manager;

use Simplon\Mysql\Mysql;
use Simplon\Mysql\MysqlQueryIterator;

/**
 * Class SqlManager
 * @package Simplon\Mysql\Manager
 */
class SqlManager
{
    /**
     * @var Mysql
     */
    protected $mysql;

    /**
     * @param Mysql $mysqlInstance
     */
    public function __construct(Mysql $mysqlInstance)
    {
        $this->mysql = $mysqlInstance;
    }

    /**
     * @return bool|int
     */
    public function getRowCount()
    {
        return $this->getMysql()->getRowCount();
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return bool
     */
    public function executeSql(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->executeSql($builder->getQuery());
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return null|string
     */
    public function fetchColumn(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->fetchColumn(
            $builder->getQuery(),
            $builder->getConditions()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return array|null
     */
    public function fetchColumnMany(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->fetchColumnMany(
            $builder->getQuery(),
            $builder->getConditions()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return null|MysqlQueryIterator
     */
    public function fetchColumnManyCursor(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->fetchColumnManyCursor(
            $builder->getQuery(),
            $builder->getConditions()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return array|null
     */
    public function fetchRow(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->fetchRow(
            $builder->getQuery(),
            $builder->getConditions()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return array|null
     */
    public function fetchRowMany(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->fetchRowMany(
            $builder->getQuery(),
            $builder->getConditions()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return null|MysqlQueryIterator
     */
    public function fetchRowManyCursor(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->fetchRowManyCursor(
            $builder->getQuery(),
            $builder->getConditions()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return array|bool
     */
    public function insert(SqlQueryBuilder $builder)
    {
        if ($builder->hasMultiData())
        {
            return $this->getMysql()->insertMany(
                $builder->getTableName(),
                $builder->getData(),
                $builder->hasInsertIgnore()
            );
        }

        return $this->getMysql()->insert(
            $builder->getTableName(),
            $builder->getData(),
            $builder->hasInsertIgnore()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return array|bool
     */
    public function replace(SqlQueryBuilder $builder)
    {
        if ($builder->hasMultiData())
        {
            return $this->getMysql()->replaceMany(
                $builder->getTableName(),
                $builder->getData()
            );
        }

        return $this->getMysql()->replace(
            $builder->getTableName(),
            $builder->getData()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return bool
     */
    public function update(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->update(
            $builder->getTableName(),
            $builder->getConditions(),
            $builder->getData(),
            $builder->getConditionsQuery()
        );
    }

    /**
     * @param SqlQueryBuilder $builder
     *
     * @return bool
     */
    public function delete(SqlQueryBuilder $builder)
    {
        return $this->getMysql()->delete(
            $builder->getTableName(),
            $builder->getConditions(),
            $builder->getConditionsQuery()
        );
    }

    /**
     * @return Mysql
     */
    private function getMysql()
    {
        return $this->mysql;
    }
}
