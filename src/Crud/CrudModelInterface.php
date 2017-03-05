<?php

namespace Simplon\Mysql\Crud;

/**
 * @package Simplon\Mysql\Crud
 */
interface CrudModelInterface
{
    /**
     * @return static
     */
    public function beforeSave();

    /**
     * @return static
     */
    public function beforeUpdate();

    /**
     * @param array $data
     *
     * @return static
     */
    public function fromArray(array $data);

    /**
     * @return array
     */
    public function toArray(): array;
}