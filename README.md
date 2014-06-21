<pre>
     _                 _                                         _ 
 ___(_)_ __ ___  _ __ | | ___  _ __    _ __ ___  _   _ ___  __ _| |
/ __| | '_ ` _ \| '_ \| |/ _ \| '_ \  | '_ ` _ \| | | / __|/ _` | |
\__ \ | | | | | | |_) | | (_) | | | | | | | | | | |_| \__ \ (_| | |
|___/_|_| |_| |_| .__/|_|\___/|_| |_| |_| |_| |_|\__, |___/\__, |_|
                |_|                              |___/        |_|  
</pre>

# Simplon/Mysql

1. __Installing__  
2. __Direct vs. SqlManager__  
3. __Setup connection__
4. __Usage: Direct access__  
4.1. Query  
4.2. Insert    
4.3. Update  
4.4. Replace  
4.5. Delete  
4.6. Execute
5. __Usage: SqlManager__  
5.1. Query  
5.2. Insert  
5.3. Update  
5.4. Replace    
5.5. Delete
5.6. Execute
6. __IN() Clause Handling__
6.1. The issue  
6.2. The solution  
7. __Exceptions__

-------------------------------------------------

### Dependecies

- PHP >= 5.3
- PDO

-------------------------------------------------

## 1. Installing

Easy install via composer. Still no idea what composer is? Inform yourself [here](http://getcomposer.org).

```json
{
  "require": {
    "simplon/mysql": "*"
  }
}
```

-------------------------------------------------

## 2. Direct vs. SqlManager

I implemented two different ways of interacting with MySQL. The first option is the usual one which interacts directly with the database. Following a straight forward example to show you what I mean:
```php
$dbConn->fetchRow('SELECT * FROM names WHERE name = :name', ['name' => 'Peter']); 
```

In constrast to the prior method the SqlManager uses a [Builder Pattern](http://sourcemaking.com/design_patterns/builder) to deal with the database. What advantage does that offer? Well, in case that we want to do more things with our query before sending it off we encapsule it as a ```Builder Pattern```. From there on we could pass it throughout our application to add more data or alike before sending the query finally off to the database. Again, a quick example of how we would rewrite the above ```direct query```:
  
```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setQuery('SELECT * FROM names WHERE name = :name')
    ->setConditions(['name' => 'Peter']);

(new \Simplon\Mysql\Manager\SqlManager($dbConn))->fetchRow($sqlBuilder);
```

-------------------------------------------------

## 3. Setup connection

The library requires a config value object in order to instantiate a connection with MySQL. See how it's done:

```php
$config = [
    // required credentials

    'server'     => 'localhost',
    'username'   => 'rootuser',
    'password'   => 'rootuser',
    'database'   => 'our_database',
    
    // optional
    
    'fetchMode'  => \PDO::FETCH_ASSOC,
    'charset'    => 'utf8',
    'port'       => 3306,
    'unixSocket' => null,
];

$dbConn = new \Simplon\Mysql\Mysql(
    new \Simplon\Mysql\MysqlConfigVo($config)
);
```

In case that you wanna use the ```SqlManager``` there is one piece missing:

```php
$sqlManager = new \Simplon\Mysql\Manager\SqlManager($dbConn);
```

-------------------------------------------------

## 4. Usage: Direct access

### 4.1. Query

#### FetchColumn

Returns a selected column from the first match. The example below returns ```id``` or ```false``` if nothing was found.

```php
$result = $dbConn->fetchColumn('SELECT id FROM names WHERE name = :name', ['name' => 'Peter']);

// result
var_dump($result); // '1' || false
```

#### FetchColumnMany

Returns an array with the selected column from all matching datasets. In the example below an array with all ```ids``` will be returned or ```false``` if nothing was found.

```php
$result = $dbConn->fetchColumnMany('SELECT id FROM names WHERE name = :name', ['name' => 'Peter']);

// result
var_dump($result); // ['1', '15', '30', ...] || false
```

#### FetchColumnManyCursor

Returns one matching dataset at a time. It is resource efficient and therefore handy when your result has many data. In the example below you either iterate through the foreach loop in case you have matchings or nothing will happen.

```php
$cursor = $dbConn->fetchColumnMany('SELECT id FROM names WHERE name = :name', ['name' => 'Peter']);

foreach ($cursor as $result)
{
    var_dump($result); // '1'
}
```

#### FetchRow

Returns all selected columns from a matched dataset. The example below returns ```id```, ```age``` for the matched dataset. If nothing got matched ```false``` will be returned.

```php
$result = $dbConn->fetchRow('SELECT id, age FROM names WHERE name = :name', ['name' => 'Peter']);

var_dump($result); // ['id' => '1', 'age' => '22'] || false
```

#### FetchRowMany

Returns all selected columns from all matched dataset. The example below returns for each matched dataset ```id```, ```age```. If nothing got matched ```false``` will be returned.

```php
$result = $dbConn->fetchRowMany('SELECT id, age FROM names WHERE name = :name', ['name' => 'Peter']);

var_dump($result); // [ ['id' => '1', 'age' => '22'],  ['id' => '15', 'age' => '40'], ... ] || false
```

#### FetchRowManyCursor

Same explanation as for ```FetchColumnManyCursor``` except that we receive all selected columns.

```php
$result = $dbConn->fetchRowMany('SELECT id, age FROM names WHERE name = :name', ['name' => 'Peter']);

foreach ($cursor as $result)
{
    var_dump($result); // ['id' => '1', 'age' => '22']
}
```

-------------------------------------------------

### 4.2. Insert

#### Single data

Inserting data into the database is pretty straight forward. Follow the example below:

```php
$data = [
    'id'   => false,
    'name' => 'Peter',
    'age'  => 45,
];

$id = $dbConn->insert('names', $data);

var_dump($id); // 50 || bool
```

The result depends on the table. If the table holds an ```autoincrementing ID``` column you will receive the ID count for the inserted data. If the table does not hold such a field you will receive ```true``` for a successful insert. If anything went bogus you will receive ```false```. 

#### Many datasets

Follow the example for inserting many datasets at once:

```php
$data = [
    [    
        'id'   => false,
        'name' => 'Peter',
        'age'  => 45,
    ],
    [    
        'id'   => false,
        'name' => 'Peter',
        'age'  => 16,
    ],
];

$id = $dbConn->insertMany('names', $data);

var_dump($id); // 50 || bool
```

The result depends on the table. If the table holds an ```autoincrementing ID``` column you will receive the ID count for the inserted data. If the table does not hold such a field you will receive ```true``` for a successful insert. If anything went bogus you will receive ```false```. 

-------------------------------------------------

### 4.3. Updating

#### Simple update statement

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If something went wrong you will receive ```false```.

```php
$conds [
    'id' => 50,
];

$data = [
    'name' => 'Peter',
    'age'  => 50,
];

$result = $dbConn->update('names', $conds, $data);

var_dump($result); // true || false
```

#### Custom update conditions query

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If something went wrong you will receive ```false```.

```php
$conds [
    'id'   => 50,
    'name' => 'Peter',
];

// custom conditions query
$condsQuery = 'id = :id OR name =: name';

$data = [
    'name' => 'Peter',
    'age'  => 50,
];

$result = $dbConn->update('names', $conds, $data, $condsQuery);

var_dump($result); // true || false
```

-------------------------------------------------

### 4.4. Replace

As MySQL states it: ```REPLACE``` works exactly like ```INSERT```, except that if an old row in the table has the same value as a new row for a ```PRIMARY KEY``` or a ```UNIQUE index```, the old row is deleted before the new row is inserted.

#### Replace a single datasets

As a result you will either receive the ```INSERT ID``` or ```false``` in case something went wrong.

```php
$data = [
    'id'   => 5,
    'name' => 'Peter',
    'age'  => 16,
];

$result = $dbConn->replace('names', $data);

var_dump($result); // 1 || false
```

#### Replace multiple datasets

As a result you will either receive an array of ```INSERT IDs``` or ```false``` in case something went wrong.

```php
$data = [
    [
        'id'   => 5,
        'name' => 'Peter',
        'age'  => 16,
    ],
    [
        'id'   => 10,
        'name' => 'John',
        'age'  => 22,
    ],
];

$result = $dbConn->replaceMany('names', $data);

var_dump($result); // [5, 10]  || false
```

-------------------------------------------------

### 4.5. Delete

#### Simple delete conditions

The following example demonstrates how to remove data. If the query succeeds we will receive ```true``` else ```false```.

```php
$result = $dbConn->delete('names', ['id' => 50]);

var_dump($result); // true || false
```

#### Custom delete conditions query

The following example demonstrates how to remove data with a custom conditions query. If the query succeeds we will receive ```true``` else ```false```.

```php
$conds = [
    'id'   => 50,
    'name' => 'John',
];

// custom conditions query
$condsQuery = 'id = :id OR name =: name';

$result = $dbConn->delete('names', $conds, $condsQuery);

var_dump($result); // true || false
```

-------------------------------------------------

### 4.6. Execute

This method is ment for calls which do not require any parameters such as ```TRUNCATE```. If the call succeeds you will receive ```true```. If it fails an ```MysqlException``` will be thrown. 

```php
$result = $dbConn->executeSql('TRUNCATE names');

var_dump($result); // true
```

-------------------------------------------------

## 5. Usage: SqlManager

The following query examples will be a rewrite of the aforementioned ```direct access``` examples. __Remember:__ We need an instance of the ```SqlManager```. Paragraph ```3. Setup connection``` shows how to get your hands on it. 

### 5.1. Query

#### FetchColumn

Returns a selected column from the first match. In the example below ```id``` will be returned or ```false``` if nothing was found.

```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setQuery('SELECT id FROM names WHERE name = :name')
    ->setConditions(['name' => 'Peter']);

$result = $sqlManager->fetchColumn($sqlBuilder);

// result
var_dump($result); // '1' || false
```

#### FetchColumnMany

Returns an array with the selected column from all matching datasets. In the example below an array with all ```ids``` will be returned or ```false``` if nothing was found.

```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setQuery('SELECT id FROM names WHERE name = :name')
    ->setConditions(['name' => 'Peter']);

$result = $sqlManager->fetchColumnMany($sqlBuilder);

// result
var_dump($result); // ['1', '15', '30', ...] || false
```

#### FetchColumnManyCursor

Returns one matching dataset at a time. It is resource efficient and therefore handy when your result has many data. In the example below you either iterate through the foreach loop in case you have matchings or nothing will happen.

```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setQuery('SELECT id FROM names WHERE name = :name')
    ->setConditions(['name' => 'Peter']);

foreach ($sqlManager->fetchColumnMany($sqlBuilder) as $result)
{
    var_dump($result); // '1'
}
```

#### FetchRow

Returns all selected columns from a matched dataset. The example below returns ```id```, ```age``` for the matched dataset. If nothing got matched ```false``` will be returned.

```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setQuery('SELECT id, age FROM names WHERE name = :name')
    ->setConditions(['name' => 'Peter']);

$result = $sqlManager->fetchRow($sqlBuilder);

var_dump($result); // ['id' => '1', 'age' => '22'] || false
```

#### FetchRowMany

Returns all selected columns from all matched dataset. The example below returns for each matched dataset ```id```, ```age```. If nothing got matched ```false``` will be returned.

```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setQuery('SELECT id, age FROM names WHERE name = :name')
    ->setConditions(['name' => 'Peter']);

$result = $sqlManager->fetchRowMany($sqlBuilder);

var_dump($result); // [ ['id' => '1', 'age' => '22'],  ['id' => '15', 'age' => '40'], ... ] || false
```

#### FetchRowManyCursor

Same explanation as for ```FetchColumnManyCursor``` except that we receive all selected columns.

```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setQuery('SELECT id, age FROM names WHERE name = :name')
    ->setConditions(['name' => 'Peter']);

foreach ($sqlManager->fetchRowManyCursor($sqlBuilder) as $result)
{
    var_dump($result); // ['id' => '1', 'age' => '22']
}
```

-------------------------------------------------

### 5.2. Insert

#### Single data

Inserting data into the database is pretty straight forward. Follow the example below:

```php
$data = [
    'id'   => false,
    'name' => 'Peter',
    'age'  => 45,
];

$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setData($data);

$id = $sqlManager->insert($sqlBuilder);

var_dump($id); // 50 || false
```

The result depends on the table. If the table holds an ```autoincrementing ID``` column you will receive the ID count for the inserted data. If the table does not hold such a field you will receive ```true``` for a successful insert. If anything went bogus you will receive ```false```. 

#### Many datasets

Follow the example for inserting many datasets at once:

```php
$data = [
    [    
        'id'   => false,
        'name' => 'Peter',
        'age'  => 45,
    ],
    [    
        'id'   => false,
        'name' => 'Peter',
        'age'  => 16,
    ],
];

$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setData($data);

$result = $sqlManager->insert($sqlBuilder);

var_dump($id); // [50, 51, ...] || false
```

The result depends on the table. If the table holds an ```autoincrementing ID``` column you will receive the ID count for the inserted data. If the table does not hold such a field you will receive ```true``` for a successful insert. If anything went bogus you will receive ```false```. 

### 5.3. Update

#### Simple update statement

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If something went wrong you will receive ```false```.

```php
$data = [
    'name' => 'Peter',
    'age'  => 50,
];

$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setConditions(['id' => 50])
    ->setData($data);

$result = $sqlManager->update($sqlBuilder);

var_dump($result); // true || false
```

#### Custom update conditions query

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If something went wrong you will receive ```false```.

```php
$data = [
    'name' => 'Peter',
    'age'  => 50,
];

$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setConditions(['id' => 50])
    ->setConditionsQuery('id = :id OR name =: name')
    ->setData($data)

$result = $sqlManager->update($sqlBuilder);

var_dump($result); // true || false
```

### 5.4. Replace

As MySQL states it: ```REPLACE``` works exactly like ```INSERT```, except that if an old row in the table has the same value as a new row for a ```PRIMARY KEY``` or a ```UNIQUE index```, the old row is deleted before the new row is inserted.

#### Replace a single datasets

As a result you will either receive the ```INSERT ID``` or ```false``` in case something went wrong.

```php
$data = [
    'id'   => 5,
    'name' => 'Peter',
    'age'  => 16,
];

$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setData($data);

$result = $sqlManager->replace($sqlBuilder);

var_dump($result); // 1 || false
```

#### Replace multiple datasets

As a result you will either receive an array of ```INSERT IDs``` or ```false``` in case something went wrong.

```php
$data = [
    [
        'id'   => 5,
        'name' => 'Peter',
        'age'  => 16,
    ],
    [
        'id'   => 10,
        'name' => 'John',
        'age'  => 22,
    ],
];

$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setData($data);

$result = $sqlManager->replaceMany($sqlBuilder);

var_dump($result); // [5, 10]  || false
```

### 5.5. Delete

#### Simple delete conditions

The following example demonstrates how to remove data. If the query succeeds we will receive ```true``` else ```false```.

```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setConditions(['id' => 50]);

$result = $sqlManager->delete($sqlBuilder);

var_dump($result); // true || false
```

#### Custom delete conditions query

The following example demonstrates how to remove data with a custom conditions query. If the query succeeds we will receive ```true``` else ```false```.

```php
$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setConditions(['id' => 50, 'name' => 'Peter'])
    ->setConditionsQuery('id = :id OR name =: name');

$result = $sqlManager->delete($sqlBuilder);

var_dump($result); // true || false
```

-------------------------------------------------

## 6. IN() Clause Handling

#### 6.1. The issue

There is no way using an ```IN()``` clause via PDO. This functionality is simply not given. However, you could do something like the following:

```php
$ids = [1,2,3,4,5];
$query = "SELECT * FROM users WHERE id IN (" . join(',', $ids) . ")";
```

Looks good at first sight - not sexy but probably does the job, right? Wrong. This approach only works with ```INTEGERS``` and it does not ```ESCAPE``` the user's input - the reason why we use ```PDO``` in first place.

Just for the record here is a string example which would not work:

```php
$emails = ['johnny@me.com', 'peter@ibm.com'];
$query = "SELECT * FROM users WHERE email IN (" . join(',', $emails) . ")";
```

The only way how this would work is by wrapping each value like the following: ```'"email"'```. Way too much work.
#### 6.2. The solution

To take advantage of the built in ```IN() Clause``` with escaping and type handling do the following:

```php
// integers
$conds = [ 'ids' => [1,2,3,4,5] ];
$query = "SELECT * FROM users WHERE id IN (:ids)";

// strings
$conds = [ 'emails' => ['johnny@me.com', 'peter@ibm.com'] ];
$query = "SELECT * FROM users WHERE email IN (:emails)";
```


-------------------------------------------------

## 7. Exceptions

For both access methods (direct, sqlmanager) occuring exceptions will be wrapped by a ```MysqlException```. All essential exception information will be summarised as ```JSON``` within the ```Exception Message```.

Here is an example of how that might look like:

```bash
Houston we have a problem: {"query":"SELECT pro_id FROM names WHERE connector_type = :connectorType","params":{"connectorType":"FB"},"errorInfo":{"sqlStateCode":"42S22","code":1054,"message":"Unknown column 'pro_id' in 'field list'"}}
```

-------------------------------------------------

# License

Cirrus is freely distributable under the terms of the MIT license.

Copyright (c) 2014 Tino Ehrich ([opensource@efides.com](mailto:opensource@efides.com))

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/fightbulc/simplon_mysql/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

