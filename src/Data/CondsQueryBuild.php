<?php

namespace Simplon\Mysql\Data;

class CondsQueryBuild
{
    /**
     * @var array
     */
    protected $strippedConds;
    /**
     * @var array
     */
    protected $condPairs;

    /**
     * @param array $strippedConds
     * @param array $condPairs
     */
    public function __construct(array $strippedConds, array $condPairs)
    {
        $this->strippedConds = $strippedConds;
        $this->condPairs = $condPairs;
    }

    /**
     * @return array
     */
    public function getStrippedConds(): array
    {
        return $this->strippedConds;
    }

    /**
     * @return array
     */
    public function getCondPairs(): array
    {
        return $this->condPairs;
    }
}