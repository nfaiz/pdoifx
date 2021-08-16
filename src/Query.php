<?php

namespace Nfaiz\PdoIfx;

use CodeIgniter\Events\Events;
use Nfaiz\PdoIfx\Connection;

use Closure;
use PDO;
use PDOException;

class Query
{
    public $pdo = null;

    protected $select = '*';

    protected $from = null;

    protected $where = null;

    protected $limit = null;

    protected $offset = null;

    protected $join = null;

    protected $orderBy = null;

    protected $groupBy = null;

    protected $having = null;

    protected $grouped = false;

    protected $numRows = 0;

    protected $insertId = null;

    protected $query = null;

    protected $error = null;

    protected $result = [];

    protected $prefix = null;

    protected $joinTypes = [
        'LEFT',
        'RIGHT',
        'OUTER',
        'INNER',
        'LEFT OUTER',
        'RIGHT OUTER',
    ];

    protected $operators = ['=', '!=', '<', '>', '<=', '>=', '<>'];

    protected $queryCount = 0;

    protected $transactionCount = 0;

    protected $connectTime;

    protected $connectDuration;

    protected $instance;

    public function __construct(string $instance = '')
    {
        $informix = new Connection($instance);

        $this->pdo = $informix->getPdo();

        $this->connectTime = $informix->connectTime;

        $this->connectDuration = $informix->connectDuration;

        $this->prefix = $informix->prefix ?: '';

        $this->instance = $instance;

        return $this->pdo;
    }

    /**
     * @param $table
     *
     * @return $this
     */
    public function table($table)
    {
        if (is_array($table))
        {
            $from = '';

            foreach ($table as $key)
            {
                $from .= $this->prefix . $key . ', ';
            }

            $this->from = rtrim($from, ', ');
        }
        else
        {
            if (strpos($table, ',') > 0)
            {
                $tables = explode(',', $table);

                foreach ($tables as $key => &$value)
                {
                    $value = $this->prefix . ltrim($value);
                }

                $this->from = implode(', ', $tables);
            }
            else
            {
                $this->from = $this->prefix . $table;
            }
        }

        return $this;
    }

    /**
     * @param array|string $fields
     *
     * @return $this
     */
    public function select($fields)
    {
        $select = is_array($fields) ? implode(', ', $fields) : $fields;

        $this->optimizeSelect($select);

        return $this;
    }

    /**
     * @param string      $field
     * @param string|null $name
     *
     * @return $this
     */
    public function max(string $field, $name = null)
    {
        $column = 'MAX(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');

        $this->optimizeSelect($column);

