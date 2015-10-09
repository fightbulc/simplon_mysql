<?php

namespace Simplon\Mysql\QueryBuilder;

use Simplon\Mysql\Crud\CrudModelInterface;

/**
 * Class CreateQueryBuilder
 * @package Simplon\Mysql\QueryBuilder
 */
class CreateQueryBuilder
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
     * @var bool
     */
    protected $insertIgnore;

    /**
     * @var array
     */
    protected $data;

    /**
     * @return CrudModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param CrudModelInterface $model
     *
     * @return CreateQueryBuilder
     */
    public function setModel(CrudModelInterface $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     *
     * @return CreateQueryBuilder
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getInsertIgnore()
    {
        return $this->insertIgnore;
    }

    /**
     * @param boolean $insertIgnore
     *
     * @return CreateQueryBuilder
     */
    public function setInsertIgnore($insertIgnore)
    {
        $this->insertIgnore = $insertIgnore;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($this->getModel() instanceof CrudModelInterface)
        {
            return $this->getModel()->toArray();
        }

        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return CreateQueryBuilder
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }
}