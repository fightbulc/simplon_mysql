<?php

    require __DIR__ . '/../vendor/autoload.php';

    $dbh = new \Simplon\Mysql\Mysql('localhost', 'beatguide_devel_service', 'rootuser', 'rootuser');
    $query = 'SELECT * FROM events WHERE venue_id = :venueId LIMIT 2';
    $conds = array('venueId' => 23);

    // ############################################

    echo '<h3>fetchValue</h3>';

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);
    $results = $sqlManager->fetchColumn($sqlBuilder);

    var_dump($results);

    // ############################################

    echo '<h3>fetchValueMany</h3>';

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);
    $results = $sqlManager->fetchColumnMany($sqlBuilder);

    echo '<h4>total rows: ' . $sqlManager->getRowCount() . '</h4>';
    var_dump($results);

    // ############################################

    echo '<h3>fetchValueManyCursor</h3>';

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $counter = 0;
    foreach ($sqlManager->fetchColumnManyCursor($sqlBuilder) as $result)
    {
        echo '<h4>#' . (++$counter) . ' cursor</h4>';
        var_dump($result);
    }

    // ############################################

    echo '<h3>fetch</h3>';

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $results = $sqlManager->fetchRow($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>fetchMany</h3>';

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $results = $sqlManager->fetchRowMany($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>fetchManyCursor</h3>';

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $counter = 0;
    foreach ($sqlManager->fetchRowManyCursor($sqlBuilder) as $result)
    {
        echo '<h4>#' . (++$counter) . ' cursor</h4>';
        var_dump($result);
    }

    // ############################################

    echo '<h3>execute sql: truncate</h3>';

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setQuery('TRUNCATE import_dump');

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $response = $sqlManager->executeSql($sqlBuilder);
    var_dump($response);

    // ############################################

    echo '<h3>insert</h3>';

    $data = [
        'id'   => NULL,
        'dump' => '{"message":"Hello"}',
    ];

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setData($data);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $results = $sqlManager->insert($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>insertMany</h3>';

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

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setData($data);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $results = $sqlManager->insert($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>update</h3>';

    $conds = ['id' => 1];
    $data = ['dump' => '{"message":"Hello BOOOOO"}'];

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setConditions($conds)
        ->setData($data);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $results = $sqlManager->update($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>replace</h3>';
    $data = [
        'id'   => 3,
        'dump' => '{"message":"Booooh!"}'
    ];

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setData($data);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $results = $sqlManager->replace($sqlBuilder);
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

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setData($data);

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $results = $sqlManager->replace($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>delete</h3>';

    $conds = [
        'id' => 3,
    ];

    $sqlBuilder = (new \Simplon\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setConditions($conds)
        ->setConditionsQuery('id = :id');

    $sqlManager = new \Simplon\Mysql\SqlManager($dbh);

    $results = $sqlManager->delete($sqlBuilder);
    var_dump($results);
