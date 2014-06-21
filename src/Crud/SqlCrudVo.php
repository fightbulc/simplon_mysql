<?php

namespace Simplon\Mysql\Crud;

abstract class SqlCrudVo implements SqlCrudInterface
{
    /** @var string */
    protected static $crudSource;

    /** @var string */
    protected static $crudQuery = '';

    /** @var array */
    protected $crudColumns = [];

    /** @var array */
    protected $crudIgnoreVariables = [];

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
    public function crudGetQuery()
    {
        return self::$crudQuery;
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
     * @return array
     */
    protected function crudParseVariables()
    {
        if (!$this->crudColumns)
        {
            $variables = get_class_vars(get_called_class());

            // remove this class's variables
            unset($variables['crudIgnoreVariables']);
            unset($variables['crudColumns']);
            unset($variables['crudSource']);
            unset($variables['crudQuery']);

            // render column names
            foreach ($variables as $name => $value)
            {
                if (in_array($name, $this->crudIgnoreVariables) === false)
                {
                    $this->crudColumns[$name] = strtolower(preg_replace('/([A-Z])/', '_\\1', $name));
                }
            }
        }

        return $this->crudColumns;
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