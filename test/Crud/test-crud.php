<?php
/**
 * @var array $config
 */

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/config.php';

use Simplon\Mysql\Mysql;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;
use Test\Crud\SampleModel;
use Test\Crud\SampleStore;

$dbh = new Mysql(
    $config['server'],
    $config['username'],
    $config['password'],
    $config['database']
);

// ############################################

$store = new SampleStore($dbh);

//$sampleModel = $store->create(
//    (new CreateQueryBuilder())->setModel(
//        (new SampleModel())
//            ->setName('Foo bar')
//            ->setEmail('foo@bar.com')
//            ->setPasswordHash('12345')
//            ->setPubToken('XXXXX')
//            ->setTimeZone('Europe/Berlin')
//    )
//);

$model = $store->read(
    (new ReadQueryBuilder())->addCondition(SampleModel::COLUMN_EMAIL, ['tino@pushcast.io', 'foo@bar.com'])
);

//$model = $store->readOne(
//    (new ReadQueryBuilder())->addCondition(SampleModel::COLUMN_EMAIL, 'foo@bar.com')
//);

var_dump($model);