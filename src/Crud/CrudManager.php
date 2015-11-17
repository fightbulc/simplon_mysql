<?php

namespace Simplon\Mysql\Crud;

use Simplon\Mysql\Mysql;
use Simplon\Mysql\MysqlException;
use Simplon\Mysql\MysqlQueryIterator;
use Simplon\Mysql\QueryBuilder\CreateQueryBuilder;
use Simplon\Mysql\QueryBuilder\DeleteQueryBuilder;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;
use Simplon\Mysql\QueryBuilder\UpdateQueryBuilder;

/**
 * Class CrudManager
 * @package Simplon\Mysql\Crud
 */
class CrudManager
{
    /**
     * @var Mysql
     */
    protected $mysql;

    /**
     * @param Mysql $mysql
     */
    public function __construct(Mysql $mysql)
    {
        $this->mysql = $mysql;
    }

    /**
     * @param CreateQueryBuilder $builder
     *
     * @return CrudModelInterface
     * @throws MysqlException
     */
    public function create(CreateQueryBuilder $builder)
    {
        $builder->getModel()->beforeSave();

        $insertId = $this->getMysql()->insert(
            $builder->getTableName(),
            $builder->getData(),
            $builder->getInsertIgnore()
        );

        if ($insertId === false)
        {
            throw new MysqlException('Could not create dataset');
        }

        if (is_bool($insertId) !== true && method_exists($builder->getModel(), 'setId'))
        {
            $builder->getModel()->setId($insertId);
        }

        return $builder->getModel();
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return MysqlQueryIterator|null
     */
    public function read(ReadQueryBuilder $builder)
    {
        return $this
            ->getMysql()
            ->fetchRowManyCursor(
                $this->buildReadQuery($builder),
                $builder->getConds()
            );
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return array|null
     */
    public function readOne(ReadQueryBuilder $builder)
    {
        return $this
            ->getMysql()
            ->fetchRow(
                $this->buildReadQuery($builder),
                $builder->getConds()
            );
    }

    /**
     * @param UpdateQueryBuilder $builder
     *
     * @return CrudModelInterface
     * @throws MysqlException
     */
    public function update(UpdateQueryBuilder $builder)
    {
        $builder->getModel()->beforeUpdate();

        $condsQuery = null;

        if ($builder->getConds())
        {
            $condsQuery = $this->buildCondsQuery($builder->getConds(), $builder->getCondsQuery());
        }

        $this->getMysql()->update(
            $builder->getTableName(),
            $builder->getConds(),
            $builder->getData(),
            $condsQuery
        );

        return $builder->getModel();
    }

    /**
     * @param DeleteQueryBuilder $builder
     *
     * @throws MysqlException
     */
    public function delete(DeleteQueryBuilder $builder)
    {
        $condsQuery = null;

        if ($builder->getConds())
        {
            $condsQuery = $this->buildCondsQuery($builder->getConds(), $builder->getCondsQuery());
        }

        $this->getMysql()->delete(
            $builder->getTableName(),
            $builder->getConds(),
            $condsQuery
        );
    }

    /**
     * @return Mysql
     */
    private function getMysql()
    {
        return $this->mysql;
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return string
     */
    private function buildReadQuery(ReadQueryBuilder $builder)
    {
        /** @noinspection SqlNoDataSourceInspection */
        $query = "SELECT {$builder->getColumns()} FROM {$builder->getTableName()}";

        if ($builder->getJoins())
        {
            $query .= " " . join("\n", $builder->getJoins());
        }

        if ($builder->getConds())
        {
            $query .= " WHERE {$this->buildCondsQuery($builder->getConds(), $builder->getCondsQuery())}";
        }

        if ($builder->getSorting())
        {
            $sorting = join(', ', $builder->getSorting());
            $query .= " ORDER BY {$sorting}";
        }

        return $query;
    }

    /**
     * @param array $conds
     * @param string $condsQuery
     *
     * @return string
     */
    private function buildCondsQuery(array $conds, $condsQuery = null)
    {
        if ($condsQuery !== null)
        {
            return (string)$condsQuery;
        }

        $condsString = [];

        foreach ($conds as $key => $val)
        {
            $query = $key . ' = :' . $key;

            if (is_array($val) === true)
            {
                $query = $key . ' IN (:' . $key . ')';
            }

            $condsString[] = $query;
        }

        return join(' AND ', $condsString);
    }
}