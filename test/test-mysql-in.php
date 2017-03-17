<?php
/**
 * @var array $config
 */

use Simplon\Mysql\Mysql;
use Simplon\Mysql\PDOConnector;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/config.php';

$pdoConnector = new PDOConnector($config['server'], $config['username'], $config['password'], $config['database']);
$dbh = new Mysql($pdoConnector->connect());

// ############################################

echo "<h1>IN with integers</h1>";
$conds = ['ids' => [1, 2, 3, 4, 5]];
$query = 'SELECT id, email FROM users WHERE id IN (:ids)';
var_dump($dbh->fetchRowMany($query, $conds));

// ############################################

echo "<h1>IN with strings</h1>";
$conds = ['emails' => ['tino@beatguide.me', 'marin@underplot.com']];
$query = 'SELECT id, email FROM users WHERE email IN (:emails)';
var_dump($dbh->fetchRowMany($query, $conds));