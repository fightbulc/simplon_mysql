<?php

namespace Simplon\Mysql\Crud;

/**
 * @package Simplon\Mysql\Crud
 */
abstract class CrudModel implements CrudModelInterface
{
    /**
     * @var string
     */
    private $internalChecksum;

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
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->internalChecksum !== $this->calcMd5($this->toArray());
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public function fromArray(array $data)
    {
        if ($data)
        {
            foreach ($data as $fieldName => $val)
            {
                // format field name
                if (strpos($fieldName, '_') !== false)
                {
                    $fieldName = self::camelCaseString($fieldName);
                }

                $setMethodName = 'set' . ucfirst($fieldName);

                // set on setter
                if (method_exists($this, $setMethodName))
                {
                    $this->$setMethodName($val);
                    continue;
                }

                // set on field
                if (property_exists($this, $fieldName))
                {
                    $this->$fieldName = $val;
                    continue;
                }
            }

            // lets create checksum here
            $this->internalChecksum = $this->calcMd5($this->toArray());
        }

        return $this;
    }

    /**
     * @param bool $snakeCase
     *
     * @return array
     */
    public function toArray($snakeCase = true): array
    {
        $result = [];

        $visibleFields = get_class_vars(get_called_class());

        // render column names
        foreach ($visibleFields as $fieldName => $value)
        {
            $propertyName = $fieldName;
            $getMethodName = 'get' . ucfirst($fieldName);

            // format field name
            if ($snakeCase === true && strpos($fieldName, '_') === false)
            {
                $fieldName = self::snakeCaseString($fieldName);
            }

            // get from getter
            if (method_exists($this, $getMethodName))
            {
                $result[$fieldName] = $this->$getMethodName();
                continue;
            }

            // get from field
            if (property_exists($this, $propertyName))
            {
                if ($propertyName !== 'internalChecksum')
                {
                    $result[$fieldName] = $this->$propertyName;
                    continue;
                }
            }
        }

        return $result;
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected static function snakeCaseString(string $string)
    {
        return strtolower(preg_replace('/([A-Z])/', '_\\1', $string));
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected static function camelCaseString(string $string)
    {
        $string = strtolower($string);
        $string = ucwords(str_replace('_', ' ', $string));

        return lcfirst(str_replace(' ', '', $string));
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function calcMd5(array $data): string
    {
        ksort($data);

        return md5(json_encode($data));
    }
}