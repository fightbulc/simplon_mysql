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
                $builder->renderQuery(),
                $this->removeNullValuesFromConds($builder->getConditions())
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
                $builder->renderQuery(),
                $this->removeNullValuesFromConds($builder->getConditions())
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

        $conds = [];
        $condsQuery = null;

        if ($builder->getConds())
        {
            $condsQuery = $this->buildCondsQuery($builder->getConds(), $builder->getCondsQuery());
            $conds = $this->removeNullValuesFromConds($builder->getConds());
        }

        $this->getMysql()->update(
            $builder->getTableName(),
            $conds,
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
        $conds = [];
        $condsQuery = null;

        if ($builder->getConds())
        {
            $condsQuery = $this->buildCondsQuery($builder->getConds(), $builder->getCondsQuery());
            $conds = $this->removeNullValuesFromConds($builder->getConds());
        }

        $this->getMysql()->delete(
            $builder->getTableName(),
            $conds,
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

            if ($val === null)
            {
                $query = $key . ' IS NULL';
            }

            if (is_array($val) === true)
            {
                $query = $key . ' IN (:' . $key . ')';
            }

            $condsString[] = $query;
        }

        return join(' AND ', $condsString);
    }

    /**
     * @param array $conds
     *
     * @return array
     */
    private function removeNullValuesFromConds(array $conds)
    {
        $new = [];

        foreach ($conds as $key => $val)
        {
            if ($val !== null)
            {
                $new[$key] = $val;
            }
        }

        return $new;
    }
}