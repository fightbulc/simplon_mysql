<?php

namespace Simplon\Mysql;

/**
 * Class Mysql
 * @package Simplon\Mysql
 */
class Mysql
{
    /**
     * @var \PDO
     */
    protected $dbh;

    /**
     * @var int
     */
    protected $fetchMode;

    /**
     * @var \PDOStatement
     */
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
    public function __construct($host, $user, $password, $database, $fetchMode = \PDO::FETCH_ASSOC, $charset = 'utf8', array $options = [])
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

            if (empty($options['pdo']))
            {
                $options['pdo'] = [];
            }

            $this->setDbh(new \PDO($dns, $user, $password, $options['pdo']));

            // set fetchMode
            $this->setFetchMode($fetchMode);
        }
        catch (\PDOException $e)
        {
            $message = str_replace($password, '********', $e->getMessage());
            throw new MysqlException($message, $e->getCode());
        }
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
     * @return bool|int
     */
    public function getRowCount()
    {
        if ($this->hasLastStatement() === false)
        {
            return false;
        }

        return $this->getLastStatement()->rowCount();
    }

    /**
     * @param string $query
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

        $error = [
            'query'     => $query,
            'errorInfo' => $this->prepareErrorInfo($dbh->errorInfo()),
        ];

        $errorInfo = json_encode($error);

        throw new MysqlException($errorInfo);
    }

    /**
     * @param string $dbName
     *
     * @return bool
     * @throws MysqlException
     */
    public function selectDb($dbName)
    {
        return $this->executeSql('use ' . $dbName);
    }

    /**
     * @return bool
     */
    public function transactionBegin()
    {
        return $this->dbh->beginTransaction();
    }

    /**
     * @return bool
     */
    public function transactionCommit()
    {
        return $this->dbh->commit();
    }

    /**
     * @return bool
     */
    public function transactionRollback()
    {
        return $this->dbh->rollBack();
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return null|string
     * @throws MysqlException
     */
    public function fetchColumn($query, array $conds = [])
    {
        $response = $this->prepareSelect($query, $conds)->fetchColumn();

        if ($response === false)
        {
            return null;
        }

        return (string)$response;
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return array|null
     * @throws MysqlException
     */
    public function fetchColumnMany($query, array $conds = [])
    {
        $responsesMany = [];
        $pdoStatment = $this->prepareSelect($query, $conds);

        while ($response = $pdoStatment->fetchColumn())
        {
            $responsesMany[] = $response;
        }

        if (empty($responsesMany))
        {
            return null;
        }

        return (array)$responsesMany;
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return MysqlQueryIterator|null
     * @throws MysqlException
     */
    public function fetchColumnManyCursor($query, array $conds = [])
    {
        $this->prepareSelect($query, $conds);

        $cursor = new MysqlQueryIterator($this->getLastStatement(), 'fetchColumn');

        if ($cursor === false)
        {
            return null;
        }

        return $cursor;
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return mixed
     * @throws MysqlException
     */
    public function fetchRow($query, array $conds = [])
    {
        $response = $this->prepareSelect($query, $conds)->fetch($this->getFetchMode());

        if ($response === false)
        {
            return null;
        }

        return $response;
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return array|null
     * @throws MysqlException
     */
    public function fetchRowMany($query, array $conds = [])
    {
        $responsesMany = [];
        $pdoStatment = $this->prepareSelect($query, $conds);

        while ($response = $pdoStatment->fetch($this->getFetchMode()))
        {
            $responsesMany[] = $response;
        }

        if (empty($responsesMany))
        {
            return null;
        }

        return (array)$responsesMany;
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return MysqlQueryIterator|null
     * @throws MysqlException
     */
    public function fetchRowManyCursor($query, array $conds = [])
    {
        $this->prepareSelect($query, $conds);

        $cursor = new MysqlQueryIterator($this->getLastStatement(), 'fetch');

        if ($cursor === false)
        {
            return null;
        }

        return $cursor;
    }

    /**
     * @param string $tableName
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

        $response = $this->insertMany($tableName, [$data], $insertIgnore);

        if ($response === false)
        {
            return false;
        }

        return array_pop($response);
    }

    /**
     * @param string $tableName
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

        $placeholder = [
            'column_names' => [],
            'param_names'  => [],
        ];

        foreach ($data[0] as $columnName => $value)
        {
            $placeholder['column_names'][] = '`' . $columnName . '`';
            $placeholder['param_names'][] = ':' . $columnName;
        }

        $query = str_replace(':COLUMN_NAMES', join(', ', $placeholder['column_names']), $query);
        $query = str_replace(':PARAM_NAMES', join(', ', $placeholder['param_names']), $query);

        // ----------------------------------

        $response = $this->prepareInsertReplace($query, $data);

        if (empty($response))
        {
            return false;
        }

        return (array)$response;
    }

    /**
     * @param string $tableName
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

        return $this->replaceMany($tableName, [$data]);
    }

    /**
     * @param string $tableName
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

        $placeholder = [
            'column_names' => [],
            'param_names'  => [],
        ];

        foreach ($data[0] as $columnName => $value)
        {
            $placeholder['column_names'][] = '`' . $columnName . '`';
            $placeholder['param_names'][] = ':' . $columnName;
        }

        $query = str_replace(':COLUMN_NAMES', join(', ', $placeholder['column_names']), $query);
        $query = str_replace(':PARAM_NAMES', join(', ', $placeholder['param_names']), $query);

        // ----------------------------------

        $response = $this->prepareInsertReplace($query, $data);

        if (empty($response))
        {
            return false;
        }

        return (array)$response;
    }

    /**
     * @param string $tableName
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

        $placeholder = [
            'params' => [],
            'conds'  => [],
        ];

        foreach ($data as $columnName => $value)
        {
            $placeholder['params'][] = '`' . $columnName . '` = :DATA_' . $columnName;

            // mark data keys in case CONDS and DATA hold the same keys
            unset($data[$columnName]);
            $data['DATA_' . $columnName] = $value;
        }

        $query = str_replace(':PARAMS', join(', ', $placeholder['params']), $query);
        $query = $this->buildCondsQuery($query, $conds, $condsQuery);

        return $this->prepareUpdate($query, $conds, $data);
    }

    /**
     * @param string $tableName
     * @param array $conds
     * @param null $condsQuery
     *
     * @return bool
     * @throws MysqlException
     */
    public function delete($tableName, array $conds = [], $condsQuery = null)
    {
        $query = $this->buildCondsQuery('DELETE FROM ' . $tableName . ' WHERE :CONDS', $conds, $condsQuery);
        $response = $this->prepareDelete($query, $conds);

        if ($response === true)
        {
            return true;
        }

        return false;
    }

    /**
     * @param int $fetchMode
     *
     * @return Mysql
     */
    protected function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;

        return $this;
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
        return [
            'sqlStateCode' => $errorInfo[0],
            'code'         => $errorInfo[1],
            'message'      => $errorInfo[2],
        ];
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
     * @param mixed $paramValue
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
     * @throws MysqlException
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
     * @param string $query
     * @param array $params
     *
     * @return bool
     */
    protected function handleInCondition(&$query, &$params)
    {
        if (empty($params))
        {
            return true;
        }

        foreach ($params as $key => $val)
        {
            if (is_array($val))
            {
                $keys = [];

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
     * @param string $query
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
        if ($pdoStatement->errorCode() !== '00000')
        {
            $error = [
                'query'     => $query,
                'params'    => $conds,
                'errorInfo' => $this->prepareErrorInfo($pdoStatement->errorInfo()),
            ];

            $errorInfo = json_encode($error);

            throw new MysqlException($errorInfo);
        }

        // cache statement
        $this->setLastStatement($pdoStatement);

        return $pdoStatement;
    }

    /**
     * @param string $query
     * @param array $rowsMany
     *
     * @return array
     * @throws MysqlException
     */
    protected function prepareInsertReplace($query, array $rowsMany)
    {
        $dbh = $this->getDbh();
        $responses = [];

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
                $error = [
                    'query'     => $query,
                    'errorInfo' => $this->prepareErrorInfo($pdoStatement->errorInfo()),
                ];

                $errorInfo = json_encode($error);

                throw new MysqlException($errorInfo);
            }

            // last insert|null
            $lastInsert = $dbh->lastInsertId();

            // cache response
            $responses[] = $lastInsert ? (int)$lastInsert : true;
        }

        return $responses;
    }

    /**
     * @param string $query
     * @param array $conds
     * @param array $data
     *
     * @return null|bool
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
            $error = [
                'query'     => $query,
                'conds'     => $conds,
                'errorInfo' => $this->prepareErrorInfo($pdoStatement->errorInfo()),
            ];

            $errorInfo = json_encode($error);

            throw new MysqlException($errorInfo);
        }

        if ($this->getRowCount() === 0)
        {
            return null;
        }

        return true;
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return null|bool
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
            $error = [
                'query'     => $query,
                'conds'     => $conds,
                'errorInfo' => $this->prepareErrorInfo($pdoStatement->errorInfo()),
            ];

            $errorInfo = json_encode($error);

            throw new MysqlException($errorInfo);
        }

        if ($this->getRowCount() === 0)
        {
            return null;
        }

        return true;
    }

    /**
     * @param string $query
     * @param array $conds
     * @param string|null $condsQuery
     *
     * @return string
     */
    private function buildCondsQuery($query, array $conds, $condsQuery = null)
    {
        if (!empty($conds))
        {
            if ($condsQuery === null)
            {
                $placeholder = [];

                foreach ($conds as $columnName => $value)
                {
                    if ($this->isColum($columnName))
                    {
                        $placeholder[] = '`' . $columnName . '` = :' . $columnName;
                    }
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

        return $query;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isColum($key)
    {
        return substr($key, 0, 1) !== '_';
    }
}