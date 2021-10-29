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
    }

    public function testFirst(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->where('id', '>', 1)->first();

        $this->assertEquals(2, $result['id']);
    }

    public function testFind(): void
    {
        $db = new Db($this->pdo());
        $user1 = $db->table('users')->find(1);
        $this->assertEquals(1, $user1['id']);

        $user2 = $db->table('users')->find(2);
        $this->assertEquals(2, $user2['id']);
    }

    public function testWhere(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->where('username', '=', 'admin')->first();
        $this->assertEquals('admin', $result['username']);

        $db = new Db($this->pdo());
        $result = $db->table('users')->where('password', '=', '123456')->get();
        $this->assertIsArray($result);
    }

    public function testOrWhere(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')
            ->where('username', '=', 'admin')
            ->orWheRe('username', '=', 'test')
            ->get();
        $this->assertIsArray($result);
        $this->assertEquals('admin', $result[0]['username']);
        $this->assertEquals('test', $result[1]['username']);
    }

    public function testLimit(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->limit(3)->get();
        $this->assertCount(3, $result);
    }

    public function testOffset(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->offset(1)->limit(1)->get();
        $this->assertEquals('test', $result[0]['username']);
    }

    /**
     * @throws Exception
     */
    public function testInsert(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->insert([
            'username' => 'test' . random_int(1000, 9999)
        ]);
        $this->assertEquals(true, $result);

        $result = $db->table('users')->insertGetId([
            'username' => 'test' . random_int(1000, 9999)
        ]);
        $this->assertIsNumeric($result);
    }

    /**
     * @throws Exception
     */
    public function testUpdate(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')
            ->where('id', '=', 3)
            ->update([
                'username' => 'test' . random_int(1000, 9999)
            ]);
        $this->assertEquals(true, $result);
    }

    public function testDelete(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->where('id', '>', 10)->delete();
        $this->assertEquals(true, $result);
    }

    public function testTruncate(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->truncate();
        $this->assertEquals(true, $result);
    }

    public function testDebug(): void
    {
        $db = new Db($this->pdo());
        $result = $db->table('users')->where('username', '=', 'admin')->debug()->first();
        $this->assertEquals('admin', $result['username']);
    }
}