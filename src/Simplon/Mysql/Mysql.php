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
         * @param $server
         * @param $database
         * @param $username
         * @param $password
         * @param int $fetchMode
         * @param string $charset
         * @param string $collate
         *
         * @throws MysqlException
         */
        public function __construct($server, $database, $username, $password, $fetchMode = \PDO::FETCH_ASSOC, $charset = 'utf8', $collate = 'utf8_unicode_ci')
        {
            try
            {
                // create PDO instance
                $this->_setDbh(new \PDO('mysql:host=' . $server . ';dbname=' . $database, $username, $password));

                // set fetchMode
                $this->_setFetchMode($fetchMode);

                // set charset
                $this->executeSql("SET NAMES '{$charset}' COLLATE '{$collate}'");
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

            $errorInfo = json_encode($this->_prepareErrorInfo($pdoStatement->errorInfo()));
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
                    $errorInfo = json_encode($this->_prepareErrorInfo($pdoStatement->errorInfo()));
                    throw new MysqlException("Houston we have a problem: {$errorInfo}");
                }

                // last insert|bool
                $lastInsert = $dbh->lastInsertId();

                // cache response
                $responses[] = $lastInsert ? ['id' => (int)$lastInsert] : TRUE;
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
                $errorInfo = json_encode($this->_prepareErrorInfo($pdoStatement->errorInfo()));
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
                $errorInfo = json_encode($this->_prepareErrorInfo($pdoStatement->errorInfo()));
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

            $errorInfo = json_encode($this->_prepareErrorInfo($dbh->errorInfo()));
            throw new MysqlException("Houston we have a problem: {$errorInfo}");
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return null|string
         */
        public function fetchValue($query, array $conds = [])
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
        public function fetchValueMany($query, array $conds = [])
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
        public function fetchValueManyCursor($query, array $conds = [])
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
        public function fetch($query, array $conds = [])
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
         * @return string
         */
        public function fetchMany($query, array $conds = [])
        {
            $responsesMany = [];
            $pdoStatment = $this->_prepareSelect($query, $conds);

            while ($response = $pdoStatment->fetch())
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
        public function fetchManyCursor($query, array $conds = [])
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
         * @return array|null
         * @throws MysqlException
         */
        public function insert($tableName, array $data, $insertIgnore = FALSE)
        {
            if (isset($data[0]))
            {
                throw new MysqlException("Multi-dimensional datasets are not allowed. Use 'Mysql::insertMany()' instead");
            }

            return $this->insertMany($tableName, [$data], $insertIgnore);
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

        // ######################################

        /**
         * @param $value
         * @param string $default
         *
         * @return string
         */
        protected function _getConditionOperator($value, $default = '=')
        {
            if (is_array($value))
            {
                $default = trim($value['opr']);
            }

            return $default;
        }

        // ######################################

        /**
         * @param array $values
         *
         * @return array
         */
        protected function _prepareConditionValues(array $values)
        {
            foreach ($values as $column => $value)
            {
                if (is_array($value))
                {
                    $values[$column] = trim($value['val']);
                }
            }

            return $values;
        }

        // ##########################################

        /**
         * @param $tableName
         * @param array $conds
         * @param array $data
         *
         * @return bool
         * @throws MysqlException
         */
        public function update($tableName, array $conds, array $data)
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
                foreach ($conds as $columnName => $value)
                {
                    $placeholder['conds'][] = $columnName . ' ' . $this->_getConditionOperator($value) . ' :' . $columnName;
                }

                $query = str_replace(':CONDS', join(', ', $placeholder['conds']), $query);
            }
            else
            {
                $query = str_replace(' WHERE :CONDS', '', $query);
            }

            // ----------------------------------

            $response = $this->_prepareUpdate($query, $this->_prepareConditionValues($conds), $data);

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
         *
         * @return bool
         */
        public function delete($tableName, array $conds = [])
        {
            $query = 'DELETE FROM ' . $tableName . ' WHERE :CONDS';

            if (!empty($conds))
            {
                $placeholder = [];

                foreach ($conds as $columnName => $value)
                {
                    $placeholder[] = $columnName . ' ' . $this->_getConditionOperator($value) . ' :' . $columnName;
                }

                $query = str_replace(':CONDS', join(', ', $placeholder), $query);
            }
            else
            {
                $query = str_replace(' WHERE :CONDS', '', $query);
            }

            // ----------------------------------

            $response = $this->_prepareDelete($query, $this->_prepareConditionValues($conds));

            if ($response === TRUE)
            {
                return TRUE;
            }

            return NULL;
        }
    }