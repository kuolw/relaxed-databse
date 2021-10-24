<?php

namespace Relaxed\Database;

use PDO;

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
        $sql = 'select * from ' . $this->table;
        $statement = $this->pdo->query($sql);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array|false
     */
    public function first(): bool|array
    {
        $sql = 'select * from ' . $this->table . ' limit 1';
        $statement = $this->pdo->query($sql);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return array|false
     */
    public function find($id): bool|array
    {
        $sql = 'select * from' . " $this->table where `id` = ? limit 1";
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
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
}