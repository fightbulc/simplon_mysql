<?php

namespace Simplon\Mysql;

class Mysql
{
    /** @var  Mysql */
    protected $dbh;

    /** @var  int */
    protected $fetchMode;

    /** @var  \PDOStatement */
    protected $lastStatement;

    /**
     * @param $host
     * @param $user
     * @param $password
     * @param $database
     * @param int $fetchMode
     * @param string $charset
     * @param array $options
     *
     * @throws MysqlException
     */
    public function __construct($host, $user, $password, $database, $fetchMode = \PDO::FETCH_ASSOC, $charset = 'utf8', array $options = array())
    {
        try
        {
            // use host
            $dns = 'mysql:host=' . $host;

            if (isset($options['port']))
            {
                $dns .= ';port=' . $options['port'];
            }

            // use unix socket
            if (isset($options['unixSocket']))
            {
                $dns = 'mysql:unix_socket=' . $options['unixSocket'];
            }

            $dns .= ';dbname=' . $database;
            $dns .= ';charset=' . $charset;

            // ------------------------------

            // create PDO instance
            $this->setDbh(new \PDO($dns, $user, $password));

            // set fetchMode
            $this->setFetchMode($fetchMode);
        }
        catch (\PDOException $e)
        {
            throw new MysqlException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $dbh
     *
     * @return Mysql
     */
    protected function setDbh($dbh)
    {
        $this->dbh = $dbh;

        return $this;
    }

    /**
     * @return \PDO
     */
    protected function getDbh()
    {
        return $this->dbh;
    }

    /**
     * @return Mysql
     */
    public function close()
    {
        $this->dbh = null;

        return $this;
    }

    /**
     * @param mixed $fetchMode
     *
     * @return Mysql
     */
    protected function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;

        return $this;
    }

    /**
     * @return int
     */
    protected function getFetchMode()
    {
        return (int)$this->fetchMode;
    }

    /**
     * @param array $errorInfo
     *
     * @return array
     */
    protected function prepareErrorInfo(array $errorInfo)
    {
        return array(
            'sqlStateCode' => $errorInfo[0],
            'code'         => $errorInfo[1],
            'message'      => $errorInfo[2],
        );
    }

    /**
     * @param \PDOStatement $cursor
     *
     * @return Mysql
     */
    protected function setLastStatement(\PDOStatement $cursor)
    {
        $this->lastStatement = $cursor;

        return $this;
    }

    /**
     * @return \PDOStatement
     */
    protected function getLastStatement()
    {
        return $this->lastStatement;
    }

    /**
     * @return bool
     */
    protected function hasLastStatement()
    {
        return $this->lastStatement ? true : false;
    }

    /**
     * @return Mysql
     */
    protected function clearLastStatement()
    {
        $this->lastStatement = null;

        return $this;
    }

    /**
     * @param $paramValue
     *
     * @return int
     * @throws MysqlException
     */
    protected function getParamType($paramValue)
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

    /**
     * @param \PDOStatement $pdoStatement
     * @param array $params
     *
     * @return \PDOStatement
     */
    protected function setParams(\PDOStatement $pdoStatement, array $params)
    {
        foreach ($params as $key => &$val)
        {
            $pdoStatement->bindParam($key, $val, $this->getParamType($val));
        }

        return $pdoStatement;
    }

    /**
     * @param $query
     * @param $params
     *
     * @return bool
     */
    protected function handleInCondition(&$query, &$params)
    {
        if (empty($params))
        {
            return true;
        }

        // --------------------------------------

        foreach ($params as $key => $val)
        {
            if (is_array($val))
            {
                $keys = array();

                foreach ($val as $k => $v)
                {
                    // new param name
                    $keyString = ':' . $key . $k;

                    // cache new params
                    $keys[] = $keyString;

                    // add new params
                    $params[$keyString] = $v;
                }

                // include new params
                $query = str_replace(':' . $key, join(',', $keys), $query);

                // remove actual param
                unset($params[$key]);
            }
        }

        return true;
    }

    /**
     * @param $query
     * @param array $conds
     *
     * @return \PDOStatement
     * @throws MysqlException
     */
    protected function prepareSelect($query, array $conds)
    {
        // clear last statement
        $this->clearLastStatement();

        // handle "in" condition
        $this->handleInCondition($query, $conds);

        // set query
        $pdoStatement = $this->getDbh()->prepare($query);

        // bind named params
        $pdoStatement = $this->setParams($pdoStatement, $conds);

        // execute
        $pdoStatement->execute();

        // check for errors
        if ($pdoStatement->errorCode() === '00000')
        {
            // cache statement
            $this->setLastStatement($pdoStatement);

            return $pdoStatement;
        }

        // ----------------------------------

        $error = array(
            'query'     => $query,
            'params'    => $conds,
            'errorInfo' => $this->prepareErrorInfo($pdoStatement->errorInfo()),
        );

        $errorInfo = json_encode($error);

        throw new MysqlException("Houston we have a problem: {$errorInfo}");
    }

    /**
     * @param $query
     * @param array $rowsMany
     *
     * @return array
     * @throws MysqlException
     */
    protected function prepareInsertReplace($query, array $rowsMany)
    {
        $dbh = $this->getDbh();
        $responses = array();

        // clear last statement
        $this->clearLastStatement();

        // set query
        $pdoStatement = $dbh->prepare($query);

        // loop through rows
        while ($row = array_shift($rowsMany))
        {
            // bind params
            $pdoStatement = $this->setParams($pdoStatement, $row);

            // execute
            $pdoStatement->execute();

            // throw errors
            if ($pdoStatement->errorCode() !== '00000')
            {
                $error = array(
                    'query'     => $query,
                    'errorInfo' => $this->prepareErrorInfo($pdoStatement->errorInfo()),
                );

                $errorInfo = json_encode($error);

                throw new MysqlException("Houston we have a problem: {$errorInfo}");
            }

            // last insert|null
            $lastInsert = $dbh->lastInsertId();

            // cache response
            $responses[] = $lastInsert ? (int)$lastInsert : true;
        }

        return $responses;
    }

    /**
     * @param $query
     * @param array $conds
     * @param array $data
     *
     * @return bool
     * @throws MysqlException
     */
    protected function prepareUpdate($query, array $conds, array $data)
    {
        // clear last statement
        $this->clearLastStatement();

        // handle "in" condition
        $this->handleInCondition($query, $conds);

        // set query
        $pdoStatement = $this->getDbh()->prepare($query);

        // bind conds params
        $pdoStatement = $this->setParams($pdoStatement, $conds);

        // bind data params
        $pdoStatement = $this->setParams($pdoStatement, $data);

        // execute
        $pdoStatement->execute();

        // cache statement
        $this->setLastStatement($pdoStatement);

        // throw errors
        if ($pdoStatement->errorCode() !== '00000')
        {
            $error = array(
                'query'     => $query,
                'conds'     => $conds,
                'errorInfo' => $this->prepareErrorInfo($pdoStatement->errorInfo()),
            );

            $errorInfo = json_encode($error);

            throw new MysqlException("Houston we have a problem: {$errorInfo}");
        }

        if ($this->getRowCount() > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * @param $query
     * @param array $conds
     *
     * @return bool
     * @throws MysqlException
     */
    protected function prepareDelete($query, array $conds)
    {
        // clear last statement
        $this->clearLastStatement();

        // handle "in" condition
        $this->handleInCondition($query, $conds);

        // set query
        $pdoStatement = $this->getDbh()->prepare($query);

        // bind conds params
        $pdoStatement = $this->setParams($pdoStatement, $conds);

        // execute
        $pdoStatement->execute();

        // cache statement
        $this->setLastStatement($pdoStatement);

        // throw errors
        if ($pdoStatement->errorCode() !== '00000')
        {
            $error = array(
                'query'     => $query,
                'conds'     => $conds,
                'errorInfo' => $this->prepareErrorInfo($pdoStatement->errorInfo()),
            );

            $errorInfo = json_encode($error);

            throw new MysqlException("Houston we have a problem: {$errorInfo}");
        }

        if ($this->getRowCount() > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getRowCount()
    {
        if ($this->hasLastStatement() !== false)
        {
            return $this->getLastStatement()->rowCount();
        }

        return false;
    }

    /**
     * @param $query
     *
     * @return bool
     * @throws MysqlException
     */
    public function executeSql($query)
    {
        $dbh = $this->getDbh();

        $response = $dbh->exec($query);

        if ($response !== false)
        {
            return true;
        }

        $error = array(
            'query'     => $query,
            'errorInfo' => $this->prepareErrorInfo($dbh->errorInfo()),
        );

        $errorInfo = json_encode($error);

        throw new MysqlException("Houston we have a problem: {$errorInfo}");
    }

    /**
     * @param $dbName
     *
     * @return bool
     * @throws MysqlException
     */
    public function selectDb($dbName)
    {
        return $this->executeSql('use ' . $dbName);
    }

    /**
     * @param $query
     * @param array $conds
     *
     * @return false|string
     */
    public function fetchColumn($query, array $conds = array())
    {
        $response = $this->prepareSelect($query, $conds)->fetchColumn();

        if ($response !== false)
        {
            return (string)$response;
        }

        return false;
    }

    /**
     * @param $query
     * @param array $conds
     *
     * @return array|bool
     */
    public function fetchColumnMany($query, array $conds = array())
    {
        $responsesMany = array();
        $pdoStatment = $this->prepareSelect($query, $conds);

        while ($response = $pdoStatment->fetchColumn())
        {
            $responsesMany[] = $response;
        }

        if (!empty($responsesMany))
        {
            return (array)$responsesMany;
        }

        return false;
    }

    /**
     * @param $query
     * @param array $conds
     *
     * @return MysqlQueryIterator
     */
    public function fetchColumnManyCursor($query, array $conds = array())
    {
        $this->prepareSelect($query, $conds);

        // ----------------------------------

        return new MysqlQueryIterator($this->getLastStatement(), 'fetchColumn');
    }

    /**
     * @param $query
     * @param array $conds
     *
     * @return array|bool
     */
    public function fetchRow($query, array $conds = array())
    {
        $response = $this->prepareSelect($query, $conds)->fetch($this->getFetchMode());

        if ($response !== false)
        {
            return (array)$response;
        }

        return false;
    }

    /**
     * @param $query
     * @param array $conds
     *
     * @return array|bool
     */
    public function fetchRowMany($query, array $conds = array())
    {
        $responsesMany = array();
        $pdoStatment = $this->prepareSelect($query, $conds);

        while ($response = $pdoStatment->fetch($this->getFetchMode()))
        {
            $responsesMany[] = $response;
        }

        if (!empty($responsesMany))
        {
            return (array)$responsesMany;
        }

        return false;
    }

    /**
     * @param $query
     * @param array $conds
     *
     * @return MysqlQueryIterator
     */
    public function fetchRowManyCursor($query, array $conds = array())
    {
        $this->prepareSelect($query, $conds);

        // ----------------------------------

        return new MysqlQueryIterator($this->getLastStatement(), 'fetch');
    }

    /**
     * @param $tableName
     * @param array $data
     * @param bool $insertIgnore
     *
     * @return int|bool
     * @throws MysqlException
     */
    public function insert($tableName, array $data, $insertIgnore = false)
    {
        if (isset($data[0]))
        {
            throw new MysqlException("Multi-dimensional datasets are not allowed. Use 'Mysql::insertMany()' instead");
        }

        $response = $this->insertMany($tableName, array($data), $insertIgnore);

        if ($response !== false)
        {
            return array_pop($response);
        }

        return false;
    }

    /**
     * @param $tableName
     * @param array $data
     * @param bool $insertIgnore
     *
     * @return array|bool
     * @throws MysqlException
     */
    public function insertMany($tableName, array $data, $insertIgnore = false)
    {
        if (!isset($data[0]))
        {
            throw new MysqlException("One-dimensional datasets are not allowed. Use 'Mysql::insert()' instead");
        }

        $query = 'INSERT' . ($insertIgnore === true ? ' IGNORE ' : null) . ' INTO ' . $tableName . ' (:COLUMN_NAMES) VALUES (:PARAM_NAMES)';

        $placeholder = array(
            'column_names' => array(),
            'param_names'  => array(),
        );

        foreach ($data[0] as $columnName => $value)
        {
            $placeholder['column_names'][] = $columnName;
            $placeholder['param_names'][] = ':' . $columnName;
        }

        $query = str_replace(':COLUMN_NAMES', join(', ', $placeholder['column_names']), $query);
        $query = str_replace(':PARAM_NAMES', join(', ', $placeholder['param_names']), $query);

        // ----------------------------------

        $response = $this->prepareInsertReplace($query, $data);

        if (!empty($response))
        {
            return (array)$response;
        }

        return false;
    }

    /**
     * @param $tableName
     * @param array $data
     *
     * @return array|bool
     * @throws MysqlException
     */
    public function replace($tableName, array $data)
    {
        if (isset($data[0]))
        {
            throw new MysqlException("Multi-dimensional datasets are not allowed. Use 'Mysql::replaceMany()' instead");
        }

        return $this->replaceMany($tableName, array($data));
    }

    /**
     * @param $tableName
     * @param array $data
     *
     * @return array|bool
     * @throws MysqlException
     */
    public function replaceMany($tableName, array $data)
    {
        if (!isset($data[0]))
        {
            throw new MysqlException("One-dimensional datasets are not allowed. Use 'Mysql::replace()' instead");
        }

        $query = 'REPLACE INTO ' . $tableName . ' (:COLUMN_NAMES) VALUES (:PARAM_NAMES)';

        $placeholder = array(
            'column_names' => array(),
            'param_names'  => array(),
        );

        foreach ($data[0] as $columnName => $value)
        {
            $placeholder['column_names'][] = $columnName;
            $placeholder['param_names'][] = ':' . $columnName;
        }

        $query = str_replace(':COLUMN_NAMES', join(', ', $placeholder['column_names']), $query);
        $query = str_replace(':PARAM_NAMES', join(', ', $placeholder['param_names']), $query);

        // ----------------------------------

        $response = $this->prepareInsertReplace($query, $data);

        if (!empty($response))
        {
            return (array)$response;
        }

        return false;
    }

    /**
     * @param $tableName
     * @param array $conds
     * @param array $data
     * @param null $condsQuery
     *
     * @return bool
     * @throws MysqlException
     */
    public function update($tableName, array $conds, array $data, $condsQuery = null)
    {
        if (isset($data[0]))
        {
            throw new MysqlException("Multi-dimensional datasets are not allowed.");
        }

        $query = 'UPDATE ' . $tableName . ' SET :PARAMS WHERE :CONDS';

        $placeholder = array(
            'params' => array(),
            'conds'  => array(),
        );

        foreach ($data as $columnName => $value)
        {
            $placeholder['params'][] = $columnName . ' = :' . $columnName;
        }

        $query = str_replace(':PARAMS', join(', ', $placeholder['params']), $query);

        // ----------------------------------

        if (!empty($conds))
        {
            if ($condsQuery === null)
            {
                $placeholder = array();

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

        $response = $this->prepareUpdate($query, $conds, $data);

        if ($response === true)
        {
            return true;
        }

        return false;
    }

    /**
     * @param $tableName
     * @param array $conds
     * @param null $condsQuery
     *
     * @return bool
     */
    public function delete($tableName, array $conds = array(), $condsQuery = null)
    {
        $query = 'DELETE FROM ' . $tableName . ' WHERE :CONDS';

        if (!empty($conds))
        {
            if ($condsQuery === null)
            {
                $placeholder = array();

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

        $response = $this->prepareDelete($query, $conds);

        if ($response === true)
        {
            return true;
        }

        return false;
    }
}