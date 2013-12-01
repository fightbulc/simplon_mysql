<?php

    require __DIR__ . '/../vendor/autoload.php';

    echo '<h1>MySQL</h1>';
    $dbInstance = \Simplon\Db\DbInstance::MySQL('localhost', 'test', 'rootuser', 'rootuser');
    $query = 'SELECT * FROM foobar WHERE ekey = :key';
    $conds = array('key' => 'BB');

    // ############################################

    echo '<h3>Straight Shizzle</h3>';
    $results = $dbInstance->FetchAll($query, $conds);
    var_dump($results);

    // ############################################

    echo '<h3>SqlManager w/ SqlQueryBuilder</h3>';
    $sqlManager = new \Simplon\Db\SqlManager($dbInstance);

    // query builder
    $sqlQuery = (new \Simplon\Db\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    // query db
    $results = $sqlManager->fetchAll($sqlQuery);
    var_dump($results);


