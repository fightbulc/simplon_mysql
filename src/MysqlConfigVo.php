<?php

namespace Simplon\Mysql;

class MysqlConfigVo
{
    protected $server;
    protected $port;
    protected $database;
    protected $unixSocket;
    protected $username;
    protected $password;
    protected $fetchMode;
    protected $charset;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (!isset($data['fetchMode']))
        {
            $data['fetchMode'] = \PDO::FETCH_ASSOC;
        }

        if (!isset($data['charset']))
        {
            $data['charset'] = 'utf8';
        }

        if (isset($data['server']))
        {
            $this->setServer($data['server']);

            if (isset($data['port']))
            {
                $this->setPort($data['port']);
            }
        }

        if (isset($data['unixSocket']))
        {
            $this->setUnixSocket($data['unixSocket']);
        }

        $this
            ->setDatabase($data['database'])
            ->setUsername($data['username'])
            ->setPassword($data['password'])
            ->setFetchMode($data['fetchMode'])
            ->setCharset($data['charset']);
    }

    /**
     * @param mixed $charset
     *
     * @return MysqlConfigVo
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return (string)$this->charset;
    }

    /**
     * @param mixed $database
     *
     * @return MysqlConfigVo
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return (string)$this->database;
    }

    /**
     * @param mixed $fetchMode
     *
     * @return MysqlConfigVo
     */
    public function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;

        return $this;
    }

    /**
     * @return int
     */
    public function getFetchMode()
    {
        return (int)$this->fetchMode;
    }

    /**
     * @param mixed $password
     *
     * @return MysqlConfigVo
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return (string)$this->password;
    }

    /**
     * @param mixed $server
     *
     * @return MysqlConfigVo
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return string
     */
    public function getServer()
    {
        return (string)$this->server;
    }

    /**
     * @param mixed $port
     *
     * @return MysqlConfigVo
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return (int)$this->port;
    }

    /**
     * @return bool
     */
    public function hasPort()
    {
        return $this->getPort() ? true : false;
    }

    /**
     * @param mixed $unixSocket
     *
     * @return MysqlConfigVo
     */
    public function setUnixSocket($unixSocket)
    {
        $this->unixSocket = $unixSocket;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnixSocket()
    {
        return (string)$this->unixSocket;
    }

    /**
     * @return bool
     */
    public function hasUnixSocket()
    {
        return $this->getUnixSocket() !== '' ? true : false;
    }

    /**
     * @param mixed $username
     *
     * @return MysqlConfigVo
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return (string)$this->username;
    }
} 