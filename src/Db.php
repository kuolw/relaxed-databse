<?php

namespace Relaxed\Database;

use PDO;
use PDOStatement;

class Db
{
    public function __construct(
        private PDO $pdo,
    )
    {
    }

    private string $table;


    /**
     * @param string $name
     * @return $this
     */
    public function table(string $name): static
    {
        $this->table = $name;
        return $this;
    }

    /**
     * @return array|false
     */
    public function get(): bool|array
    {
        $whereSql = $this->parseWhere();
        $sql = 'select * from' . " $this->table $whereSql";
        return $this->fetchAll($sql, $this->binds);
    }

    /**
     * @return array|false
     */
    public function first(): bool|array
    {
        $whereSql = $this->parseWhere();
        $sql = 'select * from' . " $this->table $whereSql limit 1";
        return $this->fetch($sql, $this->binds);
    }

    /**
     * @return array|false
     */
    public function find($id): bool|array
    {
        $sql = 'select * from' . " $this->table where `id` = ?";
        return $this->fetch($sql, [$id]);
    }

    /**
     * @param $data
     * @return bool
     */
    public function insert($data): bool
    {
        $keys = $values = $binds = [];
        foreach ($data as $key => $value) {
            $keys[] = $key;
            $values[] = '?';
            $binds[] = $value;
        }

        $keysSql = implode(',', $keys);
        $valueSql = implode(',', $values);

        $sql = 'insert into' . " $this->table ($keysSql) values ($valueSql)";
        $statement = $this->pdo->prepare($sql);
        foreach ($binds as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        return $statement->execute();
    }

    /**
     * @param $data
     * @return int
     */
    public function insertGetId($data): int
    {
        $this->insert($data);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        $whereSql = $this->parseWhere();
        $sql = 'delete from' . " $this->table $whereSql";
        $statement = $this->statement($sql, $this->binds);
        return $statement->execute();
    }

    /**
     * @return bool
     */
    public function truncate(): bool
    {
        $sql = "truncate table $this->table";
        return $this->execute($sql);
    }

    private bool $debug = false;

    /**
     * @return $this
     */
    public function debug(): static
    {
        $this->debug = true;
        return $this;
    }

    // region 查询构造器

    private array $wheres = [];
    private array $binds = [];

    /**
     * @param $field
     * @param $operator
     * @param $value
     * @return Db
     */
    public function where($field, $operator, $value): static
    {
        $this->wheres[] = ['and', $field, $operator];
        $this->binds[] = $value;
        return $this;
    }

    /**
     * @param $field
     * @param $operator
     * @param $value
     * @return Db
     */
    public function orWhere($field, $operator, $value): static
    {
        $this->wheres[] = ['or', $field, $operator];
        $this->binds[] = $value;
        return $this;
    }

    //endregion

    //region 构造解析器

    /**
     * @return string
     */
    private function parseWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = 'where';
        foreach ($this->wheres as $i => [$boolean, $field, $operator]) {
            if ($i) {
                $sql .= " $boolean `$field` $operator ?";
            } else {
                $sql .= " `$field` $operator ?";
            }
        }
        return $sql;
    }

    //endregion

    //region 语句执行器

    /**
     * 生成预处理语句
     * @param $sql
     * @param $binds
     * @return false|PDOStatement
     */
    public function statement($sql, $binds = null): bool|PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        if ($binds) {
            foreach ($binds as $i => $value) {
                $statement->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
        }

        if ($this->debug) {
            var_dump(compact('sql', 'binds'));
        }

        return $statement;
    }

    /**
     * @param $sql
     * @param null $binds
     * @return bool
     */
    public function execute($sql, $binds = null): bool
    {
        $statement = $this->statement($sql, $binds);
        return $statement->execute();
    }

    /**
     * @param $sql
     * @param $binds
     * @return bool|array
     */
    public function fetch($sql, $binds): bool|array
    {
        $statement = $this->statement($sql, $binds);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $sql
     * @param $binds
     * @return array|false
     */
    public function fetchAll($sql, $binds): bool|array
    {
        $statement = $this->statement($sql, $binds);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    //endregion
}