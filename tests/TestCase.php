<?php

require_once '../vendor/autoload.php';

use Relaxed\Database\Db;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @return PDO
     */
    public function pdo(): PDO
    {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $dsn = "{$_ENV['DB_CONNECTION']}:host={$_ENV['DB_HOST']}:{$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']}";
        return new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    }

    public function testGet(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->get();
        $this->assertIsArray($result);

        $this->assertEquals(['id' => 1, 'username' => 'admin', 'password' => 123456], $result[0]);
    }
}