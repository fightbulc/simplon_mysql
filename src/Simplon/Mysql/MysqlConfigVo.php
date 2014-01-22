<?php

    namespace Simplon\Mysql;

    class MysqlConfigVo
    {
        protected $_server;
        protected $_port;
        protected $_database;
        protected $_unixSocket;
        protected $_username;
        protected $_password;
        protected $_fetchMode;
        protected $_charset;

        // ######################################

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

        // ######################################

        /**
         * @param mixed $charset
         *
         * @return MysqlConfigVo
         */
        public function setCharset($charset)
        {
            $this->_charset = $charset;

            return $this;
        }

        // ######################################

        /**
         * @return string
         */
        public function getCharset()
        {
            return (string)$this->_charset;
        }

        // ######################################

        /**
         * @param mixed $database
         *
         * @return MysqlConfigVo
         */
        public function setDatabase($database)
        {
            $this->_database = $database;

            return $this;
        }

        // ######################################

        /**
         * @return string
         */
        public function getDatabase()
        {
            return (string)$this->_database;
        }

        // ######################################

        /**
         * @param mixed $fetchMode
         *
         * @return MysqlConfigVo
         */
        public function setFetchMode($fetchMode)
        {
            $this->_fetchMode = $fetchMode;

            return $this;
        }

        // ######################################

        /**
         * @return int
         */
        public function getFetchMode()
        {
            return (int)$this->_fetchMode;
        }

        // ######################################

        /**
         * @param mixed $password
         *
         * @return MysqlConfigVo
         */
        public function setPassword($password)
        {
            $this->_password = $password;

            return $this;
        }

        // ######################################

        /**
         * @return string
         */
        public function getPassword()
        {
            return (string)$this->_password;
        }

        // ######################################

        /**
         * @param mixed $server
         *
         * @return MysqlConfigVo
         */
        public function setServer($server)
        {
            $this->_server = $server;

            return $this;
        }

        // ######################################

        /**
         * @return string
         */
        public function getServer()
        {
            return (string)$this->_server;
        }

        // ######################################

        /**
         * @param mixed $port
         *
         * @return MysqlConfigVo
         */
        public function setPort($port)
        {
            $this->_port = $port;

            return $this;
        }

        // ######################################

        /**
         * @return int
         */
        public function getPort()
        {
            return (int)$this->_port;
        }

        // ######################################

        /**
         * @return bool
         */
        public function hasPort()
        {
            return $this->getPort() ? TRUE : FALSE;
        }

        // ######################################

        /**
         * @param mixed $unixSocket
         *
         * @return MysqlConfigVo
         */
        public function setUnixSocket($unixSocket)
        {
            $this->_unixSocket = $unixSocket;

            return $this;
        }

        // ######################################

        /**
         * @return string
         */
        public function getUnixSocket()
        {
            return (string)$this->_unixSocket;
        }

        // ######################################

        /**
         * @return bool
         */
        public function hasUnixSocket()
        {
            return $this->getUnixSocket() !== '' ? TRUE : FALSE;
        }

        // ######################################

        /**
         * @param mixed $username
         *
         * @return MysqlConfigVo
         */
        public function setUsername($username)
        {
            $this->_username = $username;

            return $this;
        }

        // ######################################

        /**
         * @return string
         */
        public function getUsername()
        {
            return (string)$this->_username;
        }
    } 