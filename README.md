# Hyperf Casbin

[![Test](https://github.com/donjan-deng/hyperf-casbin/actions/workflows/test.yml/badge.svg)](https://github.com/donjan-deng/hyperf-casbin/actions/workflows/test.yml)
[![Latest Stable Version](https://poser.pugx.org/donjan-deng/hyperf-casbin/v/stable)](https://packagist.org/packages/donjan-deng/hyperf-casbin)
[![Total Downloads](https://poser.pugx.org/donjan-deng/hyperf-casbin/downloads)](https://packagist.org/packages/donjan-deng/hyperf-casbin)
[![License](https://poser.pugx.org/donjan-deng/hyperf-casbin/license)](https://github.com/php-casbin/laravel-authz/blob/master/LICENSE)

Casbin是一个强大的、高效的开源访问控制框架，其权限管理机制支持多种访问控制模型。本项目做了Hyperf适配并自带了一个RBAC模型，使用本项目前你需要先学会如何使用Casbin。

## 简单使用

Casbin只负责访问控制，在RBAC方案中也只是储存用户和角色之间的映射关系，身份认证、管理用户列表或角色列表应由项目自身来管理。这里有个简单的示例：[example](https://github.com/donjan-deng/hyperf-casbin-example)

### 版本定义

本项目主版本与Casbin主版本相同，子版本和修订版本为项目独有

### 重大变更

+ 3.23适配Hyperf 3.1

+ 3.22适配Hyperf 3.0

+ 3.21增加Redis Watcher 升级需要重新发布配置

+ 3.6 升级需要重新发布配置

### 安装

 ```
  composer require donjan-deng/hyperf-casbin
 ```

发布配置

```
 php bin/hyperf.php vendor:publish donjan-deng/hyperf-casbin
```

配置文件config/autoload/casbin.php

API

```php
use Donjan\Casbin\Enforcer;

为用户添加权限

Enforcer::addPermissionForUser('user1', '/user', 'read');

删除一个用户的权限

Enforcer::deletePermissionForUser('user1', '/user', 'read');

获取用户所有权限

Enforcer::getPermissionsForUser('user1'); 

为用户添加角色

Enforcer::addRoleForUser('user1', 'role1');

为角色添加权限

Enforcer::addPermissionForUser('role1', '/user', 'edit');

获取所有角色

Enforcer::getAllRoles();

获取用户所有角色

Enforcer::getRolesForUser('user1');

根据角色获取用户

Enforcer::getUsersForRole('role1');

判断用户是否属于一个角色

Enforcer::hasRoleForUser('use1', 'role1');

删除用户角色

Enforcer::deleteRoleForUser('use1', 'role1');

删除用户所有角色

Enforcer::deleteRolesForUser('use1');

删除角色

Enforcer::deleteRole('role1');

删除权限

Enforcer::deletePermission('/user', 'read');

删除用户或者角色的所有权限

Enforcer::deletePermissionsForUser('user1');
Enforcer::deletePermissionsForUser('role1');

检查权限，返回 true or false

Enforcer::enforce("user1", "/user", "edit")

```
更多使用方法详见官方API

## 官方资源

* [官方文档](https://casbin.org/zh/docs/overview)
* [模型编辑器](https://casbin.org/zh/editor)
* [管理API](https://casbin.org/zh/docs/management-api)
* [RBAC API](https://casbin.org/zh/docs/rbac-api)

## 参考库

* [php-casbin](https://github.com/php-casbin/php-casbin)
* [database-adapter](https://github.com/php-casbin/database-adapter)
* [laravel-authz](https://github.com/php-casbin/laravel-authz)

## License

This project is licensed under the [Apache 2.0 license](LICENSE).
