<?php

namespace Simplon\Mysql\Crud;

abstract class SqlCrudVo implements SqlCrudInterface
{
    /** @var string */
    protected static $crudSource;

    /** @var string */
    protected static $crudQuery = '';

    /**
     * @param $query
     */
    public static function crudSetQuery($query)
    {
        self::$crudQuery = $query;
    }

    /**
     * @return string
     */
    public static function crudGetSource()
    {
        if (!self::$crudSource)
        {
            // remove "Vo"
            $class = str_replace('Vo', '', get_called_class());

            // transform from CamelCase and pluralise
            self::$crudSource = substr(strtolower(preg_replace('/([A-Z])/', '_\\1', $class)) . 's', 1);
        }

        return self::$crudSource;
    }

    /**
     * @return string
     */
    public function crudGetQuery()
    {
        return self::$crudQuery;
    }

    /**
     * @return array
     */
    public function crudIgnore()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function crudParseVariables()
    {
        $variables = get_class_vars(get_called_class());
        $ignore = $this->crudIgnore();
        $columns = array();

        // remove this class's variables
        unset($variables['crudSource']);
        unset($variables['crudQuery']);

        // render column names
        foreach ($variables as $name => $value)
        {
            if (in_array($name, $ignore) === false)
            {
                $columns[$name] = strtolower(preg_replace('/([A-Z])/', '_\\1', $name));
            }
        }

        return $columns;
    }

    /**
     * @return array
     */
    public function crudColumns()
    {
        return $this->crudParseVariables();
    }

    /**
     * @param bool $isCreateEvent
     *
     * @return bool
     */
    public function crudBeforeSave($isCreateEvent)
    {
        return true;
    }

    /**
     * @param bool $isCreateEvent
     *
     * @return bool
     */
    public function crudAfterSave($isCreateEvent)
    {
        return true;
    }
} 