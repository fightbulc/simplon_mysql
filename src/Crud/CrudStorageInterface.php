<?php

namespace Simplon\Mysql\Crud;

/**
 * Interface CrudStorageInterface
 * @package Simplon\Mysql\Crud
 */
interface CrudStorageInterface
{
    /**
     * @param CrudManager $crudManager
     */
    public function __construct(CrudManager $crudManager);

    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return CrudModelInterface
     */
    public function getModel();
}
