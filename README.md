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

## 插入
```
Db::table('users')->insert([
    'username' => 'test',
    'password' => 123456,
]);
```

### 自增 ID
```
Db::table('users')->insertGetId([
    'username' => 'test',
    'password' => 123456,
]);
```
