<?php

    require __DIR__ . '/../vendor/autoload.php';

    $mysqlConfigVo = (new \Simplon\Mysql\MysqlConfigVo())
        ->setServer('localhost')
        ->setDatabase('beatguide_devel_service')
        ->setUsername('rootuser')
        ->setPassword('rootuser');

    $dbh = new \Simplon\Mysql\Mysql($mysqlConfigVo);
    $query = 'SELECT * FROM events WHERE venue_id = :venueId LIMIT 10';
    $conds = array('venueId' => 23);

    // ############################################

    echo '<h3>fetchValue</h3>';
    $results = $dbh->fetchValue($query, $conds);
    var_dump($results);

    // ############################################

    echo '<h3>fetchValueMany</h3>';
    $results = $dbh->fetchValueMany($query, $conds);
    echo '<h4>total rows: ' . $dbh->getRowCount() . '</h4>';
    var_dump($results);

    // ############################################

    echo '<h3>fetchValueManyCursor</h3>';
    echo '<h4>#1 cursor</h4>';
    $results = $dbh->fetchValueManyCursor($query, $conds);
    var_dump($results);

    echo '<h4>#2 cursor</h4>';
    $results = $dbh->fetchValueManyCursor($query, $conds);
    var_dump($results);

    echo '<h4>#3 cursor (should result in NULL)</h4>';
    $results = $dbh->fetchValueManyCursor($query, $conds);
    var_dump($results);

    // ############################################

    echo '<h3>fetch</h3>';
    $results = $dbh->fetch($query, $conds);
    var_dump($results);

    // ############################################

    echo '<h3>fetchMany</h3>';
    $results = $dbh->fetchMany($query, $conds);
    var_dump($results);

    // ############################################

    echo '<h3>fetchCursor</h3>';
    echo '<h4>#1 cursor</h4>';
    $results = $dbh->fetchManyCursor($query, $conds);
    var_dump($results);

    echo '<h4>#2 cursor</h4>';
    $results = $dbh->fetchManyCursor($query, $conds);
    var_dump($results);

    echo '<h4>#3 cursor (should result in NULL)</h4>';
    $results = $dbh->fetchManyCursor($query, $conds);
    var_dump($results);

    // ############################################

    echo '<h3>execute sql: truncate</h3>';
    $response = $dbh->executeSql('TRUNCATE import_dump');
    var_dump($response);

    // ############################################

    echo '<h3>insert</h3>';
    $data = [
        [
            'id'   => NULL,
            'dump' => '{"message":"Hello"}',
        ],
        [
            'id'   => NULL,
            'dump' => '{"message":"Foo"}',
        ],
        [
            'id'   => NULL,
            'dump' => '{"message":"Bar"}',
        ],
    ];
    $results = $dbh->insertMany('import_dump', $data);
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
        'id' => [
            'opr' => '>=',
            'val' => '3'
        ],
    ];
    $results = $dbh->delete('import_dump', $conds);
    var_dump($results);
