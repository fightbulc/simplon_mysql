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

$query = 'SELECT * FROM events WHERE venue_id = :venueId LIMIT 10';
$conds = array('venueId' => 23);

// ############################################

echo '<h3>fetchValue</h3>';
$results = $dbh->fetchColumn($query, $conds);
var_dump($results);

// ############################################

echo '<h3>fetchValueMany</h3>';
$results = $dbh->fetchColumnMany($query, $conds);
echo '<h4>total rows: ' . $dbh->getRowCount() . '</h4>';
var_dump($results);

// ############################################

echo '<h3>fetchValueManyCursor</h3>';

$counter = 0;
foreach ($dbh->fetchColumnManyCursor($query, $conds) as $result)
{
    echo '<h4>#' . (++$counter) . ' cursor</h4>';
    var_dump($result);
}

// ############################################

echo '<h3>fetch</h3>';
$results = $dbh->fetchRow($query, $conds);
var_dump($results);

// ############################################

echo '<h3>fetchMany</h3>';
$results = $dbh->fetchRowMany($query, $conds);
var_dump($results);

// ############################################

echo '<h3>fetchManyCursor</h3>';

$counter = 0;
foreach ($dbh->fetchRowManyCursor($query, $conds) as $result)
{
    echo '<h4>#' . (++$counter) . ' cursor</h4>';
    var_dump($result);
}

// ############################################

echo '<h3>execute sql: truncate</h3>';
$response = $dbh->executeSql('TRUNCATE import_dump');
var_dump($response);

// ############################################

echo '<h3>insert</h3>';

echo '<h4>with ID response</h4>';
$data = [
    'id'   => null,
    'dump' => '{"message":"#One"}',
];
$results = $dbh->insert('import_dump', $data);
var_dump($results);

echo '<h4>with NO-ID response</h4>';
$data = [
    'dump' => '{"message":"#One"}',
];
$results = $dbh->insert('import_dump_no_id', $data);
var_dump($results);

// ############################################

echo '<h3>insertMany</h3>';

echo '<h4>with ID response</h4>';
$data = [
    [
        'id'   => null,
        'dump' => '{"message":"Hello"}',
    ],
    [
        'id'   => null,
        'dump' => '{"message":"Foo"}',
    ],
    [
        'id'   => null,
        'dump' => '{"message":"Bar"}',
    ],
];
$results = $dbh->insertMany('import_dump', $data);
var_dump($results);

echo '<h4>with NO-ID response</h4>';
$data = [
    [
        'dump' => '{"message":"Hello"}',
    ],
    [
        'dump' => '{"message":"Foo"}',
    ],
    [
        'dump' => '{"message":"Bar"}',
    ],
];
$results = $dbh->insertMany('import_dump_no_id', $data);
var_dump($results);

// ############################################

echo '<h3>update</h3>';
$conds = ['id' => 1];
$data = ['dump' => '{"message":"Hello Dad"}'];
$results = $dbh->update('import_dump', $conds, $data);
var_dump($results);

// ############################################

echo '<h3>replace</h3>';
$data = [
    'id'   => 1,
    'dump' => '{"message":"#Two"}',
];
$results = $dbh->replace('import_dump', $data);
var_dump($results);

// ############################################

echo '<h3>replaceMany</h3>';
$data = [
    [
        'id'   => 2,
        'dump' => '{"message":"Hello Mum"}'
    ],
    [
        'id'   => 3,
        'dump' => '{"message":"Booooh!"}'
    ],
];
$results = $dbh->replaceMany('import_dump', $data);
var_dump($results);

// ############################################

echo '<h3>delete</h3>';
$conds = [
    'id' => 3,
];
$condsQuery = 'id = :id';
$results = $dbh->delete('import_dump', $conds, $condsQuery);
var_dump($results);
