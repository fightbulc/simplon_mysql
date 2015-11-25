<?php

require __DIR__ . '/../../vendor/autoload.php';

use Simplon\Mysql\Crud\CrudManager;
use Test\Crud\SampleStore;

$config = [
    'server'   => '127.0.0.1',
    'username' => 'root',
    'password' => 'root',
    'database' => 'pushcast_devel_app',
];

$dbh = new \Simplon\Mysql\Mysql(
    $config['server'],
    $config['username'],
    $config['password'],
    $config['database']
);

// ############################################

$sampleStorage = new SampleStore(
    $dbh, new CrudManager($dbh)
);

$sampleModel = $sampleStorage->readOne(['email' => 'tino@pushcast.io']);

var_dump($sampleModel);
echo '<hr>';

$sampleModel = $sampleStorage->update(
    $sampleModel->setName('FOO BAR2')
);

var_dump($sampleModel);
echo '<hr>';
