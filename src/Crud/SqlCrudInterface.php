<?php

namespace Simplon\Mysql\Crud;

/**
 * Interface SqlCrudInterface
 * @package Simplon\Mysql\Crud
 * @author Tino Ehrich (tino@bigpun.me)
 */
interface SqlCrudInterface
{
    /**
     * @return string
     */
    public static function crudGetSource();

    /**
     * @return string|null
     */
    public function crudGetQuery();

    /**
     * @return array
     */
    public function crudColumns();

    /**
     * @return array
     */
    public function crudIgnore();

    /**
     * @param bool $isCreateEvent
     *
     * @return bool
     */
    public function crudBeforeSave($isCreateEvent);

    /**
     * @param bool $isCreateEvent
     *
     * @return bool
     */
    public function crudAfterSave($isCreateEvent);
} 