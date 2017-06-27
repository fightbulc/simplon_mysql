<?php

namespace Simplon\Mysql\Crud;

use Simplon\Helper\Data\Data;

/**
 * @package Simplon\Mysql\Crud
 */
abstract class CrudModel extends Data implements CrudModelInterface
{
    /**
     * @return static
     */
    public function beforeSave()
    {
        return $this;
    }

    /**
     * @return static
     */
    public function beforeUpdate()
    {
        return $this;
    }
}