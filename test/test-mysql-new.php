<?php

    require __DIR__ . '/../vendor/autoload.php';

    $mysqlConfigVo = (new \Simplon\Db\Library\Mysql\MysqlConfigVo())
        ->setServer('localhost')
        ->setDatabase('beatguide_devel_service')
        ->setUsername('rootuser')
        ->setPassword('rootuser');

    $dbh = new \Simplon\Db\Library\Mysql\Mysql($mysqlConfigVo);
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