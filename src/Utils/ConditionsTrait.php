<?php

namespace Simplon\Mysql\Utils;

trait ConditionsTrait
{
    /**
     * @var array
     */
    protected $conditions = [];
    /**
     * @var string
     */
    protected $condsQuery;

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param string $key
     * @param mixed $val
     * @param string $operator
     *
     * @return static
     */
    public function addCondition(string $key, $val, string $operator = '=')
    {
        $this->conditions[$key] = [
            'value'    => $val,
            'operator' => $operator,
        ];

        return $this;
    }

    /**
     * @param array $conditions
     *
     * @return static
     */
    public function setConditions(array $conditions)
    {
        foreach ($conditions as $key => $value)
        {
            $this->addCondition($key, $value);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCondsQuery(): ?string
    {
        return $this->condsQuery;
    }

    /**
     * @param string $condsQuery
     *
     * @return static
     */
    public function setCondsQuery(string $condsQuery)
    {
        $this->condsQuery = $condsQuery;

        return $this;
    }
}