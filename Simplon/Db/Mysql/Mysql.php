<?php

    namespace Simplon\Db\Mysql;

    class Mysql
    {
        protected $_dbh;
        protected $_fetchMode;
        protected $_hasCursor;

        /** @var  MysqlConfigVo */
        protected $_mysqlConfigVo;

        /** @var  \PDOStatement */
        protected $_lastStatement;

        // ##########################################

        /**
         * @param MysqlConfigVo $mysqlConfigVo
         */
        public function __construct(MysqlConfigVo $mysqlConfigVo)
        {
            // set config
            $this->_setMysqlConfigVo($mysqlConfigVo);

            // set database handle
            $this->_setDbh($this->_connect());

            // run init
            $this->_init();
        }

        // ##########################################

        /**
         * @param MysqlConfigVo $mysqlConfigVo
         *
         * @return Mysql
         */
        protected function _setMysqlConfigVo(MysqlConfigVo $mysqlConfigVo)
        {
            $this->_mysqlConfigVo = $mysqlConfigVo;

            return $this;
        }

        // ##########################################

        /**
         * @return MysqlConfigVo
         * @throws MysqlException
         */
        protected function _getMysqlConfigVo()
        {
            if ($this->_mysqlConfigVo)
            {
                return $this->_mysqlConfigVo;
            }

            throw new MysqlException('Missing MysqlConfigVo');
        }

        // ##########################################

        /**
         * @return \PDO
         * @throws MysqlException
         */
        protected function _connect()
        {
            try
            {
                $mysqlConfigVo = $this->_getMysqlConfigVo();

                return new \PDO(
                    'mysql:host=' . $mysqlConfigVo->getServer() . ';dbname=' . $mysqlConfigVo->getDatabase(),
                    $mysqlConfigVo->getUsername(),
                    $mysqlConfigVo->getPassword()
                );
            }
            catch (\PDOException $e)
            {
                throw new MysqlException($e->getMessage(), $e->getCode());
            }
        }

        // ##########################################

        /**
         * @return Mysql
         */
        protected function _init()
        {
            $mysqlConfigVo = $this->_getMysqlConfigVo();

            // set charset
            $this->_exec("SET NAMES '{$mysqlConfigVo->getCharset()}' COLLATE '{$mysqlConfigVo->getCollate()}'");

            // set fetchMode
            $this->_setFetchMode($mysqlConfigVo->getFetchMode());

            return $this;
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
         * @param $query
         *
         * @return int
         */
        protected function _exec($query)
        {
            return $this->_getDbh()
                ->exec($query);
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
         * @param mixed $hasCursor
         *
         * @return Mysql
         */
        protected function _setHasCursor($hasCursor)
        {
            $this->_hasCursor = $hasCursor;

            return $this;
        }

        // ##########################################

        /**
         * @return bool
         */
        protected function _getHasCursor()
        {
            return $this->_hasCursor ? TRUE : FALSE;
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
        protected function _prepareQuery($query, array $params)
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
         * @param array $conds
         *
         * @return null|string
         */
        public function fetchValue($query, array $conds)
        {
            $response = $this->_prepareQuery($query, $conds)
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
         * @return string
         */
        public function fetchValueMany($query, array $conds)
        {
            $responsesMany = [];
            $pdoStatment = $this->_prepareQuery($query, $conds);

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
         * @return string
         */
        public function fetchValueManyCursor($query, array $conds)
        {
            if ($this->_getHasCursor() === FALSE)
            {
                $this->_setHasCursor(TRUE);
                $this->_prepareQuery($query, $conds);
            }

            // ----------------------------------

            $response = $this->_getLastStatement()
                ->fetchColumn();

            if ($response)
            {
                return (string)$response;
            }

            // ----------------------------------

            $this->_setHasCursor(FALSE);

            return NULL;
        }

        // ##########################################

        /**
         * @param $query
         * @param array $conds
         *
         * @return array|null
         */
        public function fetch($query, array $conds)
        {
            $response = $this->_prepareQuery($query, $conds)
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
        public function fetchMany($query, array $conds)
        {
            $responsesMany = [];
            $pdoStatment = $this->_prepareQuery($query, $conds);

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
         * @return string
         */
        public function fetchManyCursor($query, array $conds)
        {
            if ($this->_getHasCursor() === FALSE)
            {
                $this->_setHasCursor(TRUE);
                $this->_prepareQuery($query, $conds);
            }

            // ----------------------------------

            $response = $this->_getLastStatement()
                ->fetch();

            if ($response)
            {
                return (array)$response;
            }

            // ----------------------------------

            $this->_setHasCursor(FALSE);

            return NULL;
        }
    }