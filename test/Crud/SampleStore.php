<?php

namespace Test\Crud;

use Simplon\Mysql\Crud\CrudModelInterface;
use Simplon\Mysql\Crud\CrudStore;
use Simplon\Mysql\MysqlException;
use Simplon\Mysql\QueryBuilder\CreateQueryBuilder;
use Simplon\Mysql\QueryBuilder\DeleteQueryBuilder;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;
use Simplon\Mysql\QueryBuilder\UpdateQueryBuilder;

/**
 * @package Test\Crud
 */
class SampleStore extends CrudStore
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'users_user';
    }

    /**
     * @return CrudModelInterface
     */
    public function getModel(): CrudModelInterface
    {
        return new SampleModel();
    }

    /**
     * @param CreateQueryBuilder $builder
     *
     * @return SampleModel
     * @throws MysqlException
     */
    public function create(CreateQueryBuilder $builder): SampleModel
    {
        /** @var SampleModel $model */
        $model = $this->crudCreate($builder);

        return $model;
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return SampleModel[]|null
     * @throws MysqlException
     */
    public function read(ReadQueryBuilder $builder): ?array
    {
        /** @var SampleModel[]|null $response */
        $response = $this->crudRead($builder);

        return $response;
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return null|SampleModel
     * @throws MysqlException
     */
    public function readOne(ReadQueryBuilder $builder): ?SampleModel
    {
        /** @var SampleModel|null $response */
        $response = $this->crudReadOne($builder);

        return $response;
    }

    /**
     * @param UpdateQueryBuilder $builder
     *
     * @return SampleModel
     * @throws MysqlException
     */
    public function update(UpdateQueryBuilder $builder): SampleModel
    {
        /** @var SampleModel|null $model */
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
}