        return $this;
    }

    /**
     * @param string      $field
     * @param string|null $name
     *
     * @return $this
     */
    public function min(string $field, $name = null)
    {
        $column = 'MIN(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');

        $this->optimizeSelect($column);

        return $this;
    }

    /**
     * @param string      $field
     * @param string|null $name
     *
     * @return $this
     */
    public function sum(string $field, $name = null)
    {
        $column = 'SUM(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');

        $this->optimizeSelect($column);

        return $this;
    }

    /**
     * @param string      $field
     * @param string|null $name
     *
     * @return $this
     */
    public function count(string $field, $name = null)
    {
        $column = 'COUNT(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');

        $this->optimizeSelect($column);

        return $this;
    }

    /**
     * @param string      $field
     * @param string|null $name
     *
     * @return $this
     */
    public function avg(string $field, $name = null)
    {
        $column = 'AVG(' . $field . ')' . (! is_null($name) ? ' AS ' . $name : '');

        $this->optimizeSelect($column);

        return $this;
    }

    /**
     * JOIN
     *
     * Generates the JOIN portion of the query
     *
     * @param string  $table
     * @param string  $cond   The join condition
     * @param string  $type   The type of join
     * @param boolean $escape Whether not to try to escape identifiers
     *
     * @return $this
     */
    public function join(string $table, string $cond, string $type = '', bool $escape = null)
    {
        if ($type !== '')
        {
            $type = strtoupper(trim($type));

            if (! in_array($type, $this->joinTypes, true))
            {
                $type = '';
            }
            else
            {
                $type .= ' ';
            }
        }

        if (! $this->hasOperator($cond))
        {
            $cond = ' USING (' . $cond . ')';
        }
        elseif ($escape === false)
        {
            $cond = ' ON ' . $cond;
        }
        else
        {
            // Split multiple conditions
            if (preg_match_all('/\sAND\s|\sOR\s/i', $cond, $joints, PREG_OFFSET_CAPTURE))
            {
                $conditions = [];

                $joints     = $joints[0];

                array_unshift($joints, ['', 0]);

                for ($i = count($joints) - 1, $pos = strlen($cond); $i >= 0; $i --)
                {
                    $joints[$i][1] += strlen($joints[$i][0]); // offset

                    $conditions[$i] = substr($cond, $joints[$i][1], $pos - $joints[$i][1]);

                    $pos            = $joints[$i][1] - strlen($joints[$i][0]);

                    $joints[$i]     = $joints[$i][0];
                }

                ksort($conditions);
            }
            else
            {
                $conditions = [$cond];

                $joints     = [''];
            }

            $cond = ' ON ';

            foreach ($conditions as $i => $condition)
            {
                $operator = $this->getOperator($condition);

                $cond .= $joints[$i];

                $cond .= preg_match("/(\(*)?([\[\]\w\.'-]+)" . preg_quote($operator) . '(.*)/i', $condition, $match) 
                    ? $match[1] . $match[2] . $operator . $match[3] 
                    : $condition;
            }
        }

        // Assemble the JOIN statement
        $this->join = ' ' . $type . ' JOIN ' . $table . $cond;

        return $this;
    }

    /**
     * Tests whether the string has an SQL operator
     *
     * @param string $str
     *
     * @return boolean
     */
    protected function hasOperator(string $str): bool
    {
        return (bool) preg_match('/(<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i', trim($str));
    }

    /**
     * Returns the SQL string operator
     *
     * @param string  $str
     * @param boolean $list
     *
     * @return mixed
     */
    protected function getOperator(string $str, bool $list = false)
    {
        static $_operators;

        if (empty($_operators))
        {
            $_les       = '';

            $_operators = [
                '\s*(?:<|>|!)?=\s*', // =, <=, >=, !=
                '\s*<>?\s*', // <, <>
                '\s*>\s*', // >
                '\s+IS NULL', // IS NULL
                '\s+IS NOT NULL', // IS NOT NULL
                '\s+EXISTS\s*\(.*\)', // EXISTS(sql)
                '\s+NOT EXISTS\s*\(.*\)', // NOT EXISTS(sql)
                '\s+BETWEEN\s+', // BETWEEN value AND value
                '\s+IN\s*\(.*\)', // IN(list)
                '\s+NOT IN\s*\(.*\)', // NOT IN (list)
                '\s+LIKE\s+\S.*(' . $_les . ')?', // LIKE 'expr'[ ESCAPE '%s']
                '\s+NOT LIKE\s+\S.*(' . $_les . ')?', // NOT LIKE 'expr'[ ESCAPE '%s']
            ];
        }

        return preg_match_all('/' . implode('|', $_operators) . '/i', $str, $match) 
            ? ($list ? $match[0] : $match[0][0]) 
            : false;
    }

    /**
     * @param array|string $where
     * @param string       $operator
     * @param string       $val
     * @param string       $type
     * @param string       $andOr
     *
     * @return $this
     */
    public function where($where, $operator = null, $val = null, $type = '', $andOr = 'AND')
    {
        if (is_array($where) && !empty($where))
        {
            $_where = [];

            foreach ($where as $column => $data)
            {
                $_where[] = $type . $column . '=' . $this->escape($data);
            }

            $where = implode(' ' . $andOr . ' ', $_where);
        }
        else
        {
            if (is_null($where) || empty($where))
            {
                return $this;
            }

            if (is_array($operator))
            {
                $params = explode('?', $where);

                $_where = '';

                foreach ($params as $key => $value)
                {
                    if (!empty($value))
                    {
                        $_where .= $type . $value . (isset($operator[$key]) ? $this->escape($operator[$key]) : '');
                    }
                }
                $where = $_where;
            }
            elseif (!in_array($operator, $this->operators) || $operator == false)
            {
                $where = $type . $where . ' = ' . $this->escape($operator);
            }
            else
            {
                $where = $type . $where . ' ' . $operator . ' ' . $this->escape($val);
            }
        }

        if ($this->grouped)
        {
            $where = '(' . $where;

            $this->grouped = false;
        }

        $this->where = is_null($this->where)
            ? $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    /**
     * @param array|string $where
     * @param string|null  $operator
     * @param string|null  $val
     *
     * @return $this
     */
    public function orWhere($where, $operator = null, $val = null)
    {
        return $this->where($where, $operator, $val, '', 'OR');
    }

    /**
     * @param array|string $where
     * @param string|null  $operator
     * @param string|null  $val
     *
     * @return $this
     */
    public function notWhere($where, $operator = null, $val = null)
    {
        return $this->where($where, $operator, $val, 'NOT ', 'AND');
    }

    /**
     * @param array|string $where
     * @param string|null  $operator
     * @param string|null  $val
     *
     * @return $this
     */
    public function orNotWhere($where, $operator = null, $val = null)
    {
        return $this->where($where, $operator, $val, 'NOT ', 'OR');
    }

    /**
     * @param string $where
     * @param bool   $not
     *
     * @return $this
     */
    public function whereNull(string $where, bool $not = false)
    {
        $where = $where . ' IS ' . ($not ? 'NOT' : '') . ' NULL';

        $this->where = is_null($this->where) ? $where : $this->where . ' ' . 'AND ' . $where;

        return $this;
    }

    /**
     * @param string $where
     *
     * @return $this
     */
    public function whereNotNull(string $where)
    {
        return $this->whereNull($where, true);
    }

    /**
     * @param Closure $obj
     *
     * @return $this
     */
    public function grouped(Closure $obj)
    {
        $this->grouped = true;

        call_user_func_array($obj, [$this]);

        $this->where .= ')';

        return $this;
    }

    /**
     * @param string $field
     * @param array  $keys
     * @param string $type
     * @param string $andOr
     *
     * @return $this
     */
    public function whereIn(string $field, array $keys, string $type = '', string $andOr = 'AND')
    {
        if (is_array($keys))
        {
            $_keys = [];

            foreach ($keys as $k => $v)
            {
                $_keys[] = is_numeric($v) ? $v : $this->escape($v);
            }

            $where = $field . ' ' . $type . 'IN (' . implode(', ', $_keys) . ')';

            if ($this->grouped)
            {
                $where = '(' . $where;
                $this->grouped = false;
            }

            $this->where = is_null($this->where)
                ? $where
                : $this->where . ' ' . $andOr . ' ' . $where;
        }

        return $this;
    }

    /**
     * @param string $field
     * @param array  $keys
     *
     * @return $this
     */
    public function whereNotIn(string $field, array $keys)
    {
        return $this->in($field, $keys, 'NOT ', 'AND');
    }

    /**
     * @param string $field
     * @param array  $keys
     *
     * @return $this
     */
    public function orWhereIn(string $field, array $keys)
    {
        return $this->in($field, $keys, '', 'OR');
    }

    /**
     * @param string $field
     * @param array  $keys
     *
     * @return $this
     */
    public function orWhereNotIn(string $field, array $keys)
    {
        return $this->in($field, $keys, 'NOT ', 'OR');
    }

    /**
     * @param string     $field
     * @param string|int $value1
     * @param string|int $value2
     * @param string     $type
     * @param string     $andOr
     *
     * @return $this
     */
    public function between(string $field, $value1, $value2, $type = '', $andOr = 'AND')
    {
        $where = '(' . $field . ' ' . $type . 'BETWEEN ' . ($this->escape($value1) . ' AND ' . $this->escape($value2)) . ')';

        if ($this->grouped)
        {
            $where = '(' . $where;
            $this->grouped = false;
        }

        $this->where = is_null($this->where)
            ? $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    /**
     * @param string     $field
     * @param string|int $value1
     * @param string|int $value2
     *
     * @return $this
     */
    public function notBetween(string $field, $value1, $value2)
    {
        return $this->between($field, $value1, $value2, 'NOT ', 'AND');
    }

    /**
     * @param string     $field
     * @param string|int $value1
     * @param string|int $value2
     *
     * @return $this
     */
    public function orBetween(string $field, $value1, $value2)
    {
        return $this->between($field, $value1, $value2, '', 'OR');
    }

    /**
     * @param string     $field
     * @param string|int $value1
     * @param string|int $value2
     *
     * @return $this
     */
    public function orNotBetween(string $field, $value1, $value2)
    {
        return $this->between($field, $value1, $value2, 'NOT ', 'OR');
    }

    /**
     * @param string $field
     * @param string $data
     * @param string $type
     * @param string $andOr
     *
     * @return $this
     */
    public function like(string $field, string $data, string $type = '', string $andOr = 'AND')
    {
        $like = $this->escape($data);

        $where = $field . ' ' . $type . 'LIKE ' . $like;

        if ($this->grouped)
        {
            $where = '(' . $where;
            $this->grouped = false;
        }

        $this->where = is_null($this->where)
            ? $where
            : $this->where . ' ' . $andOr . ' ' . $where;

        return $this;
    }

    /**
     * @return $this
     */
    public function orLike(string $field, string $data)
    {
        return $this->like($field, $data, '', 'OR');
    }

    /**
     * @return $this
     */
    public function notLike(string $field, string $data)
    {
        return $this->like($field, $data, 'NOT ', 'AND');
    }

    /**
     * @return $this
     */
    public function orNotLike(string $field, string $data)
    {
        return $this->like($field, $data, 'NOT ', 'OR');
    }

    /**
     * @param int      $limit
     *
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int      $first
     *
     * @return $this
     */
    public function first(int $first)
    {
        return $this->limit($first);
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param int $skip
     *
     * @return $this
     */
    public function skip(int $skip)
    {
        return $this->offset($skip);
    }

    /**
     * @param int $perPage
     * @param int $page
     *
     * @return $this
     */
    public function pagination($perPage, $page)
    {
        $this->limit = $perPage;

        $this->offset = (($page > 0 ? $page : 1) - 1) * $perPage;

        return $this;
    }

    /**
     * @param string      $orderBy
     * @param string|null $orderDir
     *
     * @return $this
     */
    public function orderBy(string $orderBy, $orderDir = null)
    {
        if (! is_null($orderDir))
        {
            $this->orderBy = $orderBy . ' ' . strtoupper($orderDir);
        }
        else
        {
            $this->orderBy = stristr($orderBy, ' ')
                ? $orderBy
                : $orderBy . ' ASC';
        }

        return $this;
    }

    /**
     * Group By
     *
     * @param string|array $groupBy
     *
     * @return $this
     */
    public function groupBy($groupBy)
    {
        $this->groupBy = is_array($groupBy) ? implode(', ', $groupBy) : $groupBy;

        return $this;
    }

    /**
     * Having
     *
     * @return $this
     */
    public function having(string $field, $operator = null, $val = null)
    {
        if (is_array($operator))
        {
            $fields = explode('?', $field);

            $where = '';

            foreach ($fields as $key => $value)
            {
                if (!empty($value))
                {
                    $where .= $value . (isset($operator[$key]) ? $this->escape($operator[$key]) : '');
                }
            }

            $this->having = $where;
        }
        elseif (!in_array($operator, $this->operators))
        {
            $this->having = $field . ' > ' . $this->escape($operator);
        }
        else
        {
            $this->having = $field . ' ' . $operator . ' ' . $this->escape($val);
        }

        return $this;
    }

    /**
     * Number of Rows
     *
     * @return int
     */
    public function numRows(): int
    {
        return $this->numRows;
    }

    /**
     * Insert Id
     *
     * @return int|null
     */
    public function insertId()
    {
        return $this->insertId;
    }

    /**
     * Error
     *
     * @throw PDOException
     */
    public function error()
    {
        throw new PDOException($this->error . PHP_EOL . $this->query);
    }

    /**
     * @param string|bool $type
     * @param string|null $argument
     *
     * @return mixed
     */
    public function get($type = null, $argument = null)
    {
        $this->limit = 1;

        $query = $this->getAll(true);

        return $type === true ? $query : $this->query($query, false, $type, $argument);
    }

    /**
     * @param string|bool $type
     * @param string|null $argument
     *
     * @return mixed
     */
    public function getRow($type = null, $argument = null)
    {
        return $this->get($type, $argument);
    }

    /**
     * @param string|bool $type
     * @param string|null $argument
     *
     * @return mixed
     */
    public function getRowArray($type = 'array', $argument = null)
    {
        return $this->get($type, $argument);
    }

    /**
     * Select
     *
     * @return mixed
     */
    public function getAll($type = null, $argument = null)
    {
        $query = 'SELECT ';

        if (! is_null($this->offset))
        {
            $query .= 'SKIP ' . $this->offset . ' ';
        }

        if (! is_null($this->limit))
        {
            $query .= 'FIRST ' . $this->limit . ' ';
        }

        $query .= $this->select . ' FROM ' . $this->from;

        if (! is_null($this->join))
        {
            $query .= $this->join;
        }

        if (! is_null($this->where))
        {
            $query .= ' WHERE ' . $this->where;
        }

        if (! is_null($this->groupBy))
        {
            $query .= ' GROUP BY ' . $this->groupBy;
        }

        if (! is_null($this->having))
        {
            $query .= ' HAVING ' . $this->having;
        }

        if (! is_null($this->orderBy))
        {
            $query .= ' ORDER BY ' . $this->orderBy;
        }

        return $type === true ? $query : $this->query($query, true, $type, $argument);
    }

    public function getResult($type = null, $argument = null)
    {
         return $this->getAll($type, $argument);
    }

    public function getResultArray($type = null, $argument = null)
    {
        return $this->getAll('array', $argument);
    }

    /**
     * Insert
     *
     * @return mixed
     */
    public function insert(array $data, bool $type = false)
    {
        $query = 'INSERT INTO ' . $this->from;

        $values = array_values($data);

        if (isset($values[0]) && is_array($values[0]))
        {
            $column = implode(', ', array_keys($values[0]));

            $query .= ' (' . $column . ') VALUES ';

            foreach ($values as $value)
            {
                $val = implode(', ', array_map([$this, 'escape'], $value));

                $query .= '(' . $val . '), ';
            }

            $query = trim($query, ', ');
        }
        else
        {
            $column = implode(', ', array_keys($data));

            $val = implode(', ', array_map([$this, 'escape'], $data));

            $query .= ' (' . $column . ') VALUES (' . $val . ')';
        }

        if ($type === true)
        {
            return $query;
        }

        if ($this->query($query, false))
        {
            $this->insertId = $this->pdo->lastInsertId();

            return $this->insertId();
        }

        return false;
    }

    /**
     * Update
     *
     * @return mixed
     */
    public function update(array $data, bool $type = false)
    {
        $query = 'UPDATE ' . $this->from . ' SET ';

        $values = [];

        foreach ($data as $column => $val)
        {
            $values[] = $column . ' = ' . $this->escape($val);
        }

        $query .= implode(', ', $values);

        if (! is_null($this->where))
        {
            $query .= ' WHERE ' . $this->where;
        }

        if (! is_null($this->orderBy))
        {
            $query .= ' ORDER BY ' . $this->orderBy;
        }

        return $type === true ? $query : $this->query($query, false);
    }

    /**
     * Delete
     *
     * @return mixed
     */
    public function delete(bool $type = false)
    {
        $query = 'DELETE FROM ' . $this->from;

        if (! is_null($this->where))
        {
            $query .= ' WHERE ' . $this->where;
        }

        if (! is_null($this->orderBy))
        {
            $query .= ' ORDER BY ' . $this->orderBy;
        }

        if ($query === 'DELETE FROM ' . $this->from)
        {
            $query = 'TRUNCATE TABLE ' . $this->from;
        }

        return $type === true ? $query : $this->query($query, false);
    }

    /**
     * Begin Transaction
     *
     * @return bool
     */
    public function beginTransaction()
    {
        if (! $this->transactionCount)
        {
            $this->transactionCount++;

            return $this->pdo->beginTransaction();
        }

        $this->pdo->exec('SAVEPOINT trans' . $this->transactionCount);

        return $this->transactionCount >= 0;
    }

    /**
     * Transaction (alias beginTransaction)
     *
     * @return bool
     */
    public function transaction()
    {
        return $this->beginTransaction();
    }

    /**
     * Commit
     *
     * @return bool
     */
    public function commit()
    {
        if ($this->transactionCount)
        {
            return $this->pdo->commit();
        }

        return $this->transactionCount >= 0;
    }

    /**
     * Rollback
     *
     * @return bool
     */
    public function rollBack()
    {
        if ($this->transactionCount)
        {
            $this->pdo->exec('ROLLBACK TO trans' . $this->transactionCount);

            $this->transactionCount--;

            return true;
        }

        return $this->pdo->rollBack();
    }

    /**
     * Exec
     *
     * @return mixed
     */
    public function exec()
    {
        if (is_null($this->query))
        {
            return null;
        }

        $start = microtime(true);

        $query = $this->pdo->exec($this->query);

        $end = microtime(true);

        if ($query === false)
        {
            $this->error = $this->pdo->errorInfo()[2];

            $this->error();
        }

        $this->triggerEvent($this->query, $start, $end, $query);

        return $query;
    }

    /**
     * Fetch
     *
     * @return mixed
     */
    public function fetch(string $type = null, $argument = null, $all = false)
    {
        if (is_null($this->query))
        {
            return null;
        }

        $start = microtime(true);

        $query = $this->pdo->query($this->query);

        if (!$query)
        {
            $this->error = $this->pdo->errorInfo()[2];

            $this->error();
        }

        $type = $this->getFetchType($type);

        if ($type === PDO::FETCH_CLASS)
        {
            $query->setFetchMode($type, $argument);
        }
        else
        {
            $query->setFetchMode($type);
        }

        $result = $all ? $query->fetchAll() : $query->fetch();

        $end = microtime(true);

        $this->numRows = is_array($result) ? count($result) : 1;

        $this->triggerEvent($this->query, $start, $end, $this->numRows);

        return $result;
    }

    /**
     * Fetch Array
     *
     * @return mixed
     */
    public function fetchArray(string $type = 'array', $argument = null, $all = false)
    {
        return $this->fetch($type, $argument, $all);
    }

    /**
     * Fetch All
     *
     * @return mixed
     */
    public function fetchAll(string $type = null, string $argument = null)
    {
        return $this->fetch($type, $argument, true);
    }

    /**
     * Fetch All
     *
     * @return mixed
     */
    public function fetchAllArray(string $type = 'array', string $argument = null)
    {
        return $this->fetchAll($type, $argument, true);
    }

    /**
     * Query
     *
     * @return $this|mixed
     */
    public function query($query, $all = true, $type = null, $argument = null)
    {
        $this->reset();

        if (is_array($all) || func_num_args() === 1)
        {
            $params = explode('?', $query);

            $newQuery = '';

            foreach ($params as $key => $value)
            {
                if (!empty($value))
                {
                    $newQuery .= $value . (isset($all[$key]) ? $this->escape($all[$key]) : '');
                }
            }

            $this->query = $newQuery;

            return $this;
        }

        $this->query = preg_replace('/\s\s+|\t\t+/', ' ', trim($query));

        $str = false;

        foreach (['select'] as $value)
        {
            if (stripos($this->query, $value) === 0)
            {
                $str = true;
                break;
            }
        }

        $type = $this->getFetchType($type);

        $start = microtime(true);

        if ($str)
        {
            $sql = $this->pdo->query($this->query);

            $end = microtime(true);

            if ($sql)
            {
                $this->numRows = $sql->rowCount();

                if ($type === PDO::FETCH_CLASS)
                {
                    $sql->setFetchMode($type, $argument);
                }
                else
                {
                    $sql->setFetchMode($type);
                }

                $this->result = $all ? $sql->fetchAll() : $sql->fetch();

            }
            else
            {
                $this->error = $this->pdo->errorInfo()[2];

                $this->error();
            }
        }
        else
        {
            $this->result = $this->pdo->exec($this->query);

            $end = microtime(true);

            if ($this->result === false)
            {
                $this->error = $this->pdo->errorInfo()[2];

                $this->error();
            }
        }

        $this->numRows = is_array($this->result) ? count($this->result) : ($this->result === '' ? 0 : 1);

        if ($type == PDO::FETCH_ASSOC)
        {
            $this->numRows = count($this->result) != count($this->result, COUNT_RECURSIVE)
                ? count($this->result)
                : ($this->result === '' ? 0 : 1);
        }

        $this->triggerEvent($this->query, $start, $end, $this->numRows);

        $this->queryCount++;

        return $this->result;
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function escape($data)
    {
        return $data === null ? 'NULL' : (
            is_int($data) || is_float($data) ? $data : $this->pdo->quote($data)
        );
    }

    /**
     * @return int
     */
    public function queryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * @return string|null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Alias getQuery
     *
     * @return string|null
     */
    public function getLastQuery()
    {
        return $this->query;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * @return void
     */
    protected function reset()
    {
        $this->select = '*';
        $this->from = null;
        $this->where = null;
        $this->limit = null;
        $this->offset = null;
        $this->orderBy = null;
        $this->groupBy = null;
        $this->having = null;
        $this->join = null;
        $this->grouped = false;
        $this->numRows = 0;
        $this->insertId = null;
        $this->query = null;
        $this->error = null;
        $this->result = [];
        $this->transactionCount = 0;
    }

    /**
     * @param  $type
     *
     * @return int
     */
    protected function getFetchType($type)
    {
        return $type === 'class'
            ? PDO::FETCH_CLASS
            : ($type === 'array'
                ? PDO::FETCH_ASSOC
                : PDO::FETCH_OBJ);
    }

    /**
     * Optimize Selected fields for the query
     *
     * @param string $fields
     *
     * @return void
     */
    private function optimizeSelect($fields)
    {
        $this->select = $this->select === '*'
            ? $fields
            : $this->select . ', ' . $fields;
    }

    private function triggerEvent(string $sql, $start, $end, $numRows = 0)
    {
        $config = config('PdoIfx');

        $duration = number_format(($end - $start), 6);

        if (isset($config->enableQueryLog) && $config->enableQueryLog === true)
        {
            $query = [
                'sql' => $sql,
                'start' => $start,
                'end' => $end,
                'duration' => number_format(($end - $start), 6),
                'numRows' => $numRows,
                'connectTime' => $this->connectTime,
                'connectDuration' => $this->connectDuration,
                'instance' => $this->instance,
            ];

            Events::trigger('PdoIfx', $query);
        }
    }
}
