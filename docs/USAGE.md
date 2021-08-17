# Usage

## Configure Database Connection

Database configuration is same like CodeIniter 4. See [here](https://www.codeigniter.com/user_guide/database/configuration.html) for more information.

Refer [here](https://www.php.net/manual/en/ref.pdo-informix.connection.php#122191) for more information about PDO DNS.

E.g if using `.env`

```shell
database.default.DSN = informix:host=host.domain.com;service=9800;database=default;server=product;protocol=onsoctcp;EnableScrollableCursors=1
database.default.username = root
database.default.password =
database.default.DBPrefix =
database.default.charset = utf8
database.default.DBCollat = utf8_general_ci
```

## Create Database Connection

Choose either one from two (2) methods to connect to PDO Informix database. .

E.g using `default` or `default2` for database connection. 

```php
// method 1
$builder = new \Nfaiz\PdoIfx\Query('default');
$builder2 = new \Nfaiz\PdoIfx\Query('default2');

// method 2
$builder = ifx_connect('default');
$builder2 = ifx_connect('default2');

```

Note: If no database connection is specified **$defaultGroup** value in `app/Config/Database.php` will be used.


## Methods

Table of Contents

 * [select](#select)
 * [select functions (min, max, sum, avg, count)](#select-functions-min-max-sum-avg-count)
 * [table](#table)
 * [getRow AND getResult](#getrow-and-getresult)
 * [join](#join)
 * [where](#where)
 * [grouped](#grouped)
 * [whereIn](#wherein)
 * [between](#between)
 * [like](#like)
 * [groupBy](#groupby)
 * [having](#having)
 * [orderBy](#orderby)
 * [limit - offset](#limit---offset)
 * [pagination](#pagination)
 * [insert](#insert)
 * [update](#update)
 * [delete](#delete)
 * [query](#query)
 * [insertId](#insertid)
 * [numRows](#numrows)
 * [transaction](#transaction)
   * commit
   * rollBack
 * [error](#error)
 * [queryCount](#querycount)
 * [getLastQuery](#getlastquery)



### select
```php
// Usage 1: string parameter
$builder->select('name, email')->table('users')->getResult();
// Produces: "SELECT title, content FROM users"

$builder->select('name AS n, email AS e')->table('users')->getResult();
// Produces: "SELECT name AS n, email AS e FROM users"
```
```php
// Usage2: array parameter
$builder->select(['name', 'email'])->table('users')->getResult();
// Produces: "SELECT name, email FROM users"

$builder->select(['name AS n', 'email AS e'])->table('users')->getResult();
// Produces: "SELECT name AS n, email AS e FROM users"
```

### select functions (min, max, sum, avg, count)
```php
// Usage 1:
$builder->table('users')->max('age')->getRow();
// Produces: "SELECT MAX(age) FROM users"

// Usage 2:
$builder->table('users')->count('id', 'total_row')->getRow();
// Produces: "SELECT COUNT(id) AS total_row FROM users"
```

### table
```php
// Usage 1: string parameter
$builder->table('table');
// Produces: "SELECT * FROM table" (not final query)

$builder->table('table1, table2');
// Produces: "SELECT * FROM table1, table2" (not final query)

$builder->table('table1 AS t1, table2 AS t2');
// Produces: "SELECT * FROM table1 AS t1, table2 AS t2" (not final query)
```
```php
// Usage 2: array parameter
$builder->table(['table1', 'table2']);
// Produces: "SELECT * FROM table1, table2" (not final query)

$builder->table(['table1 AS t1', 'table2 AS t2']);
// Produces: "SELECT * FROM table1 AS t1, table2 AS t2" (not final query)
```

### getRow AND getResult
```php
// getRow() and getRowArray(): return 1 record.
// getResult() and getResultArray: return multiple records.

$builder->table('users')->getResult(); 
// Produces: "SELECT * FROM users"

$builder->select('username')->table('users')->where('status', 1)->getResult();
// Produces: "SELECT username FROM users WHERE status = 1"

$builder->select('email')->table('users')->where('id', 17)->getRow(); 
// Produces: "SELECT FIRST 1 email FROM users WHERE id = 17"
```

### join
```php
$builder->table('users as u')->join('address as a', 'u.id = a.uid', 'left')->where('u.status', 1)->getResult();
// Produces: "SELECT * FROM users as u LEFT JOIN address as a ON u.id = a.t_id WHERE u.status = 1"
```
Available **join** types are;
- LEFT
- RIGHT
- OUTER
- INNER
- LEFT OUTER
- RIGHT OUTER


### where
```php
$where = [
    'name' => 'Faiz',
    'age' => 37,
    'status' => 1
];
$builder->table('users')->where($where)->getRow();
// Produces: "SELECT FIRST 1 * FROM users WHERE name = 'Faiz' AND age = 37 AND status = 1"

// OR

$builder->table('users')->where('active', 1)->getResult();
// Produces: "SELECT * FROM users WHERE active = 1"

// OR

$builder->table('users')->where('age', '>=', 18)->getResult();
// Produces: "SELECT * FROM users WHERE age >= 18"

// OR

$builder->table('users')->where('age = ? OR age = ?', [18, 20])->getResult();
// Produces: "SELECT * FROM users WHERE age = 18 OR age = 20"
```

Available **where** methods are;

- where
- orWhere
- notWhere
- orNotWhere
- whereNull
- whereNotNull

Example:
```php
$builder->table('users')->where('active', 1)->notWhere('gender', 'M')->getResult();
// Produces: "SELECT * FROM users WHERE active = 1 AND NOT gender = 'M'"

// OR

$builder->table('users')->where('age', 20)->orWhere('age', '>', 25)->getResult();
// Produces: "SELECT * FROM users WHERE age = 20 OR age > 25"

$builder->table('users')->whereNotNull('email')->getResult();
// Produces: "SELECT * FROM users WHERE email IS NOT NULL"
```

### grouped
```php
$builder->table('address')
    ->grouped(function($q) {
        $q->where('country', 'MAL')->orWhere('country', 'IDN');
    })
    ->where('uid', 1)
    ->getResult();
// Produces: "SELECT * FROM address WHERE (country='MAL' OR country = 'IDN') AND uid = '1'"
```

### WhereIn
```php
$builder->table('users')->where('active', 1)->WhereIn('id', [1, 2, 3])->getResult();
// Produces: "SELECT * FROM users WHERE active = 1 AND id IN ('1', '2', '3')"
```

Available **whereIn** methods are;

- whereIn
- whereNotIn
- orWhereIn
- orWhereNotIn

Example:
```php
$builder->table('users')->where('active', 1)->whereNotIn('id', [1, 2, 3])->getResult();
// Produces: "SELECT * FROM users WHERE active = 1 AND id NOT IN ('1', '2', '3')"

// OR

$builder->table('users')->where('active', 1)->orWhereIn('id', [1, 2, 3])->getResult();
// Produces: "SELECT * FROM users WHERE active = 1 OR id IN ('1', '2', '3')"
```

### between
```php
$builder->table('users')->where('active', 1)->between('age', 18, 25)->getResult();
// Produces: "SELECT * FROM users WHERE active = 1 AND age BETWEEN '18' AND '25'"
```

Available **between** methods are;

- between
- orBetween
- notBetween
- orNotBetween

Example:
```php
$builder->table('users')->where('active', 1)->notBetween('age', 18, 25)->getResult();
// Produces: "SELECT * FROM users WHERE active = 1 AND age NOT BETWEEN '18' AND '25'"

// OR

$builder->table('users')->where('active', 1)->orBetween('age', 18, 25)->getResult();
// Produces: "SELECT * FROM users WHERE active = 1 OR age BETWEEN '18' AND '25'"
```

### like
```php
$builder->table('users')->like('name', "%faiz%")->getResult();
// Produces: "SELECT * FROM users WHERE name LIKE '%faiz%'"
```

Available like methods are;

- like
- orLike
- notLike
- orNotLike

Example:
```php
$builder->table('users')->where('active', 1)->notLike('name', '%faiz%')->getResult();
// Produces: "SELECT * FROM users WHERE active = 1 AND name NOT LIKE '%faiz%'"

// OR

$builder->table('users')->like('name', '%faiz%')->orLike('name', '%ateman%')->getResult();
// Produces: "SELECT * FROM users WHERE name LIKE '%faiz%' OR name LIKE '%ateman%'"
```

### groupBy
```php
// Usage 1: One parameter
$builder->table('users')->where('status', 1)->groupBy('gender')->getResult();
// Produces: "SELECT * FROM users WHERE status = 1 GROUP BY gender"
```

```php
// Usage 1: Array parameter
$builder->table('users')->where('status', 1)->groupBy(['gender', 'religion'])->getResult();
// Produces: "SELECT * FROM users WHERE status = 1 GROUP BY gender, religion"
```


### having
```php
$builder->select('COUNT(gender), country')->table('users')->where('status', 1)->groupBy('gender, country')->having('COUNT(gender)', 100)->getResult();
// Produces: "SELECT COUNT(gender), country FROM users WHERE status = 1 GROUP BY gender, country HAVING COUNT(gender) > '100'"

// OR

$builder->table('users')->where('active', 1)->groupBy('gender')->having('AVG(age)', '<=', 18)->getResult();
// Produces: "SELECT * FROM users WHERE active='1' GROUP BY gender HAVING AVG(age) <= '18'"

// OR

$builder->table('users')->where('active', 1)->groupBy('gender')->having('AVG(age) > ? AND MAX(age) < ?', [18, 30])->getResult();
// Produces: "SELECT * FROM users WHERE active='1' GROUP BY gender HAVING AVG(age) > 18 AND MAX(age) < 30"
```

### orderBy
```php
// Usage 1: One parameter
$builder->table('users')->where('status', 1)->orderBy('id')->getResult();
// Produces: "SELECT * FROM test WHERE status = 1 ORDER BY id ASC"

// OR

$builder->table('users')->where('status', 1)->orderBy('id desc')->getResult();
// Produces: "SELECT * FROM test WHERE status = 1 ORDER BY id desc"
```

```php
// Usage 1: Two parameters
$builder->table('users')->where('status', 1)->orderBy('id', 'desc')->getResult();
// Produces: "SELECT * FROM users WHERE status = 1 ORDER BY id DESC"
```

```php
// Usage 3: Rand()
$builder->table('users')->where('status', 1)->orderBy(2)->limit(10)->getResult();
// Produces: "SELECT FIRST 10 * FROM users WHERE status = 1 ORDER BY 2"
```

### limit - offset
```php
// Usage 1: One parameter
$builder->table('users')->limit(10)->getResult();
// Produces: "SELECT FIRST 10 * FROM users"
```
```php
// Usage 2: with offset method
$builder->table('users')->limit(10)->offset(10)->getResult();
// Produces: "SELECT SKIP 10 FIRST 10 * FROM users"
```

### pagination
```php
// First parameter: Data count of per page
// Second parameter: Active page

$builder->table('users')->pagination(15, 1)->getResult();
// Produces: "SELECT SKIP 0 FIRST 15 * FROM users"

$builder->table('users')->pagination(15, 2)->getResult();
// Produces: "SELECT SKIP 15 FIRST 15 * FROM users"
```

### insert
```php
$data = [
    'name' => 'Faiz',
    'gender' => 'M',
    'dob' => date('m/d/Y', strtotime('2021-08-18')),
    'status' => 1
];

$builder->table('users')->insert($data);
// Produces: "INSERT INTO users (name, gender, dob, status) VALUES ('faiz', 'M', '18/08/2021', '1')"
```

### update
```php
$data = [
    'username' => 'Faiz',
    'password' => 'pass',
    'activation' => 1,
    'status' => 1
];

$builder->table('account')->where('id', 10)->update($data);
// Produces: "UPDATE account SET username = 'Faiz', password = 'pass', activation = 1, status = 1 WHERE id = 10"
```

### delete
```php
$builder->table('users')->where('id', 17)->delete();
// Produces: "DELETE FROM users WHERE id = 1"

// OR

$builder->table('users')->delete();
// Produces: "TRUNCATE TABLE users"
```

### transaction
```php
try
{
    $builder->beginTransaction();

    $builder->table('test')->insert([
        'id' => 10
        'title' => 'title',
        'status' => 2
    ]);

    $builder->table('test')->where('id', 10)->update([
        'title' => 'new title',
        'status' => 2
    ]);

    $builder->commit();
}
catch (\PDOException $e)
{
    $builder->rollBack();
    die($e->getMessage());
}
```

### query
```php
// Usage 1: Select all records, returns object
$builder->query('SELECT * FROM users WHERE id = ? AND status = ?', [10, 1])->fetchAll();

// Usage 1: Select all records, returns array
$builder->query('SELECT * FROM users WHERE id = ? AND status = ?', [10, 1])->fetchAllArray();

// Usage 2: Select one record, returns object
$builder->query('SELECT * FROM users WHERE id = ? AND status = ?', [10, 1])->fetch();

// Usage 2: Select one record, returns array
$builder->query('SELECT * FROM users WHERE id = ? AND status = ?', [10, 1])->fetchArray();

// Usage 3: Other queries like Update, Insert, Delete etc...
$builder->query('DELETE FROM users WHERE id = ?', [10])->exec();
```

### insertId
```php
$data = [
    'username' => 'Faiz',
    'password' => 'pass',
    'time' => strtotime('now'),
    'status' => 1
];
$builder->table('account')->insert($data);

d($builder->insertId());
```

### numRows
```php
$builder->select('name, gender')->table('users')->where('status', 1)->orWhere('status', 2)->getResult();

d($builder->numRows());
```

### error
```php
$builder->error();
```

### queryCount
```php
$builder->queryCount(); 
// The number of all SQL queries on the page until the end of the beginning.
```

### getLastQuery
```php
$builder->getLastQuery(); 
// Get last SQL Query.
```
