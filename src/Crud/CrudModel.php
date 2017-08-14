<?php

namespace Simplon\Mysql\Crud;

use Simplon\Helper\Data\Data;

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