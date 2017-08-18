<pre>
     _                 _                                         _ 
 ___(_)_ __ ___  _ __ | | ___  _ __    _ __ ___  _   _ ___  __ _| |
/ __| | '_ ` _ \| '_ \| |/ _ \| '_ \  | '_ ` _ \| | | / __|/ _` | |
\__ \ | | | | | | |_) | | (_) | | | | | | | | | | |_| \__ \ (_| | |
|___/_|_| |_| |_| .__/|_|\___/|_| |_| |_| |_| |_|\__, |___/\__, |_|
                |_|                              |___/        |_|  
</pre>

# Simplon/Mysql

-------------------------------------------------

1. [__Installing__](#1-installing)  
2. [__Direct vs. CRUD__](#2-direct-vs-crud)  
3. [__Setup connection__](#3-setup-connection)  
4. [__Usage: Direct access__](#4-usage-direct-access)  
4.1. Query  
4.2. Insert  
4.3. Update  
4.4. Replace  
4.5. Delete  
4.6. Execute  
5. [__Usage: CRUD__](#5-usage-crud)  
5.1. Setup store  
5.2. Setup model  
5.3. Connect to store  
5.4. Query  
5.5. Insert  
5.6. Update  
5.7. Delete  
5.8. Custom queries  
6. [__IN() Clause Handling__](#6-in-clause-handling)  
6.1. The issue  
6.2. The solution  
7. [__Exceptions__](#7-exceptions)

-------------------------------------------------

### Dependecies

- PHP >= 7.1
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

## 2. Direct vs. CRUD

I implemented two different ways of interacting with MySQL. The first option is the usual one which interacts directly with the database. Following a straight forward example to show you what I mean:

```php
$data = $dbConn->fetchRow('SELECT * FROM names WHERE name = :name', ['name' => 'Peter']);

//
// $data is an array with our result
//
```

In constrast to the prior method CRUD is more structured with `store` and `model`. Further, it uses [Builder Patterns](http://sourcemaking.com/design_patterns/builder) to interact with the database. A quick example of how we would rewrite the above ```direct query```:
  
```php
$store = new NamesStore($dbConn);

$model = $store->read(
    (new ReadQueryBuilder())->addCondition(NameModel::COLUMN_NAME, 'Peter')
);

//
// $model is a class with our result data abstracted
//
```

-------------------------------------------------

## 3. Setup connection

The library requires a config value object in order to instantiate a connection with MySQL. See how it's done:

```php
$pdo = new PDOConnector(
	'localhost', // server
	'root',      // user
	'root',      // password
	'database'   // database
);

$pdoConn = $pdo->connect('utf8', []); // charset, options

//
// you could now interact with PDO for instance setting attributes etc:
// $pdoConn->setAttribute($attribute, $value);
//

$dbConn = new Mysql($pdoConn);
```

-------------------------------------------------

## 4. Usage: Direct access

### 4.1. Query

#### FetchColumn

Returns a selected column from the first match. The example below returns ```id``` or ```null``` if nothing was found.

```php
$result = $dbConn->fetchColumn('SELECT id FROM names WHERE name = :name', ['name' => 'Peter']);

// result
var_dump($result); // '1' || null
```

#### FetchColumnMany

Returns an array with the selected column from all matching datasets. In the example below an array with all ```ids``` will be returned or ```null``` if nothing was found.

```php
$result = $dbConn->fetchColumnMany('SELECT id FROM names WHERE name = :name', ['name' => 'Peter']);

// result
var_dump($result); // ['1', '15', '30', ...] || null
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

Returns all selected columns from a matched dataset. The example below returns ```id```, ```age``` for the matched dataset. If nothing got matched ```null``` will be returned.

```php
$result = $dbConn->fetchRow('SELECT id, age FROM names WHERE name = :name', ['name' => 'Peter']);

var_dump($result); // ['id' => '1', 'age' => '22'] || null
```

#### FetchRowMany

Returns all selected columns from all matched dataset. The example below returns for each matched dataset ```id```, ```age```. If nothing got matched ```null``` will be returned.

```php
$result = $dbConn->fetchRowMany('SELECT id, age FROM names WHERE name = :name', ['name' => 'Peter']);

var_dump($result); // [ ['id' => '1', 'age' => '22'],  ['id' => '15', 'age' => '40'], ... ] || null
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

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If nothing has been updated you will receive ```null```.

```php
$conds = [
    'id' => 50,
];

$data = [
    'name' => 'Peter',
    'age'  => 50,
];

$result = $dbConn->update('names', $conds, $data);

var_dump($result); // true || null
```

#### Custom update conditions query

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If nothing has been updated you will receive ```null```.

```php
$conds = [
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

var_dump($result); // true || null
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

## 5. Usage: CRUD

The following query examples will be a rewrite of the aforementioned ```direct access``` examples. For this we need a `Store` and a related `Model`.

### 5.1. Setup store

```php
namespace Test\Crud;

use Simplon\Mysql\Crud\CrudModelInterface;
use Simplon\Mysql\Crud\CrudStore;
use Simplon\Mysql\MysqlException;
use Simplon\Mysql\QueryBuilder\CreateQueryBuilder;
use Simplon\Mysql\QueryBuilder\DeleteQueryBuilder;
use Simplon\Mysql\QueryBuilder\ReadQueryBuilder;
use Simplon\Mysql\QueryBuilder\UpdateQueryBuilder;

/**
 * @package Test\Crud
 */
class NamesStore extends CrudStore
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'names';
    }

    /**
     * @return CrudModelInterface
     */
    public function getModel(): CrudModelInterface
    {
        return new NameModel();
    }

    /**
     * @param CreateQueryBuilder $builder
     *
     * @return NameModel
     * @throws MysqlException
     */
    public function create(CreateQueryBuilder $builder): NameModel
    {
        /** @var NameModel $model */
        $model = $this->crudCreate($builder);

        return $model;
    }

    /**
     * @param ReadQueryBuilder|null $builder
     *
     * @return NameModel[]|null
     * @throws MysqlException
     */
    public function read(?ReadQueryBuilder $builder = null): ?array
    {
        /** @var NameModel[]|null $response */
        $response = $this->crudRead($builder);

        return $response;
    }

    /**
     * @param ReadQueryBuilder $builder
     *
     * @return null|NameModel
     * @throws MysqlException
     */
    public function readOne(ReadQueryBuilder $builder): ?NameModel
    {
        /** @var NameModel|null $response */
        $response = $this->crudReadOne($builder);

        return $response;
    }

    /**
     * @param UpdateQueryBuilder $builder
     *
     * @return NameModel
     * @throws MysqlException
     */
    public function update(UpdateQueryBuilder $builder): NameModel
    {
        /** @var NameModel|null $model */
        $model = $this->crudUpdate($builder);

        return $model;
    }

    /**
     * @param DeleteQueryBuilder $builder
     *
     * @return bool
     * @throws MysqlException
     */
    public function delete(DeleteQueryBuilder $builder): bool
    {
        return $this->crudDelete($builder);
    }   
    
    /**
     * @param int $id
     *
     * @return null|NameModel
     * @throws MysqlException
     */
    public function customMethod(int $id): ?NameModel
    {
        $query = 'SELECT * FROM ' . $this->getTableName() . ' WHERE id=:id';

        if ($result = $this->getCrudManager()->getMysql()->fetchRow($query, ['id' => $id]))
        {
            return (new NameModel())->fromArray($result);
        }

        return null;
    }
}
```

### 5.2. Setup model

```php
<?php

namespace Test\Crud;

use Simplon\Mysql\Crud\CrudModel;

/**
 * @package Test\Crud
 */
class NameModel extends CrudModel
{
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'name';
    const COLUMN_AGE = 'age';

    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var int
     */
    protected $age;

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * @param int $id
     *
     * @return NameModel
     */
    public function setId(int $id): NameModel
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return NameModel
     */
    public function setName(string $name): NameModel
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return (int)$this->age;
    }

    /**
     * @param int $age
     *
     * @return NameModel
     */
    public function setAge(int $age): NameModel
    {
        $this->age = $age;

        return $this;
    }
}
```

### 5.3. Connect to store

In order to interact with our store we need to create an instance. For the following points we will make use of this instance.

```php
$store = new NamesStore($pdoConn);
```

### 5.4. Query

#### Fetch one item

Returns a `name model` or `NULL` if nothing could be matched.

```php
$model = $store->readOne(
    (new ReadQueryBuilder())->addCondition(NameModel::COLUMN_NAME, 'Peter')
);

echo $model->getId(); // prints user id
```

You can make use of operators from the `addCondition`:

```php
$model = $store->readOne(
    (new ReadQueryBuilder())->addCondition(NameModel::COLUMN_AGE, 20, '>')
);

echo $model->getId(); // prints user id
```

#### Fetch many items

Returns an array of `name models` or `NULL` if nothing could be matched.

```php
$models = $store->read(
    (new ReadQueryBuilder())->addCondition(NameModel::COLUMN_NAME, 'Peter')
);

echo $models[0]->getId(); // prints user id from first matched model
```

### 5.5. Insert

The following example shows how to create a new store entry.

```php
$model = $store->create(
    (new CreateQueryBuilder())->setModel(
        (new NameModel())
            ->setName('Johnny')
            ->setAge(22)
    )
);
```

### 5.6. Update  

The following example shows how to update an existing store entry.

```php
//
// fetch a model
//

$model = $store->readOne(
    (new ReadQueryBuilder())->addCondition(NameModel::COLUMN_NAME, 'Peter')
);

//
// update age
//

$model->setAge(36);

//
// persist change
//

$model = $store->update(
    (new UpdateQueryBuilder())
        ->setModel($model)
        ->addCondition(NameModel::COLUMN_ID, $model->getId())
);
```

### 5.7. Delete  

The following example shows how to delete an existing store entry.

```php
//
// fetch a model
//

$model = $store->readOne(
    (new ReadQueryBuilder())->addCondition(NameModel::COLUMN_NAME, 'Peter')
);

//
// delete model from store
//

$store->delete(
    (new DeleteQueryBuilder())
        ->addCondition(NameModel::COLUMN_ID, $model->getId())
)
```

### 5.8. Custom queries

You also have the possibility to write custom queries/handlings for your store. I added a method to the store which demonstrates on how to implement custom handlings.

```php
/**
 * @param int $id
 *
 * @return null|NameModel
 * @throws MysqlException
 */
public function customMethod(int $id): ?NameModel
{
    $query = 'SELECT * FROM ' . $this->getTableName() . ' WHERE id=:id';

    if ($result = $this->getCrudManager()->getMysql()->fetchRow($query, ['id' => $id]))
    {
        return (new NameModel())->fromArray($result);
    }

    return null;
}
```

-------------------------------------------------

## 6. IN() Clause Handling

### 6.1. The issue

There is no way using an ```IN()``` clause via PDO. This functionality is simply not given. However, you could do something like the following:

```php
$ids = array(1,2,3,4,5);
$query = "SELECT * FROM users WHERE id IN (" . join(',', $ids) . ")";
```

Looks good at first sight - not sexy but probably does the job, right? Wrong. This approach only works with ```INTEGERS``` and it does not ```ESCAPE``` the user's input - the reason why we use ```PDO``` in first place.

Just for the record here is a string example which would not work:

```php
$emails = array('johnny@me.com', 'peter@ibm.com');
$query = "SELECT * FROM users WHERE email IN (" . join(',', $emails) . ")";
```

The only way how this would work is by wrapping each value like the following: ```'"email"'```. Way too much work.

### 6.2. The solution

To take advantage of the built in ```IN() Clause``` with escaping and type handling do the following for the direct access. CRUD will do the query building automatically for you.

```php
// integers
$conds = array('ids' => array(1,2,3,4,5));
$query = "SELECT * FROM users WHERE id IN (:ids)";

// strings
$conds = array('emails' => array('johnny@me.com', 'peter@ibm.com'));
$query = "SELECT * FROM users WHERE email IN (:emails)";
```

-------------------------------------------------

## 7. Exceptions

For both access methods (direct, sqlmanager) occuring exceptions will be wrapped by a ```MysqlException```. All essential exception information will be summarised as ```JSON``` within the ```Exception Message```.

Here is an example of how that might look like:

```bash
{"query":"SELECT pro_id FROM names WHERE connector_type = :connectorType","params":{"connectorType":"FB"},"errorInfo":{"sqlStateCode":"42S22","code":1054,"message":"Unknown column 'pro_id' in 'field list'"}}
```

-------------------------------------------------

# License

Simplon/Mysql is freely distributable under the terms of the MIT license.

Copyright (c) 2017 Tino Ehrich ([tino@bigpun.me](mailto:tino@bigpun.me))

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
