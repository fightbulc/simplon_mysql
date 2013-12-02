<?php

    namespace Simplon\Mysql;

    class SqlQueryIterator implements \Iterator
    {
        protected $_position;
        protected $_pdoStatement;
        protected $_fetchType;
        protected $_fetchMode;
        protected $_data;

        // ######################################

        /**
         * @param \PDOStatement $pdoStatement
         * @param $fetchType
         * @param int $fetchMode
         */
        public function __construct(\PDOStatement $pdoStatement, $fetchType, $fetchMode = \PDO::FETCH_ASSOC)
        {
            $this->_position = 0;
            $this->_pdoStatement = $pdoStatement;
            $this->_fetchType = $fetchType;
            $this->_fetchMode = $fetchMode;
        }

        // ######################################

        function rewind()
        {
            $this->_position = 0;
            $this->_data = $this->_fetchType === 'fetch' ? $this->_pdoStatement->fetch($this->_fetchMode) : $this->_pdoStatement->fetchColumn();
        }

        // ######################################

        /**
         * @return mixed
         */
        function current()
        {
            return $this->_data;
        }

        // ######################################

        /**
         * @return int|mixed
         */
        function key()
        {
            return $this->_position;
        }

        // ######################################

        function next()
        {
            $this->_data = $this->_fetchType === 'fetch' ? $this->_pdoStatement->fetch($this->_fetchMode) : $this->_pdoStatement->fetchColumn();
            ++$this->_position;
        }

        // ######################################

        /**
         * @return bool
         */
        function valid()
        {
            return $this->_data !== FALSE;
        }
    }