<?php

namespace Test\Crud;

use Simplon\Mysql\Crud\CrudStore;
use Simplon\Mysql\MysqlException;
use Simplon\Mysql\QueryBuilder\CreateQueryBuilder;
use Simplon\Mysql\QueryBuilder\DeleteQueryBuilder;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;
use Simplon\Mysql\QueryBuilder\UpdateQueryBuilder;

/**
 * @package Test\Crud
 */
class NamesStore extends CrudStore
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'names';
    }

    /**
     * @return NameModel
     */
    public function getModel(): NameModel
    {
        return new NameModel();
    }

    /**
     * @param CreateQueryBuilder $builder
     *
     * @return NameModel
     * @throws MysqlException
     */
    public function create(CreateQueryBuilder $builder): NameModel
    {
        /** @var NameModel $model */
        $model = $this->crudCreate($builder);

        return $model;
    }

    /**
     * @param ReadQueryBuilder|null $builder
     *
     * @return NameModel[]|null
     * @throws MysqlException
     */
    public function read(?ReadQueryBuilder $builder = null): ?array
    {
        /** @var NameModel[]|null $response */
        $response = $this->crudRead($builder);

        return $response;
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return null|NameModel
     * @throws MysqlException
     */
    public function readOne(ReadQueryBuilder $builder): ?NameModel
    {
        /** @var NameModel|null $response */
        $response = $this->crudReadOne($builder);

        return $response;
    }

    /**
     * @param UpdateQueryBuilder $builder
     *
     * @return NameModel
     * @throws MysqlException
     */
    public function update(UpdateQueryBuilder $builder): NameModel
    {
        /** @var NameModel|null $model */
        $model = $this->crudUpdate($builder);

        return $model;
    }

    /**
     * @param DeleteQueryBuilder $builder
     *
     * @return bool
     * @throws MysqlException
     */
    public function delete(DeleteQueryBuilder $builder): bool
    {
        return $this->crudDelete($builder);
    }

    /**
     * @param int $id
     *
     * @return null|NameModel
     * @throws MysqlException
     */
    public function customMethod(int $id): ?NameModel
    {
        $query = 'SELECT * FROM ' . $this->getTableName() . ' WHERE id=:id';

        if ($result = $this->getCrudManager()->getMysql()->fetchRow($query, ['id' => $id]))
        {
            return (new NameModel())->fromArray($result);
        }

        return null;
    }
}