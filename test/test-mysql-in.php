<?php

require __DIR__ . '/../vendor/autoload.php';

$config = [
    'server'   => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'beatguide_devel_service',
];

$mysqlConfigVo = new \Simplon\Mysql\MysqlConfigVo($config);

$dbh = new \Simplon\Mysql\Mysql($mysqlConfigVo);

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