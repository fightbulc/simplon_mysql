<?php

namespace Simplon\Mysql\Crud;

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
}