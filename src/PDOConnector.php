<?php

namespace Simplon\Mysql;

class PDOConnector
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $user;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $database;
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     */
    public function __construct(string $host, string $user, string $password, string $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
    }

    /**
     * @param string $charset
     * @param array $options
     *
     * @return \PDO
     * @throws \Exception
     */
    public function connect(string $charset = 'utf8', array $options = []): \PDO
    {
        try
        {
            if (!$this->pdo)
            {
                $dns = $this->buildDns($this->host, $this->database, $charset, $options);

                if (empty($options['pdo']))
                {
                    $options['pdo'] = [];
                }

                $this->pdo = new \PDO($dns, $this->user, $this->password, $options['pdo']);
            }

            return $this->pdo;
        }
        catch (\PDOException $e)
        {
            $message = str_replace($this->password, '********', $e->getMessage());
            throw new \Exception($message, $e->getCode());
        }
    }

    /**
     * @param string $host
     * @param string $database
     * @param string $charset
     * @param array $options
     *
     * @return string
     */
    private function buildDns(string $host, string $database, string $charset, array $options): string
    {
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

        return $dns;
    }

}