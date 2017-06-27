<?php
/**
 * @var array $config
 */

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php';

use Simplon\Mysql\Mysql;
use Simplon\Mysql\PDOConnector;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;
use Test\Crud\NameModel;
use Test\Crud\NamesStore;

$pdoConnector = new PDOConnector($config['server'], $config['username'], $config['password'], $config['database']);
$dbh = new Mysql($pdoConnector->connect());

// ############################################

$store = new NamesStore($dbh);

$model = $store->readOne(
    (new ReadQueryBuilder())->addCondition(NameModel::COLUMN_NAME, 'Peter')
);

var_dump([
    $model,
    $model->fromJson(json_encode(['name' => 'Hans', 'age' => 10]), false)->isChanged()
]);