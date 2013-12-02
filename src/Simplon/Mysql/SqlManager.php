<?php

    namespace Simplon\Mysql;

    class SqlManager
    {
        /** @var Mysql */
        protected $_mysqlInstance;

        // ########################################

        /**
         * @param Mysql $mysqlInstance
         */
        public function __construct(Mysql $mysqlInstance)
        {
            $this->_mysqlInstance = $mysqlInstance;
        }

        // ########################################

        /**
         * @return Mysql
         */
        protected function _getMysqlInstance()
        {
            return $this->_mysqlInstance;
        }

        // ########################################

        /**
         * @return bool|int
         */
        public function getRowCount()
        {
            return $this
                ->_getMysqlInstance()
                ->getRowCount();
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return bool
         */
        public function executeSql(SqlQueryBuilder $sqlBuilder)
        {
            return $this
                ->_getMysqlInstance()
                ->executeSql($sqlBuilder->getQuery());
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return bool|string
         */
        public function fetchColumn(SqlQueryBuilder $sqlBuilder)
        {
            $result = $this
                ->_getMysqlInstance()
                ->fetchColumn($sqlBuilder->getQuery(), $sqlBuilder->getConditions());

            if ($result !== NULL)
            {
                return (string)$result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return array|bool
         */
        public function fetchColumnMany(SqlQueryBuilder $sqlBuilder)
        {
            $result = $this
                ->_getMysqlInstance()
                ->fetchColumnMany($sqlBuilder->getQuery(), $sqlBuilder->getConditions());

            if ($result !== NULL)
            {
                return (array)$result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return SqlQueryIterator
         */
        public function fetchColumnManyCursor(SqlQueryBuilder $sqlBuilder)
        {
            return $this
                ->_getMysqlInstance()
                ->fetchColumnManyCursor($sqlBuilder->getQuery(), $sqlBuilder->getConditions());
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return array|bool
         */
        public function fetchRow(SqlQueryBuilder $sqlBuilder)
        {
            $result = $this
                ->_getMysqlInstance()
                ->fetchRow($sqlBuilder->getQuery(), $sqlBuilder->getConditions());

            if ($result !== NULL)
            {
                return (array)$result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return array|bool
         */
        public function fetchRowMany(SqlQueryBuilder $sqlBuilder)
        {
            $result = $this
                ->_getMysqlInstance()
                ->fetchRowMany($sqlBuilder->getQuery(), $sqlBuilder->getConditions());

            if ($result !== NULL)
            {
                return (array)$result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return SqlQueryIterator
         */
        public function fetchRowManyCursor(SqlQueryBuilder $sqlBuilder)
        {
            return $this
                ->_getMysqlInstance()
                ->fetchRowManyCursor($sqlBuilder->getQuery(), $sqlBuilder->getConditions());
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return array|null
         */
        public function insert(SqlQueryBuilder $sqlBuilder)
        {
            if ($sqlBuilder->hasMultiData())
            {
                return $this->_getMysqlInstance()
                    ->insertMany(
                        $sqlBuilder->getTableName(),
                        $sqlBuilder->getData(),
                        $sqlBuilder->hasInsertIgnore()
                    );
            }

            return $this->_getMysqlInstance()
                ->insert(
                    $sqlBuilder->getTableName(),
                    $sqlBuilder->getData(),
                    $sqlBuilder->hasInsertIgnore()
                );
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return array|null
         */
        public function replace(SqlQueryBuilder $sqlBuilder)
        {
            if ($sqlBuilder->hasMultiData())
            {
                return $this->_getMysqlInstance()
                    ->replaceMany(
                        $sqlBuilder->getTableName(),
                        $sqlBuilder->getData()
                    );
            }

            return $this->_getMysqlInstance()
                ->replace(
                    $sqlBuilder->getTableName(),
                    $sqlBuilder->getData()
                );
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return bool
         */
        public function update(SqlQueryBuilder $sqlBuilder)
        {
            return $this->_getMysqlInstance()
                ->update(
                    $sqlBuilder->getTableName(),
                    $sqlBuilder->getConditions(),
                    $sqlBuilder->getData()
                );
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlBuilder
         *
         * @return bool
         */
        public function delete(SqlQueryBuilder $sqlBuilder)
        {
            return $this->_getMysqlInstance()
                ->delete($sqlBuilder->getTableName(), $sqlBuilder->getConditions());
        }
    }
