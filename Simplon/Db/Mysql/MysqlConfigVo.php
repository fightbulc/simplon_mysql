<?php

    namespace Simplon\Db\Mysql;

    class MysqlConfigVo
    {
        protected $_server;
        protected $_database;
        protected $_username;
        protected $_password;
        protected $_charset = 'utf8';
        protected $_collate = 'utf8_unicode_ci';
        protected $_fetchMode = \PDO::FETCH_ASSOC;

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
         * @return mixed
         */
        public function getDatabase()
        {
            return $this->_database;
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
         * @return mixed
         */
        public function getPassword()
        {
            return $this->_password;
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
         * @return mixed
         */
        public function getServer()
        {
            return $this->_server;
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
         * @return mixed
         */
        public function getUsername()
        {
            return $this->_username;
        }

        // ######################################

        /**
         * @param string $charset
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
         * @param string $collate
         *
         * @return MysqlConfigVo
         */
        public function setCollate($collate)
        {
            $this->_collate = $collate;

            return $this;
        }

        // ######################################

        /**
         * @return string
         */
        public function getCollate()
        {
            return (string)$this->_collate;
        }

        // ######################################

        /**
         * @param int $fetchMode
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
    }
