# RELAXED-DATABASE

## 简介

基于 PHP8 实现现代化低成本、高性能、轻便易用的数据库查询构造器。

## 获取数据

```php
$db->table('users')->get();
```

### 获取单行数据

```php
$db->table('users')->first();
```

### 通过 ID 获取数据

```php
$db->table('users')->find(1);
```

## 查询条件

### Where

```php
Db::table('users')->where('username', '=', 'admin')->first();
Db::table('users')->where('password', '=', '123456')->get();

$db->table('users')
    ->where('username', '=', 'admin')
    ->orWheRe('username', '=', 'test1')
    ->get();
```

## 插入

```php
$db->table('users')->insert([
    'username' => 'test',
    'password' => 123456,
]);
```

### 自增 ID

```php
$db->table('users')->insertGetId([
    'username' => 'test',
    'password' => 123456,
]);
```

## 更新

```php
$db->table('users')
    ->where('id', '=', 3)
    ->update([
        'username' => 'test' . random_int(1000, 9999)
    ]);
```

## 删除

```php
$db->table('users')->where('id', '>', 10)->delete();
```

### 清空表

```php
$db->table('users')->truncate();
```

## SQL 调试

```php
$db->table('users')
    ->where('username', '=', 'admin')
    ->debug()
    ->first();
```