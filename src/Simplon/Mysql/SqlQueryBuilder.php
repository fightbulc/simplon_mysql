<?php

    namespace Simplon\Mysql;

    class SqlQueryBuilder
    {
        /** @var string */
        protected $_tableName;

        /** @var string */
        protected $_query;

        /** @var bool */
        protected $_enableInsertIgnore = FALSE;

        /** @var array */
        protected $_conditions = [];

        /** @var  string */
        protected $_conditionsQuery;

        /** @var array */
        protected $_data = [];

        // ##########################################

        /**
         * @param $query string
         *
         * @return SqlQueryBuilder
         */
        public function setQuery($query)
        {
            $this->_query = $query;

            return $this;
        }

        // ##########################################

        /**
         * @return string
         */
        public function getQuery()
        {
            foreach ($this->_conditions as $key => $val)
            {
                if (strpos($this->_query, '_' . $key . '_') !== FALSE)
                {
                    $this->_query = str_replace('_' . $key . '_', $val, $this->_query);

                    // remove placeholder condition
                    $this->_removeCondition($key);
                }
            }

            return (string)$this->_query;
        }

        // ##########################################

        /**
         * @param $conditions array
         *
         * @return SqlQueryBuilder
         */
        public function setConditions($conditions)
        {
            $this->_conditions = $conditions;

            return $this;
        }

        // ##########################################

        /**
         * @return array
         */
        public function getConditions()
        {
            return (array)$this->_conditions;
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool
         */
        protected function _removeCondition($key)
        {
            if (isset($this->_conditions[$key]))
            {
                unset($this->_conditions[$key]);

                return TRUE;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param string $conditionsQuery
         *
         * @return SqlQueryBuilder
         */
        public function setConditionsQuery($conditionsQuery)
        {
            $this->_conditionsQuery = $conditionsQuery;

            return $this;
        }

        // ##########################################

        /**
         * @return null|string
         */
        public function getConditionsQuery()
        {
            if ($this->_conditionsQuery)
            {
                return (string)$this->_conditionsQuery;
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param array $data
         *
         * @return SqlQueryBuilder
         */
        public function setData($data)
        {
            $this->_data = $data;

            return $this;
        }

        // ##########################################

        /**
         * @return array
         */
        public function getData()
        {
            return (array)$this->_data;
        }

        // ##########################################

        /**
         * @return bool
         */
        public function hasMultiData()
        {
            return isset($this->_data[0]) && is_array($this->_data[0]);
        }

        // ##########################################

        /**
         * @param $tableName string
         *
         * @return SqlQueryBuilder
         */
        public function setTableName($tableName)
        {
            $this->_tableName = $tableName;

            return $this;
        }

        // ##########################################

        /**
         * @return string
         */
        public function getTableName()
        {
            return (string)$this->_tableName;
        }

        // ##########################################

        /**
         * @param $insertIgnore
         *
         * @return SqlQueryBuilder
         */
        public function enableInsertIgnore($insertIgnore)
        {
            $this->_enableInsertIgnore = $insertIgnore;

            return $this;
        }

        // ##########################################

        /**
         * @return bool
         */
        public function hasInsertIgnore()
        {
            return $this->_enableInsertIgnore !== FALSE ? TRUE : FALSE;
        }
    }
