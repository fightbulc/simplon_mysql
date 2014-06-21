<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/UserVo.php';

$config = [
    'server'   => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'dhtest',
];

$mysqlConfigVo = new \Simplon\Mysql\MysqlConfigVo($config);

$dbh = new \Simplon\Mysql\Mysql($mysqlConfigVo);

// ############################################

$sqlCrudManager = new \Simplon\Mysql\Crud\SqlCrudManager($dbh);

//$userVo = (new UserVo())
//    ->setId(null)
//    ->setName('Tino Ehrich')
//    ->setEmail('ehrich@efides.com');
//
///** @var UserVo $userVo */
//$userVo = $sqlCrudManager->create($userVo);
//var_dump($userVo);

// ----------------------------------------------

/** @var UserVo $userVo */
$userVo = $sqlCrudManager->read(new UserVo(), ['itemId' => 1]);
var_dump($userVo);
echo '<hr>';

// ----------------------------------------------

// update
//$userVo->setName('Hansi Hinterseher');
//$userVo = $sqlCrudManager->update($userVo, ['id' => 1]);
//var_dump($userVo);
//echo '<hr>';

// delete
//$response = $sqlCrudManager->delete(UserVo::crudGetSource(), ['id' => 1]);
//var_dump($response);
//echo '<hr>';
