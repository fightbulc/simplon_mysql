<?php

    namespace Simplon\Mysql;

    class SqlQueryIterator implements \Iterator
    {
        protected $_position;
        protected $_pdoStatement;
        protected $_fetchType;
        protected $_fetchMode;
        protected $_data;

        /** @var callable */
        protected $_closure;

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

        /**
         * @return mixed
         */
        public function getData()
        {
            return call_user_func([$this->_pdoStatement, $this->_fetchType]);
        }

        // ######################################

        function rewind()
        {
            $this->_position = 0;
            $this->_data = $this->getData();
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
            $this->_data = $this->getData();
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