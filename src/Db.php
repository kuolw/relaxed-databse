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
        $sql = $this->fullSql('select * from', ['where', 'limit', 'offset']);
        return $this->fetchAll($sql, $this->binds);
    }

    /**
     * @return array|false
     */
    public function first(): bool|array
    {
        $sql = $this->fullSql('select * from', ['where'], ' limit 1;');
        return $this->fetch($sql, $this->binds);
    }

    /**
     * @param $id
     * @return bool|array|Model
     */
    public function find($id): bool|array|Model
    {
        $sql = 'select * from' . " $this->table where `id` = ?;";
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

        $sql = 'insert into' . " $this->table ($keysSql) values ($valueSql);";
        $statement = $this->statement($sql, $binds);
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
     * @param $data
     * @return bool
     */
    public function update($data): bool
    {
        $sets = $binds = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key=?";
            $binds[] = $value;
        }

        $setSql = implode(',', $sets);
        $whereSql = $this->parseWhere();

        $sql = 'update' . " $this->table set $setSql $whereSql;";
        $statement = $this->statement($sql, array_merge($binds, $this->binds));
        return $statement->execute();
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        $whereSql = $this->parseWhere();
        $sql = 'delete from' . " $this->table $whereSql;";
        $statement = $this->statement($sql, $this->binds);
        return $statement->execute();
    }

    /**
     * @return bool
     */
    public function truncate(): bool
    {
        $sql = "truncate table $this->table;";
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

    private int $limit;

    /**
     * @param $value
     * @return $this
     */
    public function limit($value): static
    {
        $this->limit = $value;
        return $this;
    }

    private int $offset;

    /**
     * @param $value
     * @return $this
     */
    public function offset($value): static
    {
        $this->offset = $value;
        return $this;
    }

    //endregion

    //region 构造解析器

    /**
     * @param string $sql
     * @param array $components
     * @param string $over
     * @return string
     */
    private function fullSql(string $sql, array $components, string $over = ';'): string
    {
        $sqlArr = [$sql, $this->table];
        foreach ($components as $component) {
            $method = 'parse' . ucfirst($component);
            if ($componentSql = $this->$method()) {
                $sqlArr[] = $componentSql;
            }
        }
        return implode(' ', $sqlArr) . $over;
    }

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

    /**
     * @return string
     */
    private function parseLimit(): string
    {
        if (empty($this->limit)) {
            return '';
        }
        return 'limit ' . $this->limit;
    }

    /**
     * @return string
     */
    private function parseOffset(): string
    {
        if (empty($this->offset)) {
            return '';
        }
        return 'offset ' . $this->offset;
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
     * @return bool|array|Model
     */
    public function fetch($sql, $binds): bool|array|Model
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