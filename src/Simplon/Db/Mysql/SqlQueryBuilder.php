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

        /** @var bool */
        protected $multiData = FALSE;

        /** @var array */
        protected $conditions = [];

        /** @var array */
        protected $data = [];

        // ##########################################

        /**
         * @param $query string
         *
         * @return SqlQueryBuilder
         */
        public function setQuery($query)
        {
            $this->query = $query;

            return $this;
        }

        // ##########################################

        /**
         * @return string
         */
        public function getQuery()
        {
            foreach ($this->conditions as $key => $val)
            {
                if (strpos($this->query, '_' . $key . '_') !== FALSE)
                {
                    $this->query = str_replace('_' . $key . '_', $val, $this->query);

                    // remove placeholder condition
                    $this->_removeCondition($key);
                }
            }

            return (string)$this->query;
        }

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
         * @return array
         */
        public function getConditions()
        {
            return (array)$this->conditions;
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool
         */
        protected function _removeCondition($key)
        {
            if (isset($this->conditions[$key]))
            {
                unset($this->conditions[$key]);

                return TRUE;
            }

            return FALSE;
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
            return (array)$this->data;
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
            return (string)$this->tableName;
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
         * @return bool
         */
        public function hasInsertIgnore()
        {
            return (bool)$this->insertIgnore;
        }

        // ##########################################

        /**
         * @param boolean $multiData
         *
         * @return SqlQueryBuilder
         */
        public function setMultiData($multiData)
        {
            $this->multiData = $multiData;

            return $this;
        }

        // ##########################################

        /**
         * @return bool
         */
        public function hasMultiData()
        {
            return (bool)$this->multiData;
        }
    }
