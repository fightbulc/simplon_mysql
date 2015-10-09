<?php

namespace Simplon\Mysql\Crud;

/**
 * Class CrudModel
 * @package Simplon\Mysql\Crud
 */
abstract class CrudModel implements CrudModelInterface
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

            // set by setter
            if (method_exists($this, $setMethodName))
            {
                $this->$setMethodName($val);
                continue;
            }

            // set directly on field
            $this->$fieldName = $val;
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
        $result = [];

        $visibleFields = get_class_vars(get_called_class());

        // render column names
        foreach ($visibleFields as $fieldName => $value)
        {
            $getMethodName = 'get' . ucfirst($fieldName);

            // format field name
            if ($snakeCase === true && strpos($fieldName, '_') === false)
            {
                $fieldName = self::snakeCaseString($fieldName);
            }

            // set by getter
            if (method_exists($this, $getMethodName))
            {
                $result[$fieldName] = $this->$getMethodName();
                continue;
            }

            // get from field
            $result[$fieldName] = $this->$fieldName;
        }

        return $result;
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected static function snakeCaseString($string)
    {
        return strtolower(preg_replace('/([A-Z])/', '_\\1', $string));
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected static function camelCaseString($string)
    {
        $string = strtolower($string);
        $string = ucwords(str_replace('_', ' ', $string));

        return lcfirst(str_replace(' ', '', $string));
    }
}