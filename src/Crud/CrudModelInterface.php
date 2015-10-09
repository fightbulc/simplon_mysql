<?php

namespace Simplon\Mysql\Crud;

/**
 * Interface CrudModelInterface
 * @package Simplon\Mysql\Crud
 */
interface CrudModelInterface
{
    /**
     * @return CrudModelInterface
     */
    public function beforeSave();

    /**
     * @return CrudModelInterface
     */
    public function beforeUpdate();

    /**
     * @param array $data
     *
     * @return CrudModelInterface
     */
    public function fromArray(array $data);

    /**
     * @return array
     */
    public function toArray();
}