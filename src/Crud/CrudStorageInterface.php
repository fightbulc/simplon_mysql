<?php

namespace Simplon\Mysql\Crud;

use Simplon\Mysql\MysqlException;

/**
 * Interface CrudStorageInterface
 * @package Simplon\Mysql\Crud
 */
interface CrudStorageInterface
{
    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return CrudModelInterface
     */
    public function getModel();

    /**
     * @param CrudModelInterface $model
     *
     * @return CrudModelInterface
     */
    public function create(CrudModelInterface $model);

    /**
     * @param array $conds
     *
     * @return null|CrudModelInterface[]
     */
    public function read(array $conds);

    /**
     * @param array $conds
     *
     * @return null|CrudModelInterface
     */
    public function readOne(array $conds);

    /**
     * @param CrudModelInterface $model
     *
     * @return CrudModelInterface
     * @throws MysqlException
     */
    public function update(CrudModelInterface $model);

    /**
     * @param array $conds
     *
     * @throws MysqlException
     */
    public function delete(array $conds);
}
