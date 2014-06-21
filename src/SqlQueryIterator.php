<?php

namespace Simplon\Mysql;

class SqlQueryIterator implements \Iterator
{
    protected $position;
    protected $pdoStatement;
    protected $fetchType;
    protected $fetchMode;
    protected $data;

    /**
     * @param \PDOStatement $pdoStatement
     * @param $fetchType
     * @param int $fetchMode
     */
    public function __construct(\PDOStatement $pdoStatement, $fetchType, $fetchMode = \PDO::FETCH_ASSOC)
    {
        $this->position = 0;
        $this->pdoStatement = $pdoStatement;
        $this->fetchType = $fetchType;
        $this->fetchMode = $fetchMode;
    }

    function rewind()
    {
        $this->position = 0;
        $this->data = $this->fetchType === 'fetch' ? $this->pdoStatement->fetch($this->fetchMode) : $this->pdoStatement->fetchColumn();
    }

    /**
     * @return mixed
     */
    function current()
    {
        return $this->data;
    }

    /**
     * @return int|mixed
     */
    function key()
    {
        return $this->position;
    }

    function next()
    {
        $this->data = $this->fetchType === 'fetch' ? $this->pdoStatement->fetch($this->fetchMode) : $this->pdoStatement->fetchColumn();
        ++$this->position;
    }

    /**
     * @return bool
     */
    function valid()
    {
        return $this->data !== false;
    }
}