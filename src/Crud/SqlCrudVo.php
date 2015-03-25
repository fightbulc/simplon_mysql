<?php

namespace Simplon\Mysql\Crud;

/**
 * SqlCrudVo
 * @package Simplon\Mysql\Crud
 * @author  Tino Ehrich (tino@bigpun.me)
 */
abstract class SqlCrudVo implements SqlCrudInterface
{
    /**
     * @var string
     */
    private static $crudSource;

    /**
     * @var string
     */
    private $crudQuery;

    /**
     * @param string $query
     */
    public function __construct($query = null)
    {
        $this->crudQuery = $query;
    }

    /**
     * @return string
     */
    public static function crudGetSource()
    {
        if (self::$crudSource === null)
        {
            // remove "CrudVo"
            $class = str_replace('CrudVo', '', get_called_class());

            // transform from CamelCase and pluralise
            self::$crudSource = substr(self::snakeCaseString($class) . 's', 1);
        }

        return self::$crudSource;
    }

    /**
     * @return string|null
     */
    public function crudGetQuery()
    {
        return $this->crudQuery;
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
    public function crudColumns()
    {
        return $this->iterateOverProcessableFields(
            function ($result, $fieldName)
            {
                $result[$fieldName] = self::snakeCaseString($fieldName);

                return $result;
            }
        );
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public function fromArray(array $data)
    {
        foreach ($data as $fieldName => $val)
        {
            // format field name
            if (strpos($fieldName, '_') !== false)
            {
                $fieldName = self::camelCaseString($fieldName);
            }

            $setMethodName = 'set' . ucfirst($fieldName);

            if (method_exists($this, $setMethodName))
            {
                $this->$setMethodName($val);
            }
        }

        return $this;
    }

    /**
     * @param bool $snakeCase
     *
     * @return array
     */
    public function toArray($snakeCase = true)
    {
        return $this->iterateOverProcessableFields(
            function ($result, $fieldName) use ($snakeCase)
            {
                $getMethodName = 'get' . ucfirst($fieldName);

                // format field name
                if ($snakeCase === true && strpos($fieldName, '_') === false)
                {
                    $fieldName = self::snakeCaseString($fieldName);
                }

                if (method_exists($this, $getMethodName))
                {
                    $result[$fieldName] = $this->$getMethodName();
                }

                return $result;
            }
        );
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

    /**
     * @param $string
     *
     * @return string
     */
    private static function snakeCaseString($string)
    {
        return strtolower(preg_replace('/([A-Z])/', '_\\1', $string));
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private static function camelCaseString($string)
    {
        $string = strtolower($string);
        $string = ucwords(str_replace('_', ' ', $string));

        return lcfirst(str_replace(' ', '', $string));
    }

    /**
     * @return array
     */
    private function getProcessableFields()
    {
        $variables = get_class_vars(get_called_class());
        $ignore = $this->crudIgnore();
        $columns = array();

        // remove this class's variables
        unset($variables['crudSource']);
        unset($variables['crudQuery']);

        // render column names
        foreach ($variables as $fieldName => $value)
        {
            if (in_array($fieldName, $ignore) === false)
            {
                $columns[] = $fieldName;
            }
        }

        return $columns;
    }

    /**
     * @param \Closure $callable
     *
     * @return array
     */
    private function iterateOverProcessableFields(\Closure $callable)
    {
        $result = array();
        $processableFields = $this->getProcessableFields();

        // render column names
        foreach ($processableFields as $fieldName)
        {
            $result = $callable($result, $fieldName);
        }

        return $result;
    }
}