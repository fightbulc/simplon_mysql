<?php

namespace Simplon\Mysql;

class Mysql
{
    /**
     * @var \PDO
     */
    protected $pdo;
    /**
     * @var int
     */
    protected $fetchMode = \PDO::FETCH_ASSOC;
    /**
     * @var \PDOStatement
     */
    protected $lastStatement;

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Mysql
     */
    public function close(): self
    {
        $this->pdo = null;

        return $this;
    }

    /**
     * @param int $fetchMode
     *
     * @return Mysql
     */
    public function setFetchMode(int $fetchMode): self
    {
        $this->fetchMode = $fetchMode;

        return $this;
    }

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        if ($this->hasLastStatement() === false)
        {
            return 0;
        }

        return $this->getLastStatement()->rowCount();
    }

    /**
     * @param string $query
     *
     * @return bool
     * @throws MysqlException
     */
    public function executeSql(string $query): bool
    {
        $dbh = $this->getPdo();

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
    public function selectDb(string $dbName): bool
    {
        return $this->executeSql('use ' . $dbName);
    }

    /**
     * @return bool
     */
    public function transactionBegin(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function transactionCommit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function transactionRollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return null|string
     * @throws MysqlException
     */
    public function fetchColumn(string $query, array $conds = []): ?string
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
    public function fetchColumnMany(string $query, array $conds = []): ?array
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
     * @return null|MysqlQueryIterator
     * @throws MysqlException
     */
    public function fetchColumnManyCursor(string $query, array $conds = []): ?MysqlQueryIterator
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
     * @return array|null
     * @throws MysqlException
     */
    public function fetchRow(string $query, array $conds = []): ?array
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
    public function fetchRowMany(string $query, array $conds = []): ?array
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
     * @return null|MysqlQueryIterator
     * @throws MysqlException
     */
    public function fetchRowManyCursor(string $query, array $conds = []): ?MysqlQueryIterator
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
    public function insert(string $tableName, array $data, bool $insertIgnore = false)
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
    public function insertMany(string $tableName, array $data, bool $insertIgnore = false)
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
    public function replace(string $tableName, array $data)
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
    public function replaceMany(string $tableName, array $data)
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
     * @param null|string $condsQuery
     *
     * @return bool
     * @throws MysqlException
     */
    public function update(string $tableName, array $conds, array $data, ?string $condsQuery = null): bool
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
     * @param null|string $condsQuery
     *
     * @return bool
     * @throws MysqlException
     */
    public function delete(string $tableName, array $conds = [], ?string $condsQuery = null): bool
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
     * @return \PDO
     */
    protected function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @return int
     */
    protected function getFetchMode(): int
    {
        return $this->fetchMode;
    }

    /**
     * @param array $errorInfo
     *
     * @return array
     */
    protected function prepareErrorInfo(array $errorInfo): array
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
    protected function setLastStatement(\PDOStatement $cursor): self
    {
        $this->lastStatement = $cursor;

        return $this;
    }

    /**
     * @return \PDOStatement
     */
    protected function getLastStatement(): \PDOStatement
    {
        return $this->lastStatement;
    }

    /**
     * @return bool
     */
    protected function hasLastStatement(): bool
    {
        return $this->lastStatement !== null;
    }

    /**
     * @return Mysql
     */
    protected function clearLastStatement(): self
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
    protected function getParamType($paramValue): int
    {
        if (is_null($paramValue))
        {
            return \PDO::PARAM_NULL;
        }
        elseif (is_int($paramValue))
        {
            return \PDO::PARAM_INT;
        }
        elseif (is_bool($paramValue))
        {
            return \PDO::PARAM_INT;
        }
        elseif (is_string($paramValue))
        {
            return \PDO::PARAM_STR;
        }
        elseif (is_float($paramValue))
        {
            return \PDO::PARAM_STR;
        }
        elseif (is_double($paramValue))
        {
            return \PDO::PARAM_STR;
        }

        throw new MysqlException("Invalid param type: {$paramValue} with type {gettype($paramValue)}");
    }

    /**
     * @param \PDOStatement $pdoStatement
     * @param array $params
     *
     * @return \PDOStatement
     * @throws MysqlException
     */
    protected function setParams(\PDOStatement $pdoStatement, array $params): \PDOStatement
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
     */
    protected function handleInCondition(string &$query, array &$params): void
    {
        if (!empty($params))
        {
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
        }
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return \PDOStatement
     * @throws MysqlException
     */
    protected function prepareSelect(string $query, array $conds): \PDOStatement
    {
        // clear last statement
        $this->clearLastStatement();

        // handle "in" condition
        $this->handleInCondition($query, $conds);

        // set query
        $pdoStatement = $this->getPdo()->prepare($query);

        // bind named params
        $pdoStatement = $this->setParams($pdoStatement, $conds);

        try
        {
            $pdoStatement->execute();

            if ($pdoStatement->errorCode() === '00000')
            {
                $this->setLastStatement($pdoStatement);

                return $pdoStatement;
            }

            $errorInfo = $this->prepareErrorInfo($pdoStatement->errorInfo());
        }
        catch (\Exception $e)
        {
            $errorInfo = [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
        }

        throw new MysqlException(json_encode([
            'query'     => $query,
            'params'    => $conds,
            'errorInfo' => $errorInfo,
        ]));
    }

    /**
     * @param string $query
     * @param array $rowsMany
     *
     * @return array
     * @throws MysqlException
     */
    protected function prepareInsertReplace(string $query, array $rowsMany): array
    {
        $dbh = $this->getPdo();
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
     * @return bool
     * @throws MysqlException
     */
    protected function prepareUpdate(string $query, array $conds, array $data): bool
    {
        // clear last statement
        $this->clearLastStatement();

        // handle "in" condition
        $this->handleInCondition($query, $conds);

        // set query
        $pdoStatement = $this->getPdo()->prepare($query);

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

        return $this->getRowCount() === 0 ? false : true;
    }

    /**
     * @param string $query
     * @param array $conds
     *
     * @return bool
     * @throws MysqlException
     */
    protected function prepareDelete(string $query, array $conds): bool
    {
        // clear last statement
        $this->clearLastStatement();

        // handle "in" condition
        $this->handleInCondition($query, $conds);

        // set query
        $pdoStatement = $this->getPdo()->prepare($query);

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

        return $this->getRowCount() === 0 ? false : true;
    }

    /**
     * @param string $query
     * @param array $conds
     * @param null|string $condsQuery
     *
     * @return string
     */
    private function buildCondsQuery(string $query, array $conds, ?string $condsQuery = null): string
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
    private function isColum(string $key): bool
    {
        return substr($key, 0, 1) !== '_';
    }
}