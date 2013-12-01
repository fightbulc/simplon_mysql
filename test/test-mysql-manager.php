<?php

    require __DIR__ . '/../vendor/autoload.php';

    $mysqlConfigVo = (new \Simplon\Db\Mysql\MysqlConfigVo())
        ->setServer('localhost')
        ->setDatabase('beatguide_devel_service')
        ->setUsername('rootuser')
        ->setPassword('rootuser');

    $dbh = new \Simplon\Db\Mysql\Mysql($mysqlConfigVo);
    $query = 'SELECT * FROM events WHERE venue_id = :venueId LIMIT 2';
    $conds = array('venueId' => 23);

    // ############################################

    echo '<h3>fetchValue</h3>';

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);
    $results = $sqlManager->fetchColumn($sqlBuilder);

    var_dump($results);

    // ############################################

    echo '<h3>fetchValueMany</h3>';

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);
    $results = $sqlManager->fetchColumnAll($sqlBuilder);

    echo '<h4>total rows: ' . $sqlManager->getRowCount() . '</h4>';
    var_dump($results);

    // ############################################

    echo '<h3>fetchValueManyCursor</h3>';

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    echo '<h4>#1 cursor</h4>';
    $results = $sqlManager->fetchColumnAllCursor($sqlBuilder);
    var_dump($results);

    echo '<h4>#2 cursor</h4>';
    $results = $sqlManager->fetchColumnAllCursor($sqlBuilder);
    var_dump($results);

    echo '<h4>#3 cursor (should result in FALSE)</h4>';
    $results = $sqlManager->fetchColumnAllCursor($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>fetch</h3>';

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    $results = $sqlManager->fetchRow($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>fetchMany</h3>';

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    $results = $sqlManager->fetchAll($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>fetchCursor</h3>';

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setQuery($query)
        ->setConditions($conds);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    echo '<h4>#1 cursor</h4>';
    $results = $sqlManager->fetchAllCursor($sqlBuilder);
    var_dump($results);

    echo '<h4>#2 cursor</h4>';
    $results = $sqlManager->fetchAllCursor($sqlBuilder);
    var_dump($results);

    echo '<h4>#3 cursor (should result in FALSE)</h4>';
    $results = $sqlManager->fetchAllCursor($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>execute sql: truncate</h3>';

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setQuery('TRUNCATE import_dump');

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    $response = $sqlManager->executeSql($sqlBuilder);
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

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setData($data)
        ->setMultiData(TRUE);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    $results = $sqlManager->insert($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>update</h3>';

    $conds = ['id' => 1];
    $data = ['dump' => '{"message":"Hello Dad"}'];

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setConditions($conds)
        ->setData($data);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    $results = $sqlManager->update($sqlBuilder);
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

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setData($data)
        ->setMultiData(TRUE);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    $results = $sqlManager->replace($sqlBuilder);
    var_dump($results);

    // ############################################

    echo '<h3>delete</h3>';

    $conds = [
        'id' => [
            'opr' => '>=',
            'val' => '3'
        ],
    ];

    $sqlBuilder = (new \Simplon\Db\Mysql\SqlQueryBuilder())
        ->setTableName('import_dump')
        ->setConditions($conds);

    $sqlManager = new \Simplon\Db\Mysql\SqlManager($dbh);

    $results = $sqlManager->delete($sqlBuilder);
    var_dump($results);
