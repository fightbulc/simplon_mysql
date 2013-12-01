<?php

    namespace Simplon\Db\Mysql;

    use Simplon\Db\Mysql;

    class SqlManager
    {
        /** @var Mysql */
        protected $_mysqlInstance;

        // ########################################

        /**
         * @param Library\Mysql $mysqlInstance
         */
        public function __construct(Mysql $mysqlInstance)
        {
            $this->_mysqlInstance = $mysqlInstance;
        }

        // ########################################

        /**
         * @return Library\Mysql
         */
        protected function _getSqlInstance()
        {
            return $this->_mysqlInstance;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return \QueryResultIterator|\QueryResultIteratorClass
         */
        public function fetchCursor(SqlQueryBuilder $sqlQuery)
        {
            return $this
                ->_getSqlInstance()
                ->Fetch($sqlQuery->getQuery(), $sqlQuery->getConditions());
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool|mixed
         */
        public function fetchColumn(SqlQueryBuilder $sqlQuery)
        {
            $result = $this
                ->_getSqlInstance()
                ->FetchValue($sqlQuery->getQuery(), $sqlQuery->getConditions());

            if (!is_null($result))
            {
                return $result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool|array
         */
        public function fetchRow(SqlQueryBuilder $sqlQuery)
        {
            $result = $this
                ->_getSqlInstance()
                ->FetchArray($sqlQuery->getQuery(), $sqlQuery->getConditions());

            if ($result !== FALSE)
            {
                return $result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool|array
         */
        public function fetchAll(SqlQueryBuilder $sqlQuery)
        {
            $result = $this
                ->_getSqlInstance()
                ->FetchAll($sqlQuery->getQuery(), $sqlQuery->getConditions());

            if (!empty($result))
            {
                return $result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool|array
         */
        public function fetchAllColumn(SqlQueryBuilder $sqlQuery)
        {
            $result = $this->fetchAll($sqlQuery);

            if ($result !== FALSE)
            {
                $mapFunction = function ($a)
                {
                    return array_pop($a);
                };

                return array_map($mapFunction, $result);
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param $type
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool
         */
        protected function _insertReplaceCommand($type, SqlQueryBuilder $sqlQuery)
        {
            $tableName = $sqlQuery->getTableName();
            $data = $sqlQuery->getData();

            if ($tableName && !empty($data))
            {
                // prepare placeholders and values
                $_set = array();
                $_placeholder = array();
                $_values = array();

                foreach ($data as $key => $value)
                {
                    $_set[] = $key;
                    $placeholder_key = ':' . $key;

                    // only ID field gets autoincrement
                    if (is_null($value))
                    {
                        $placeholder_key = 'NULL';
                    }
                    else
                    {
                        $_values[$key] = $value;
                    }

                    $_placeholder[] = $placeholder_key;
                }

                // ------------------------------

                $commandString = 'REPLACE';

                if ($type === 'insert')
                {
                    $commandString = 'INSERT';

                    // insert ignore awareness for tables with unique entries
                    if ($sqlQuery->getInsertIgnore() === TRUE)
                    {
                        $commandString = 'INSERT IGNORE';
                    }
                }

                // ------------------------------

                // sql statement
                $sql = $commandString . ' INTO ' . $tableName . ' (' . join(',', $_set) . ') VALUES (' . join(',', $_placeholder) . ')';

                // insert data
                $insertId = $this
                    ->_getSqlInstance()
                    ->ExecuteSQL($sql, $_values);

                return $insertId;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool|null|string
         */
        public function insert(SqlQueryBuilder $sqlQuery)
        {
            return $this->_insertReplaceCommand('insert', $sqlQuery);
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool|null|string
         */
        public function replace(SqlQueryBuilder $sqlQuery)
        {
            return $this->_insertReplaceCommand('replace', $sqlQuery);
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool|null|string
         */
        public function update(SqlQueryBuilder $sqlQuery)
        {
            $tableName = $sqlQuery->getTableName();
            $newData = $sqlQuery->getData();
            $updateConditions = $sqlQuery->getConditions();

            if ($tableName && !empty($newData) && !empty($updateConditions))
            {
                // prepare placeholders and values
                $_set = array();
                $_values = array();

                foreach ($newData as $key => $value)
                {
                    $placeholder_key = ':' . $key;
                    $_set[] = $key . '=' . $placeholder_key;
                    $_values[$key] = $value;
                }

                // prepare conditions
                $_conditions = array();

                foreach ($updateConditions as $key => $value)
                {
                    /**
                     * Case NULL to enable conditions such as:
                     * IN (1,2,3,4,5)
                     */
                    if (is_null($value))
                    {
                        $_conditions[] = $key;
                    }
                    else
                    {
                        /**
                         * wrap key to prevent duplication with $_values keys
                         */
                        $placeholder_key = ':_simplon_condition_' . $key;
                        $_conditions[] = $key . '= ' . $placeholder_key;
                        $_values[substr($placeholder_key, 1)] = $value;
                    }
                }

                // sql statement
                $sql = 'UPDATE ' . $tableName . ' SET ' . join(',', $_set) . ' WHERE ' . join(' AND ', $_conditions);

                // update data
                return $this
                    ->_getSqlInstance()
                    ->ExecuteSQL($sql, $_values);
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param SqlQueryBuilder $sqlQuery
         *
         * @return bool|null|string
         */
        public function remove(SqlQueryBuilder $sqlQuery)
        {
            $tableName = $sqlQuery->getTableName();
            $deleteConditions = $sqlQuery->getConditions();

            // remove from sql
            if ($tableName && !empty($deleteConditions))
            {
                // prepare conditions
                $_conditions = array();
                $_values = array();

                foreach ($deleteConditions as $key => $value)
                {
                    /**
                     * Case NULL to enable conditions such as:
                     * IN (1,2,3,4,5)
                     */
                    if (is_null($value))
                    {
                        $_conditions[] = $key;
                    }
                    else
                    {
                        $_conditions[] = $key . '= :' . $key;
                        $_values[$key] = $value;
                    }
                }

                // sql statement
                $sql = 'DELETE FROM ' . $tableName . ' WHERE ' . join(' AND ', $_conditions);

                // remove data
                return $this
                    ->_getSqlInstance()
                    ->ExecuteSQL($sql, $_values);
            }

            return FALSE;
        }
    }
