<?php

namespace Test\Crud;

use Simplon\Mysql\Crud\CrudModelInterface;
use Simplon\Mysql\Crud\CrudManager;
use Simplon\Mysql\Crud\CrudStorageInterface;
use Simplon\Mysql\MysqlException;
use Simplon\Mysql\QueryBuilder\CreateQueryBuilder;
use Simplon\Mysql\QueryBuilder\DeleteQueryBuilder;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;
use Simplon\Mysql\QueryBuilder\UpdateQueryBuilder;

/**
 * Class SampleStorage
 * @package Test\Crud
 */
class SampleStorage implements CrudStorageInterface
{
    /**
     * @var CrudManager
     */
    private $crudStorage;

    /**
     * @param CrudManager $crudStorage
     */
    public function __construct(CrudManager $crudStorage)
    {
        $this->crudStorage = $crudStorage;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'users_user';
    }

    /**
     * @return SampleModel
     */
    public function getModel()
    {
        return new SampleModel();
    }

    /**
     * @param CrudModelInterface $model
     *
     * @return SampleModel
     * @throws MysqlException
     */
    public function create(CrudModelInterface $model)
    {
        return $this->crudStorage->create(
            (new CreateQueryBuilder())
                ->setModel($model)
                ->setTableName($this->getTableName())
        );
    }

    /**
     * @param array $conds
     *
     * @return null|SampleModel[]
     */
    public function read(array $conds)
    {
        $rows = $this->crudStorage->read(
            (new ReadQueryBuilder())
                ->setTableName($this->getTableName())
                ->setConds($conds)
        );

        if ($rows === null)
        {
            return null;
        }

        $models = [];

        foreach ($rows as $data)
        {
            $models[] = $this->getModel()->fromArray($data);
        }

        return $models;
    }

    /**
     * @param array $conds
     *
     * @return null|SampleModel
     */
    public function readOne(array $conds)
    {
        $row = $this->crudStorage->readOne(
            (new ReadQueryBuilder())
                ->setTableName($this->getTableName())
                ->setConds($conds)
        );

        if ($row === null)
        {
            return null;
        }

        return $this->getModel()->fromArray($row);
    }

    /**
     * @param CrudModelInterface $model
     *
     * @return SampleModel
     * @throws MysqlException
     */
    public function update(CrudModelInterface $model)
    {
        return $this->crudStorage->update(
            (new UpdateQueryBuilder())
                ->setModel($model)
                ->setTableName($this->getTableName())
                ->setConds(['id' => $model->getId()])
        );
    }

    /**
     * @param array $conds
     */
    public function delete(array $conds)
    {
        $this->crudStorage->delete(
            (new DeleteQueryBuilder())
                ->setTableName($this->getTableName())
                ->setConds($conds)
        );
    }
}