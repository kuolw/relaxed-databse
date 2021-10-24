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
}