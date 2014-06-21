<?php

namespace Simplon\Mysql\Crud;

use Simplon\Mysql\Mysql;
use Simplon\Mysql\MysqlException;

class SqlCrudManager
{
    /** @var \Simplon\Mysql\Mysql */
    protected $mysql;

    /**
     * @param Mysql $mysql
     */
    public function __construct(Mysql $mysql)
    {
        $this->mysql = $mysql;
    }

    /**
     * @return Mysql
     */
    protected function getMysql()
    {
        return $this->mysql;
    }

    /**
     * @param array $conds
     * @param null $condsQuery
     *
     * @return string
     */
    protected function getCondsQuery(array $conds, $condsQuery = null)
    {
        if ($condsQuery !== null)
        {
            return (string)$condsQuery;
        }

        $condsString = [];

        foreach ($conds as $key => $val)
        {
            $condsString[] = $key . ' = :' . $key;
        }

        return join(' AND ', $condsString);
    }

    /**
     * @param SqlCrudInterface $sqlCrudInterface
     *
     * @return array
     */
    protected function getData(SqlCrudInterface &$sqlCrudInterface)
    {
        $data = [];

        foreach ($sqlCrudInterface->crudColumns() as $variable => $column)
        {
            $methodName = 'get' . ucfirst($variable);
            $data[$column] = $sqlCrudInterface->$methodName();
        }

        return $data;
    }

    /**
     * @param SqlCrudInterface $sqlCrudInterface
     * @param array $data
     *
     * @return SqlCrudInterface
     */
    protected function setData(SqlCrudInterface &$sqlCrudInterface, array $data)
    {
        $columns = array_flip($sqlCrudInterface->crudColumns());

        foreach ($data as $column => $value)
        {
            if (isset($columns[$column]))
            {
                $methodName = 'set' . ucfirst($columns[$column]);
                $sqlCrudInterface->$methodName($value);
            }
        }

        return $sqlCrudInterface;
    }

    /**
     * @param SqlCrudInterface $sqlCrudInterface
     * @param bool $insertIgnore
     *
     * @return bool|SqlCrudInterface
     * @throws MysqlException
     */
    public function create(SqlCrudInterface $sqlCrudInterface, $insertIgnore = false)
    {
        // do something before we save
        $sqlCrudInterface->crudBeforeSave(true);

        // save to db
        $insertId = $this->getMysql()->insert(
            $sqlCrudInterface->crudGetSource(),
            $this->getData($sqlCrudInterface),
            $insertIgnore
        );

        if ($insertId !== false)
        {
            // set id
            if (is_bool($insertId) !== true)
            {
                $sqlCrudInterface->setId($insertId);
            }

            // do something after we saved
            $sqlCrudInterface->crudAfterSave(true);

            return $sqlCrudInterface;
        }

        return false;
    }

    /**
     * @param SqlCrudInterface $sqlCrudInterface
     * @param array $conds
     * @param null $condsQuery
     *
     * @return bool|SqlCrudInterface
     */
    public function read(SqlCrudInterface $sqlCrudInterface, array $conds, $condsQuery = null)
    {
        // handle custom query
        $query = $sqlCrudInterface->crudGetQuery();

        // fallback to standard query
        if ($query === '')
        {
            $query = 'SELECT * FROM ' . $sqlCrudInterface::crudGetSource() . ' WHERE ' . $this->getCondsQuery($conds, $condsQuery);
        }

        // fetch data
        $data = $this->getMysql()->fetchRow($query, $conds);

        if ($data !== false)
        {
            return $this->setData($sqlCrudInterface, $data);
        }

        return false;
    }

    /**
     * @param SqlCrudInterface $sqlCrudInterface
     * @param array $conds
     * @param null $condsQuery
     *
     * @return bool|SqlCrudInterface
     * @throws \Simplon\Mysql\MysqlException
     */
    public function update(SqlCrudInterface $sqlCrudInterface, array $conds, $condsQuery = null)
    {
        // do something before we save
        $sqlCrudInterface->crudBeforeSave(false);

        $response = $this->getMysql()->update(
            $sqlCrudInterface::crudGetSource(),
            $conds,
            $this->getData($sqlCrudInterface),
            $this->getCondsQuery($conds, $condsQuery)
        );

        if ($response !== false)
        {
            // do something after update
            $sqlCrudInterface->crudAfterSave(false);

            return $sqlCrudInterface;
        }

        return false;
    }

    /**
     * @param $crudSource
     * @param array $conds
     * @param null $condsQuery
     *
     * @return bool
     */
    public function delete($crudSource, array $conds, $condsQuery = null)
    {
        return $this->getMysql()->delete(
            $crudSource,
            $conds,
            $this->getCondsQuery($conds, $condsQuery)
        );
    }
} 