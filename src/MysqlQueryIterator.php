<?php

namespace Simplon\Mysql;

class MysqlQueryIterator implements \Iterator
{
    /**
     * @var int
     */
    protected $position = 0;
    /**
     * @var \PDOStatement
     */
    protected $pdoStatement;
    /**
     * @var string
     */
    protected $fetchType;
    /**
     * @var int
     */
    protected $fetchMode;
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param \PDOStatement $pdoStatement
     * @param string $fetchType
     * @param int $fetchMode
     */
    public function __construct(\PDOStatement $pdoStatement, string $fetchType, int $fetchMode = \PDO::FETCH_ASSOC)
    {
        $this->pdoStatement = $pdoStatement;
        $this->fetchType = $fetchType;
        $this->fetchMode = $fetchMode;
    }

    function rewind(): void
    {
        $this->position = 0;
        $this->data = $this->fetchData();
    }

    /**
     * @return mixed
     */
    function current()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    function key(): int
    {
        return $this->position;
    }

    function next(): void
    {
        $this->data = $this->fetchData();
        ++$this->position;
    }

    /**
     * @return bool
     */
    function valid(): bool
    {
        return $this->data !== false;
    }

    /**
     * @return mixed
     */
    private function fetchData()
    {
        if ($this->fetchType === 'fetch')
        {
            return $this->pdoStatement->fetch($this->fetchMode);
        }

        return $this->pdoStatement->fetchColumn();
    }
}