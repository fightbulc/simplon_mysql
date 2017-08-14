<?php

namespace Simplon\Mysql\Utils;

use Simplon\Helper\Data\Data;
use Simplon\Helper\SecurityUtil;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;

class UniqueTokenOptions extends Data
{
    /**
     * @var string
     */
    protected $column = 'token';
    /**
     * @var int
     */
    protected $length = 12;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var string
     */
    protected $characters = SecurityUtil::TOKEN_UPPERCASE_LETTERS_NUMBERS;
    /**
     * @var ReadQueryBuilder
     */
    protected $readQuery;

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return null|ReadQueryBuilder
     */
    public function mergeReadQuery(ReadQueryBuilder $builder): ?ReadQueryBuilder
    {
        if ($this->readQuery)
        {
            foreach ($builder->getConditions() as $key => $val)
            {
                $this->readQuery->addCondition($key, $val);
            }

            return $this->readQuery;
        }

        return $builder;
    }

    /**
     * @param ReadQueryBuilder $readQuery
     *
     * @return UniqueTokenOptions
     */
    public function presetReadQuery(ReadQueryBuilder $readQuery): UniqueTokenOptions
    {
        $this->readQuery = $readQuery;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @param string $column
     *
     * @return UniqueTokenOptions
     */
    public function setColumn(string $column): UniqueTokenOptions
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     *
     * @return UniqueTokenOptions
     */
    public function setLength(int $length): UniqueTokenOptions
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     *
     * @return UniqueTokenOptions
     */
    public function setPrefix(string $prefix): UniqueTokenOptions
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getCharacters(): string
    {
        return $this->characters;
    }

    /**
     * @param string $characters
     *
     * @return UniqueTokenOptions
     */
    public function setCharacters(string $characters): UniqueTokenOptions
    {
        $this->characters = $characters;

        return $this;
    }
}