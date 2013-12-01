<?php

    namespace Simplon\Db\Mysql;

    class SqlQueryBuilder
    {
        /** @var string */
        protected $tableName;

        /** @var string */
        protected $query;

        /** @var string */
        protected $preparedQuery;

        /** @var bool */
        protected $insertIgnore = FALSE;

        /** @var array */
        protected $conditions = array();

        /** @var array */
        protected $data = array();

        // ##########################################

        /**
         * @param $conditions array
         *
         * @return SqlQueryBuilder
         */
        public function setConditions($conditions)
        {
            $this->conditions = $conditions;

            return $this;
        }

        // ##########################################

        /**
         * @param $condition
         *
         * @return bool
         */
        public function removeCondition($condition)
        {
            if (!isset($this->conditions[$condition]))
            {
                return FALSE;
            }

            unset($this->conditions[$condition]);

            return TRUE;
        }

        // ##########################################

        /**
         * @return array
         */
        public function getConditions()
        {
            return $this->conditions;
        }

        // ##########################################

        /**
         * @return bool
         */
        public function hasConditions()
        {
            return count($this->getConditions()) > 0 ? TRUE : FALSE;
        }

        // ##########################################

        /**
         * @param $query string
         *
         * @return SqlQueryBuilder
         */
        public function setQuery($query)
        {
            $this->query = $query;
            $this->_setPreparedQuery($query);

            return $this;
        }

        // ##########################################

        /**
         * @return string
         */
        public function getQuery()
        {
            return $this->_getPreparedQuery();
        }

        // ##########################################

        /**
         * @param $query
         *
         * @return SqlQueryBuilder
         */
        protected function _setPreparedQuery($query)
        {
            $this->preparedQuery = $query;

            return $this;
        }

        // ##########################################

        /**
         * @return string
         */
        protected function _getPreparedQuery()
        {
            foreach ($this->conditions as $key => $val)
            {
                if (strpos($this->preparedQuery, '_' . $key . '_') !== FALSE)
                {
                    $this->preparedQuery = str_replace('_' . $key . '_', $val, $this->preparedQuery);
                    $this->removeCondition($key);
                }
            }

            return $this->preparedQuery;
        }

        // ##########################################

        /**
         * @param array $data
         *
         * @return SqlQueryBuilder
         */
        public function setData($data)
        {
            $this->data = $data;

            return $this;
        }

        // ##########################################

        /**
         * @return array
         */
        public function getData()
        {
            return $this->data;
        }

        // ##########################################

        /**
         * @param $tableName string
         *
         * @return SqlQueryBuilder
         */
        public function setTableName($tableName)
        {
            $this->tableName = $tableName;

            return $this;
        }

        // ##########################################

        /**
         * @return string
         */
        public function getTableName()
        {
            return $this->tableName;
        }

        // ##########################################

        /**
         * @param $insertIgnore
         *
         * @return SqlQueryBuilder
         */
        public function setInsertIgnore($insertIgnore)
        {
            $this->insertIgnore = $insertIgnore;

            return $this;
        }

        // ##########################################

        /**
         * @return boolean
         */
        public function getInsertIgnore()
        {
            return $this->insertIgnore;
        }
    }
