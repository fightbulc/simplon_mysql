__Note:__ Version 1.x will break when it comes to CRUD. Changed a lot here. Will try to bring documentation up to speed.

<pre>
     _                 _                                         _ 
 ___(_)_ __ ___  _ __ | | ___  _ __    _ __ ___  _   _ ___  __ _| |
/ __| | '_ ` _ \| '_ \| |/ _ \| '_ \  | '_ ` _ \| | | / __|/ _` | |
\__ \ | | | | | | |_) | | (_) | | | | | | | | | | |_| \__ \ (_| | |
|___/_|_| |_| |_| .__/|_|\___/|_| |_| |_| |_| |_|\__, |___/\__, |_|
                |_|                              |___/        |_|  
</pre>

# Simplon/Mysql

!! NOTE: 2.0 is experimental, works only with PHP 7.1+ and works with `QueryBuilder` !!

-------------------------------------------------

1. [__Installing__](#1-installing)  
2. [__Direct vs. SqlManager__](#2-direct-vs-sqlmanager)  
3. [__Setup connection__](#3-setup-connection)  
4. [__Usage: Direct access__](#4-usage-direct-access)  
4.1. Query  
4.2. Insert  
4.3. Update  
4.4. Replace  
4.5. Delete  
4.6. Execute  
4.7. Transactions    
5. [__Usage: SqlManager__](#5-usage-sqlmanager)  
5.1. Query  
5.2. Insert  
5.3. Update  
5.4. Replace    
5.5. Delete  
5.6. Execute  
6. [__IN() Clause Handling__](#6-in-clause-handling)  
6.1. The issue  
6.2. The solution  
7. [__CRUD Helper__](#7-crud-helper)  
7.1. Intro  
7.2. Requirements  
7.3. Flexibility/Restrictions  
7.4. Conclusion  
7.5. Examples  
7.6. Example Custom Vo
8. [__Exceptions__](#8-exceptions)

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
$dbConn->fetchRow('SELECT * FROM names WHERE name = :name', array('name' => 'Peter'));
```

In constrast to the prior method the SqlManager uses a [Builder Pattern](http://sourcemaking.com/design_patterns/builder) to deal with the database. What advantage does that offer? Well, in case that we want to do more things with our query before sending it off we encapsule it as a ```Builder Pattern```. From there on we could pass it throughout our application to add more data or alike before sending the query finally off to the database. Again, a quick example of how we would rewrite the above ```direct query```:
  
```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setQuery('SELECT * FROM names WHERE name = :name')
    ->setConditions(array('name' => 'Peter'));

$sqlManager = new \Simplon\Mysql\Manager\SqlManager($dbConn);
$sqlManager->fetchRow($sqlBuilder);
```

-------------------------------------------------

## 3. Setup connection

The library requires a config value object in order to instantiate a connection with MySQL. See how it's done:

```php
$config = array(
    // required credentials

    'host'       => 'localhost',
    'user'       => 'rootuser',
    'password'   => 'rootuser',
    'database'   => 'our_database',
    
    // optional
    
    'fetchMode'  => \PDO::FETCH_ASSOC,
    'charset'    => 'utf8',
    'port'       => 3306,
    'unixSocket' => null,
);

// standard setup
$dbConn = new \Simplon\Mysql\Mysql(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database']
);
```

The following code shows all possible parameters to setup a connection:

```php
\Simplon\Mysql\Mysql::__construct(
    $host,
    $user,
    $password,
    $database,
    $fetchMode = \PDO::FETCH_ASSOC,
    $charset = 'utf8',
    array $options = array('port' => 3306, 'unixSocket' => '')
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

Returns a selected column from the first match. The example below returns ```id``` or ```null``` if nothing was found.

```php
$result = $dbConn->fetchColumn('SELECT id FROM names WHERE name = :name', array('name' => 'Peter'));

// result
var_dump($result); // '1' || null
```

#### FetchColumnMany

Returns an array with the selected column from all matching datasets. In the example below an array with all ```ids``` will be returned or ```null``` if nothing was found.

```php
$result = $dbConn->fetchColumnMany('SELECT id FROM names WHERE name = :name', array('name' => 'Peter'));

// result
var_dump($result); // ['1', '15', '30', ...] || null
```

#### FetchColumnManyCursor

Returns one matching dataset at a time. It is resource efficient and therefore handy when your result has many data. In the example below you either iterate through the foreach loop in case you have matchings or nothing will happen.

```php
$cursor = $dbConn->fetchColumnMany('SELECT id FROM names WHERE name = :name', array('name' => 'Peter'));

foreach ($cursor as $result)
{
    var_dump($result); // '1'
}
```

#### FetchRow

Returns all selected columns from a matched dataset. The example below returns ```id```, ```age``` for the matched dataset. If nothing got matched ```null``` will be returned.

```php
$result = $dbConn->fetchRow('SELECT id, age FROM names WHERE name = :name', array('name' => 'Peter'));

var_dump($result); // ['id' => '1', 'age' => '22'] || null
```

#### FetchRowMany

Returns all selected columns from all matched dataset. The example below returns for each matched dataset ```id```, ```age```. If nothing got matched ```null``` will be returned.

```php
$result = $dbConn->fetchRowMany('SELECT id, age FROM names WHERE name = :name', array('name' => 'Peter'));

var_dump($result); // [ ['id' => '1', 'age' => '22'],  ['id' => '15', 'age' => '40'], ... ] || null
```

#### FetchRowManyCursor

Same explanation as for ```FetchColumnManyCursor``` except that we receive all selected columns.

```php
$result = $dbConn->fetchRowMany('SELECT id, age FROM names WHERE name = :name', array('name' => 'Peter'));

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
$data = array(
    'id'   => false,
    'name' => 'Peter',
    'age'  => 45,
);

$id = $dbConn->insert('names', $data);

var_dump($id); // 50 || bool
```

The result depends on the table. If the table holds an ```autoincrementing ID``` column you will receive the ID count for the inserted data. If the table does not hold such a field you will receive ```true``` for a successful insert. If anything went bogus you will receive ```false```. 

#### Many datasets

Follow the example for inserting many datasets at once:

```php
$data = array(
    array(
        'id'   => false,
        'name' => 'Peter',
        'age'  => 45,
    ),
    array(
        'id'   => false,
        'name' => 'Peter',
        'age'  => 16,
    ),
);

$id = $dbConn->insertMany('names', $data);

var_dump($id); // 50 || bool
```

The result depends on the table. If the table holds an ```autoincrementing ID``` column you will receive the ID count for the inserted data. If the table does not hold such a field you will receive ```true``` for a successful insert. If anything went bogus you will receive ```false```. 

-------------------------------------------------

### 4.3. Updating

#### Simple update statement

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If nothing has been updated you will receive ```null```.

```php
$conds = array(
    'id' => 50,
);

$data = array(
    'name' => 'Peter',
    'age'  => 50,
);

$result = $dbConn->update('names', $conds, $data);

var_dump($result); // true || null
```

#### Custom update conditions query

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If nothing has been updated you will receive ```null```.

```php
$conds = array(
    'id'   => 50,
    'name' => 'Peter',
);

// custom conditions query
$condsQuery = 'id = :id OR name =: name';

$data = array(
    'name' => 'Peter',
    'age'  => 50,
);

$result = $dbConn->update('names', $conds, $data, $condsQuery);

var_dump($result); // true || null
```

-------------------------------------------------

### 4.4. Replace

As MySQL states it: ```REPLACE``` works exactly like ```INSERT```, except that if an old row in the table has the same value as a new row for a ```PRIMARY KEY``` or a ```UNIQUE index```, the old row is deleted before the new row is inserted.

#### Replace a single datasets

As a result you will either receive the ```INSERT ID``` or ```false``` in case something went wrong.

```php
$data = array(
    'id'   => 5,
    'name' => 'Peter',
    'age'  => 16,
);

$result = $dbConn->replace('names', $data);

var_dump($result); // 1 || false
```

#### Replace multiple datasets

As a result you will either receive an array of ```INSERT IDs``` or ```false``` in case something went wrong.

```php
$data = array(
    array(
        'id'   => 5,
        'name' => 'Peter',
        'age'  => 16,
    ),
    array(
        'id'   => 10,
        'name' => 'John',
        'age'  => 22,
    ),
);

$result = $dbConn->replaceMany('names', $data);

var_dump($result); // [5, 10]  || false
```

-------------------------------------------------

### 4.5. Delete

#### Simple delete conditions

The following example demonstrates how to remove data. If the query succeeds we will receive ```true``` else ```false```.

```php
$result = $dbConn->delete('names', array('id' => 50));

var_dump($result); // true || false
```

#### Custom delete conditions query

The following example demonstrates how to remove data with a custom conditions query. If the query succeeds we will receive ```true``` else ```false```.

```php
$conds = array(
    'id'   => 50,
    'name' => 'John',
);

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

### 4.7. Transaction

You can run `transactions` by using the following methods: 

```php
$dbConn->transactionBegin();

// some sql e.g. inserts etc.

$dbConn->transactionCommit();

// upps! if we made a mistake we can rollback

$dbConn->transactionRollback();
```

-------------------------------------------------

## 5. Usage: SqlManager

The following query examples will be a rewrite of the aforementioned ```direct access``` examples. __Remember:__ We need an instance of the ```SqlManager```. Paragraph ```3. Setup connection``` shows how to get your hands on it. 

### 5.1. Query

#### FetchColumn

Returns a selected column from the first match. In the example below ```id``` will be returned or ```null``` if nothing was found.

```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setQuery('SELECT id FROM names WHERE name = :name')
    ->setConditions(array('name' => 'Peter'));

$result = $sqlManager->fetchColumn($sqlBuilder);

// result
var_dump($result); // '1' || null
```

#### FetchColumnMany

Returns an array with the selected column from all matching datasets. In the example below an array with all ```ids``` will be returned or ```null``` if nothing was found.

```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setQuery('SELECT id FROM names WHERE name = :name')
    ->setConditions(array('name' => 'Peter'));

$result = $sqlManager->fetchColumnMany($sqlBuilder);

// result
var_dump($result); // ['1', '15', '30', ...] || null
```

#### FetchColumnManyCursor

Returns one matching dataset at a time. It is resource efficient and therefore handy when your result has many data. In the example below you either iterate through the foreach loop in case you have matchings or nothing will happen.

```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setQuery('SELECT id FROM names WHERE name = :name')
    ->setConditions(array('name' => 'Peter'));

foreach ($sqlManager->fetchColumnMany($sqlBuilder) as $result)
{
    var_dump($result); // '1'
}
```

#### FetchRow

Returns all selected columns from a matched dataset. The example below returns ```id```, ```age``` for the matched dataset. If nothing got matched ```null``` will be returned.

```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setQuery('SELECT id, age FROM names WHERE name = :name')
    ->setConditions(array('name' => 'Peter'));

$result = $sqlManager->fetchRow($sqlBuilder);

var_dump($result); // ['id' => '1', 'age' => '22'] || null
```

#### FetchRowMany

Returns all selected columns from all matched dataset. The example below returns for each matched dataset ```id```, ```age```. If nothing got matched ```null``` will be returned.

```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setQuery('SELECT id, age FROM names WHERE name = :name')
    ->setConditions(array('name' => 'Peter'));

$result = $sqlManager->fetchRowMany($sqlBuilder);

var_dump($result); // [ ['id' => '1', 'age' => '22'],  ['id' => '15', 'age' => '40'], ... ] || null
```

#### FetchRowManyCursor

Same explanation as for ```FetchColumnManyCursor``` except that we receive all selected columns.

```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setQuery('SELECT id, age FROM names WHERE name = :name')
    ->setConditions(array('name' => 'Peter'));

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
$data = array(
    'id'   => false,
    'name' => 'Peter',
    'age'  => 45,
);

$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setTableName('names')
    ->setData($data);

$id = $sqlManager->insert($sqlBuilder);

var_dump($id); // 50 || false
```

The result depends on the table. If the table holds an ```autoincrementing ID``` column you will receive the ID count for the inserted data. If the table does not hold such a field you will receive ```true``` for a successful insert. If anything went bogus you will receive ```false```. 

#### Many datasets

Follow the example for inserting many datasets at once:

```php
$data = array(
    array(
        'id'   => false,
        'name' => 'Peter',
        'age'  => 45,
    ),
    array(
        'id'   => false,
        'name' => 'Peter',
        'age'  => 16,
    ),
);

$sqlBuilder = (new \Simplon\Mysql\Manager\SqlQueryBuilder())
    ->setTableName('names')
    ->setData($data);

$result = $sqlManager->insert($sqlBuilder);

var_dump($id); // [50, 51, ...] || false
```

The result depends on the table. If the table holds an ```autoincrementing ID``` column you will receive the ID count for the inserted data. If the table does not hold such a field you will receive ```true``` for a successful insert. If anything went bogus you will receive ```false```. 

### 5.3. Update

#### Simple update statement

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If nothing has been updated you will receive ```null```.

```php
$data = array(
    'name' => 'Peter',
    'age'  => 50,
);

$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setTableName('names')
    ->setConditions(array('id' => 50))
    ->setData($data);

$result = $sqlManager->update($sqlBuilder);

var_dump($result); // true || null
```

#### Custom update conditions query

Same as for insert statements accounts for updates. Its easy to understand. If the update succeeded the response will be ```true```. If nothing has been updated you will receive ```null```.

```php
$data = array(
    'name' => 'Peter',
    'age'  => 50,
);

$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setTableName('names')
    ->setConditions(array('id' => 50))
    ->setConditionsQuery('id = :id OR name =: name')
    ->setData($data)

$result = $sqlManager->update($sqlBuilder);

var_dump($result); // true || null
```

### 5.4. Replace

As MySQL states it: ```REPLACE``` works exactly like ```INSERT```, except that if an old row in the table has the same value as a new row for a ```PRIMARY KEY``` or a ```UNIQUE index```, the old row is deleted before the new row is inserted.

#### Replace a single datasets

As a result you will either receive the ```INSERT ID``` or ```false``` in case something went wrong.

```php
$data = array(
    'id'   => 5,
    'name' => 'Peter',
    'age'  => 16,
);

$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setTableName('names')
    ->setData($data);

$result = $sqlManager->replace($sqlBuilder);

var_dump($result); // 1 || false
```

#### Replace multiple datasets

As a result you will either receive an array of ```INSERT IDs``` or ```false``` in case something went wrong.

```php
$data = array(
    array(
        'id'   => 5,
        'name' => 'Peter',
        'age'  => 16,
    ),
    array(
        'id'   => 10,
        'name' => 'John',
        'age'  => 22,
    ),
);

$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setTableName('names')
    ->setData($data);

$result = $sqlManager->replaceMany($sqlBuilder);

var_dump($result); // [5, 10]  || false
```

### 5.5. Delete

#### Simple delete conditions

The following example demonstrates how to remove data. If the query succeeds we will receive ```true``` else ```false```.

```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setTableName('names')
    ->setConditions(array('id' => 50));

$result = $sqlManager->delete($sqlBuilder);

var_dump($result); // true || false
```

#### Custom delete conditions query

The following example demonstrates how to remove data with a custom conditions query. If the query succeeds we will receive ```true``` else ```false```.

```php
$sqlBuilder = new \Simplon\Mysql\Manager\SqlQueryBuilder();

$sqlBuilder
    ->setTableName('names')
    ->setConditions(array('id' => 50, 'name' => 'Peter'))
    ->setConditionsQuery('id = :id OR name =: name');

$result = $sqlManager->delete($sqlBuilder);

var_dump($result); // true || false
```

-------------------------------------------------

## 6. IN() Clause Handling

#### 6.1. The issue

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
#### 6.2. The solution

To take advantage of the built in ```IN() Clause``` with escaping and type handling do the following:

```php
// integers
$conds = array('ids' => array(1,2,3,4,5));
$query = "SELECT * FROM users WHERE id IN (:ids)";

// strings
$conds = array('emails' => array('johnny@me.com', 'peter@ibm.com'));
$query = "SELECT * FROM users WHERE email IN (:emails)";
```

-------------------------------------------------

## 7. CRUD Helper (CONTENT IS OUTDATED. UPDATE WILL FOLLLOW)

#### 7.1. Intro

```CRUD``` stands for ```Create Read Update Delete``` and reflects the for basic functions for persisent storage.

I found myself writing more and more CRUDs for all my object/database interactions simply for the reason of having a
```SINGLE POINT OF ACCESS``` when I was interacting with these objects for above mentioned functions. Eventually, it has
sort of a touch of a database model but with more flexibility. Also, we keep writing ```VALUE OBJECTS``` and by that we
keep the red line for all our code base.

__Note:__ ```VALUE OBJECTS``` are actually ```MODELS``` while models are not value objects. The reason for this is that a value
object is vehicle for all sorts of data while models are only vehicles for database data. At least that's what it should be.  

#### 7.2. Requirements/Restrictions

There are really __not many__ requirements/restrictions:

- Instance of ```SqlCrudManager``` - requires an instance of ```Simplon\Mysql```.
- Value object needs to extend from ```SqlCrudVo```
- Table name should be in plural or set it via ```SqlCrudVo::$crudSource``` within the value object.
- Value object's instance variables must match the table's column names in ```CamelCase``` (see example below).
- Each value object reflects ```ONE OBJECT``` only - ```Mysql::fetchRow()``` fetches your data.
- ```VARIABLE = COLUMN``` __Don't set any property in your value object__ which doesn't reflect your database table. __If you have to__,
    make either use of ```SqlCrudVo::crudColumns()``` or ```SqlCrudVo::crudIgnore()```. See ```Flexibility``` for description.
 
#### 7.3. Flexibility

- __Set source:__ In case you have a table name which can't be easily pluralised (e.g. person/people) you can set the source yourself via ```SqlCrudVo::$crudSource``` within value object

- __Set custom read query:__ In case you need a custom query to get your object you can set it when you instantiate the object ```new SqlCrudVo($query)``` or simply within your ```__construct() { parent::construct($query); }```. 

- __Callbacks:__ You can implement two methods which will be called prior/after saving an object: ```SqlCrudVo::crudBeforeSave($isCreateEvent)``` and ```SqlCrudVo::crudAfterSave($isCreateEvent)```. The manager
    will pass you a boolean to let you know what type of save process happens/happened. You could use this e.g. to set automatically ```created_at``` and ```updated_at``` fields. 

- __Set columns:__ If you have to either match property- and column name or only want a selection of your properties make use of ```SqlCrudVo::crudColumns()``` within your value object. It should return an array where the ```ARRAY KEY``` reflects the value object's ```VARIABLE NAME``` and the ```ARRAY VALUE``` the ```COLUMN NAME```.
    __Example:__ ```array('createdAt' => 'created_at')```

- __Ignore properties:__ Considering the prior point you could do the reverse and simply ```IGNORE VARIABLES```. For that implement ```SqlCrudVo::crudIgnore()``` which should return an array of properties you would like to ignore.

- __No assumptions:__ There are no assumptions about primary keys or anything alike. You set all conditions for reading, updating and/or deleting objects.

- __Casted values:__ Thanks to your value object which is always in between you and your database you can cast all values - good bye ```STRING CASTED ONLY``` values.

#### 7.4. Conclusion

That's all what is needed - at least for now. It's ```simple```, ```explicit``` and ```flexible``` enough not to restrict you in your requirements respectively your ```creativity```.

#### 7.5. Examples

Enough talk, bring it on! Alright, what is needed? Lets assume we have a database table called ```users``` and a value
object called ```UserVo```. __Note:__ the value object name has to be the singular of the table's plural name.

Here is the table schema:

```sql
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(254) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```

... and here is our value object for the given table:

```php
class UserVo extends \Simplon\Mysql\Crud\SqlCrudVo
{
    protected $id;
    protected $name;
    protected $email;
    protected $createdAt;
    protected $updatedAt;

    // ... here goes getter/setter for the above variables
} 
```

Now, lets do some CRUD, baby! For all processes we need an instance of our ```SqlCrudManager```:

```php
/**
* construct it with an instance of your simplon/mysql
*/
$sqlCrudManager = new \Simplon\Mysql\Crud\SqlCrudManager($mysqlInstance);
```

Create a user:

```php
$userVo = new UserVo();

$userVo
    ->setId(null)
    ->setName('Johnny Foobar')
    ->setEmail('foo@bar.com');

/** @var UserVo $userVo */
$userVo = $sqlCrudManager->create($userVo);

// print insert id
echo $userVo->getId(); // 1
```

Read a user:

```php
// conditions: where id = 1
$conds = array('id' => 1);

/** @var UserVo $userVo */
$userVo = $sqlCrudManager->read(new UserVo(), $conds);

// print name
echo $userVo->getName(); // Johnny Foobar
```

Update a user:

```php
// conditions: where id = 1
$conds = array('id' => 1);

/** @var UserVo $userVo */
$userVo = $sqlCrudManager->read(new UserVo(), $conds);

// set new name
$userVo->setName('Hansi Hinterseher');

// update
/** @var UserVo $userVo */
$userVo = $sqlCrudManager->update($userVo, $conds);

// print name
echo $userVo->getName(); // Hansi Hinterseher
```

Delete a user:

```php
// conditions: where id = 1
$conds = array('id' => 1);

/**
* UserVo::crudGetSource() is the name of the table
* based on the value object's name
*/
$sqlCrudManager->update(UserVo::crudGetSource(), $conds);
```

#### 7.6. Example Custom Vo

Setting a ```custom table name``` since the plural from person is not persons:

```php
class PersonVo extends \Simplon\Mysql\Crud\SqlCrudVo
{
    /**
    * @return string
    */
    public static function crudGetSource()
    {
        return 'people';
    }

    // ... here goes the rest
}
```

In case your ```column names``` are totally off there is a way to match them anyway against your ```properties```:

```php
class UserVo extends \Simplon\Mysql\Crud\SqlCrudVo
{
    protected $id;
    protected $name;
    protected $email;
    protected $createdAt;
    protected $updatedAt;

    /**
    * @return array
    */
    public function crudColumns()
    {
        return array(
            'id'        => 'xx_id',
            'name'      => 'xx_name',
            'email'     => 'xx_email',
            'createdAt' => 'xx_created_at',
            'updatedAt' => 'xx_updated_at',
        );
    }

    // ... here goes the rest
}
```

Sometimes there are some ```helper properties``` which are not part of your database entry. Here is a way to ignore them:

```php
class UserVo extends \Simplon\Mysql\Crud\SqlCrudVo
{
    protected $id;
    protected $name;
    protected $email;
    protected $createdAt;
    protected $updatedAt;

    // helper property: not part of the people table
    protected $isOffline;

    /**
    * @return array
    */
    public function crudIgnore()
    {
        return array(
            'isOffline',
        );
    }

    // ... here goes the rest
}
```

-------------------------------------------------

## 8. Exceptions

For both access methods (direct, sqlmanager) occuring exceptions will be wrapped by a ```MysqlException```. All essential exception information will be summarised as ```JSON``` within the ```Exception Message```.

Here is an example of how that might look like:

```bash
{"query":"SELECT pro_id FROM names WHERE connector_type = :connectorType","params":{"connectorType":"FB"},"errorInfo":{"sqlStateCode":"42S22","code":1054,"message":"Unknown column 'pro_id' in 'field list'"}}
```

-------------------------------------------------

# License

Simplon/Mysql is freely distributable under the terms of the MIT license.

Copyright (c) 2015 Tino Ehrich ([tino@bigpun.me](mailto:tino@bigpun.me))

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
