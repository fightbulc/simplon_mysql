<?php

    namespace Simplon\Mysql;

    class Mysql
    {
        protected $_dbh;
        protected $_fetchMode;

        /** @var  \PDOStatement */
        protected $_lastStatement;

        // ##########################################

        /**
         * @param MysqlConfigVo $mysqlConfigVo
         *
         * @throws MysqlException
         */
        public function __construct(MysqlConfigVo $mysqlConfigVo)
        {
            try
            {
                // set dns
                $dns = [];

                // use unix socket
                if ($mysqlConfigVo->hasUnixSocket())
                {
                    $dns[] = 'mysql:unix_socket=' . $mysqlConfigVo->getUnixSocket();
                }

                // use server
                else
                {
                    $dns[] = 'mysql:host=' . $mysqlConfigVo->getServer();

                    if ($mysqlConfigVo->hasPort())
                    {
                        $dns[] = 'port=' . $mysqlConfigVo->getPort();
                    }
                }

                $dns[] = 'dbname=' . $mysqlConfigVo->getDatabase();
                $dns[] = 'charset=' . $mysqlConfigVo->getCharset();

                // ------------------------------

                // create PDO instance
                $this->_setDbh(new \PDO(join(';', $dns), $mysqlConfigVo->getUsername(), $mysqlConfigVo->getPassword()));

                // set fetchMode
                $this->_setFetchMode($mysqlConfigVo->getFetchMode());
            }
            catch (\PDOException $e)
            {
                throw new MysqlException($e->getMessage(), $e->getCode());
            }
        }

        // ##########################################

        /**
         * @param $dbh
         *
         * @return Mysql
         */
        protected function _setDbh($dbh)
        {
            $this->_dbh = $dbh;

            return $this;
        }

        // ##########################################

        /**
         * @return \PDO
         */
        protected function _getDbh()
        {
            return $this->_dbh;
        }

        // ##########################################

        /**
         * @param mixed $fetchMode
         *
         * @return Mysql
         */
        protected function _setFetchMode($fetchMode)
        {
            $this->_fetchMode = $fetchMode;

            return $this;
        }

        // ##########################################

        /**
         * @return int
         */
        protected function _getFetchMode()
        {
            return (int)$this->_fetchMode;
        }

        // ##########################################

        /**
         * @param array $errorInfo
         *
         * @return array
         */
        protected function _prepareErrorInfo(array $errorInfo)
        {
            return [
                'sqlStateCode' => $errorInfo[0],
                'code'         => $errorInfo[1],
                'message'      => $errorInfo[2],
            ];
        }

        // ##########################################

        /**
         * @param \PDOStatement $cursor
         *
         * @return Mysql
         */
        protected function _setLastStatement(\PDOStatement $cursor)
        {
            $this->_lastStatement = $cursor;

            return $this;
        }

        // ##########################################

        /**
         * @return \PDOStatement
         */
        protected function _getLastStatement()
        {
            return $this->_lastStatement;
        }

        // ##########################################

        /**
         * @return bool
         */
        protected function _hasLastStatement()
        {
            return $this->_lastStatement ? TRUE : FALSE;
        }

        // ##########################################

        /**
         * @return Mysql
         */
        protected function _clearLastStatement()
        {
            $this->_lastStatement = NULL;

            return $this;
        }

        // ##########################################

        /**
         * @param $paramValue
         *
         * @return int
         * @throws MysqlException
         */
        protected function _getParamType($paramValue)
        {
            switch ($paramValue)
            {
                case is_int($paramValue):
                    return \PDO::PARAM_INT;

                case is_bool($paramValue):
                    return \PDO::PARAM_INT;

                case is_string($paramValue):
                    return \PDO::PARAM_STR;

                case is_float($paramValue):
                    return \PDO::PARAM_STR;

                case is_double($paramValue):
                    return \PDO::PARAM_STR;

                case is_null($paramValue):
                    return \PDO::PARAM_NULL;

                default:
                    throw new MysqlException("Invalid param type: {$paramValue} with type {gettype($paramValue)}");
            }
        }

        // ##########################################

        /**
         * @param \PDOStatement $pdoStatement
         * @param array $params
         *
         * @return \PDOStatement
         */
        protected function _setParams(\PDOStatement $pdoStatement, array $params)
        {
            foreach ($params as $key => &$val)
            {
                $pdoStatement->bindParam($key, $val, $this->_getParamType($val));
            }

            return $pdoStatement;
        }

        // ##########################################

        /**
         * @param $query
         * @param array $params
         *
         * @return \PDOStatement
         * @throws MysqlException
         */
        protected function _prepareSelect($query, array $params)
        {
            // clear last statement
            $this->_clearLastStatement();

            // set query
            $pdoStatement = $this->_getDbh()
                ->prepare($query);

            // bind named params
            $pdoStatement = $this->_setParams($pdoStatement, $params);

            // execute
            $pdoStatement->execute();

            // check for errors
            if ($pdoStatement->errorCode() === '00000')
            {
                // cache statement
                $this->_setLastStatement($pdoStatement);

                return $pdoStatement;
            }

            // ----------------------------------

            $error = [
                'query'     => $query,
                'params'    => $params,
                'errorInfo' => $this->_prepareErrorInfo($pdoStatement->errorInfo()),
            ];

            $errorInfo = json_encode($error);

            throw new MysqlException("Houston we have a problem: {$errorInfo}");
        }

        // ##########################################

        /**
         * @param $query
         * @param array $rowsMany
         *
         * @return array
         * @throws MysqlException
         */
        protected function _prepareInsertReplace($query, array $rowsMany)
        {
            $dbh = $this->_getDbh();
            $responses = [];

            // clear last statement
            $this->_clearLastStatement();

            // set query
            $pdoStatement = $dbh->prepare($query);

            // loop through rows
            while ($row = array_shift($rowsMany))
            {
                // bind params
                $pdoStatement = $this->_setParams($pdoStatement, $row);

                // execute
                $pdoStatement->execute();

                // throw errors
                if ($pdoStatement->errorCode() !== '00000')
                {
                    $error = [
                        'query'     => $query,
                        'errorInfo' => $this->_prepareErrorInfo($pdoStatement->errorInfo()),
                    ];

                    $errorInfo = json_encode($error);

                    throw new MysqlException("Houston we have a problem: {$errorInfo}");
                }

                // last insert|bool
                $lastInsert = $dbh->lastInsertId();

                // cache response
                $responses[] = $lastInsert ? (int)$lastInsert : TRUE;
            }

            return $responses;
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         * @param array $data
         *
         * @return bool
         * @throws MysqlException
         */
        protected function _prepareUpdate($query, array $conds, array $data)
        {
            // clear last statement
            $this->_clearLastStatement();

            // set query
            $pdoStatement = $this->_getDbh()
                ->prepare($query);

            // bind conds params
            $pdoStatement = $this->_setParams($pdoStatement, $conds);

            // bind data params
            $pdoStatement = $this->_setParams($pdoStatement, $data);

            // execute
            $pdoStatement->execute();

            // cache statement
            $this->_setLastStatement($pdoStatement);

            // throw errors
            if ($pdoStatement->errorCode() !== '00000')
            {
                $error = [
                    'query'     => $query,
                    'conds'     => $conds,
                    'errorInfo' => $this->_prepareErrorInfo($pdoStatement->errorInfo()),
                ];

                $errorInfo = json_encode($error);

                throw new MysqlException("Houston we have a problem: {$errorInfo}");
            }

            if ($this->getRowCount() > 0)
            {
                return TRUE;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return bool
         * @throws MysqlException
         */
        protected function _prepareDelete($query, array $conds)
        {
            // clear last statement
            $this->_clearLastStatement();

            // set query
            $pdoStatement = $this->_getDbh()
                ->prepare($query);

            // bind conds params
            $pdoStatement = $this->_setParams($pdoStatement, $conds);

            // execute
            $pdoStatement->execute();

            // cache statement
            $this->_setLastStatement($pdoStatement);

            // throw errors
            if ($pdoStatement->errorCode() !== '00000')
            {
                $error = [
                    'query'     => $query,
                    'conds'     => $conds,
                    'errorInfo' => $this->_prepareErrorInfo($pdoStatement->errorInfo()),
                ];

                $errorInfo = json_encode($error);

                throw new MysqlException("Houston we have a problem: {$errorInfo}");
            }

            if ($this->getRowCount() > 0)
            {
                return TRUE;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @return bool|int
         */
        public function getRowCount()
        {
            if ($this->_hasLastStatement() !== FALSE)
            {
                return $this->_getLastStatement()
                    ->rowCount();
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $query
         *
         * @return bool
         * @throws MysqlException
         */
        public function executeSql($query)
        {
            $dbh = $this->_getDbh();

            $response = $dbh->exec($query);

            if ($response !== FALSE)
            {
                return TRUE;
            }

            $error = [
                'query'     => $query,
                'errorInfo' => $this->_prepareErrorInfo($dbh->errorInfo()),
            ];

            $errorInfo = json_encode($error);

            throw new MysqlException("Houston we have a problem: {$errorInfo}");
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return null|string
         */
        public function fetchColumn($query, array $conds = [])
        {
            $response = $this->_prepareSelect($query, $conds)
                ->fetchColumn();

            if ($response !== FALSE)
            {
                return (string)$response;
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return array|null
         */
        public function fetchColumnMany($query, array $conds = [])
        {
            $responsesMany = [];
            $pdoStatment = $this->_prepareSelect($query, $conds);

            while ($response = $pdoStatment->fetchColumn())
            {
                $responsesMany[] = $response;
            }

            if (!empty($responsesMany))
            {
                return (array)$responsesMany;
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return SqlQueryIterator
         */
        public function fetchColumnManyCursor($query, array $conds = [])
        {
            $this->_prepareSelect($query, $conds);

            // ----------------------------------

            return new SqlQueryIterator($this->_getLastStatement(), 'fetchColumn');
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return array|null
         */
        public function fetchRow($query, array $conds = [])
        {
            $response = $this->_prepareSelect($query, $conds)
                ->fetch($this->_getFetchMode());

            if ($response !== FALSE)
            {
                return (array)$response;
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return array|null
         */
        public function fetchRowMany($query, array $conds = [])
        {
            $responsesMany = [];
            $pdoStatment = $this->_prepareSelect($query, $conds);

            while ($response = $pdoStatment->fetch($this->_getFetchMode()))
            {
                $responsesMany[] = $response;
            }

            if (!empty($responsesMany))
            {
                return (array)$responsesMany;
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return SqlQueryIterator
         */
        public function fetchRowManyCursor($query, array $conds = [])
        {
            $this->_prepareSelect($query, $conds);

            // ----------------------------------

            return new SqlQueryIterator($this->_getLastStatement(), 'fetch');
        }

        // ##########################################

        /**
         * @param $tableName
         * @param array $data
         * @param bool $insertIgnore
         *
         * @return int|bool|null
         * @throws MysqlException
         */
        public function insert($tableName, array $data, $insertIgnore = FALSE)
        {
            if (isset($data[0]))
            {
                throw new MysqlException("Multi-dimensional datasets are not allowed. Use 'Mysql::insertMany()' instead");
            }

            $response = $this->insertMany($tableName, [$data], $insertIgnore);

            if ($response !== NULL)
            {
                return array_pop($response);
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param $tableName
         * @param array $data
         * @param bool $insertIgnore
         *
         * @return array|null
         * @throws MysqlException
         */
        public function insertMany($tableName, array $data, $insertIgnore = FALSE)
        {
            if (!isset($data[0]))
            {
                throw new MysqlException("One-dimensional datasets are not allowed. Use 'Mysql::insert()' instead");
            }

            $query = 'INSERT' . ($insertIgnore === TRUE ? ' IGNORE ' : NULL) . ' INTO ' . $tableName . ' (:COLUMN_NAMES) VALUES (:PARAM_NAMES)';

            $placeholder = [
                'column_names' => [],
                'param_names'  => [],
            ];

            foreach ($data[0] as $columnName => $value)
            {
                $placeholder['column_names'][] = $columnName;
                $placeholder['param_names'][] = ':' . $columnName;
            }

            $query = str_replace(':COLUMN_NAMES', join(', ', $placeholder['column_names']), $query);
            $query = str_replace(':PARAM_NAMES', join(', ', $placeholder['param_names']), $query);

            // ----------------------------------

            $response = $this->_prepareInsertReplace($query, $data);

            if (!empty($response))
            {
                return (array)$response;
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param $tableName
         * @param array $data
         *
         * @return array|null
         * @throws MysqlException
         */
        public function replace($tableName, array $data)
        {
            if (isset($data[0]))
            {
                throw new MysqlException("Multi-dimensional datasets are not allowed. Use 'Mysql::replaceMany()' instead");
            }

            return $this->replaceMany($tableName, [$data]);
        }

        // ##########################################

        /**
         * @param $tableName
         * @param array $data
         *
         * @return array|null
         * @throws MysqlException
         */
        public function replaceMany($tableName, array $data)
        {
            if (!isset($data[0]))
            {
                throw new MysqlException("One-dimensional datasets are not allowed. Use 'Mysql::replace()' instead");
            }

            $query = 'REPLACE INTO ' . $tableName . ' (:COLUMN_NAMES) VALUES (:PARAM_NAMES)';

            $placeholder = [
                'column_names' => [],
                'param_names'  => [],
            ];

            foreach ($data[0] as $columnName => $value)
            {
                $placeholder['column_names'][] = $columnName;
                $placeholder['param_names'][] = ':' . $columnName;
            }

            $query = str_replace(':COLUMN_NAMES', join(', ', $placeholder['column_names']), $query);
            $query = str_replace(':PARAM_NAMES', join(', ', $placeholder['param_names']), $query);

            // ----------------------------------

            $response = $this->_prepareInsertReplace($query, $data);

            if (!empty($response))
            {
                return (array)$response;
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param $tableName
         * @param array $conds
         * @param array $data
         * @param null $condsQuery
         *
         * @return bool|null
         * @throws MysqlException
         */
        public function update($tableName, array $conds, array $data, $condsQuery = NULL)
        {
            if (isset($data[0]))
            {
                throw new MysqlException("Multi-dimensional datasets are not allowed.");
            }

            $query = 'UPDATE ' . $tableName . ' SET :PARAMS WHERE :CONDS';

            $placeholder = [
                'params' => [],
                'conds'  => [],
            ];

            foreach ($data as $columnName => $value)
            {
                $placeholder['params'][] = $columnName . ' = :' . $columnName;
            }

            $query = str_replace(':PARAMS', join(', ', $placeholder['params']), $query);

            // ----------------------------------

            if (!empty($conds))
            {
                if ($condsQuery === NULL)
                {
                    $placeholder = [];

                    foreach ($conds as $columnName => $value)
                    {
                        $placeholder[] = $columnName . '= :' . $columnName;
                    }

                    $query = str_replace(':CONDS', join(' AND ', $placeholder), $query);
                }
                else
                {
                    $query = str_replace(':CONDS', $condsQuery, $query);
                }
            }
            else
            {
                $query = str_replace(' WHERE :CONDS', '', $query);
            }

            // ----------------------------------

            $response = $this->_prepareUpdate($query, $conds, $data);

            if ($response === TRUE)
            {
                return TRUE;
            }

            return NULL;
        }

        // ##########################################

        /**
         * @param $tableName
         * @param array $conds
         * @param null $condsQuery
         *
         * @return bool|null
         */
        public function delete($tableName, array $conds = [], $condsQuery = NULL)
        {
            $query = 'DELETE FROM ' . $tableName . ' WHERE :CONDS';

            if (!empty($conds))
            {
                if ($condsQuery === NULL)
                {
                    $placeholder = [];

                    foreach ($conds as $columnName => $value)
                    {
                        $placeholder[] = $columnName . '= :' . $columnName;
                    }

                    $query = str_replace(':CONDS', join(' AND ', $placeholder), $query);
                }
                else
                {
                    $query = str_replace(':CONDS', $condsQuery, $query);
                }
            }
            else
            {
                $query = str_replace(' WHERE :CONDS', '', $query);
            }

            // ----------------------------------

            $response = $this->_prepareDelete($query, $conds);

            if ($response === TRUE)
            {
                return TRUE;
            }

            return NULL;
        }
    }