# Hyperf Casbin

Casbin是一个强大的、高效的开源访问控制框架，其权限管理机制支持多种访问控制模型。本项目做了Hyperf适配并自带了一个RBAC模型，使用本项目前你需要先学会如何使用Casbin。

## 简单使用

Casbin只负责访问控制，在RBAC方案中也只是储存用户和角色之间的映射关系，身份认证、管理用户列表或角色列表应由项目自身来管理。

### 安装

 ```
  composer require donjan-deng/hyperf-casbin
 ```

发布配置

```
 php bin/hyperf.php vendor:publish donjan-deng/hyperf-casbin
```

配置文件config/autoload/casbin.php

运行迁移

```
php bin/hyperf.php migrate
```
命令

```
php bin/hyperf.php casbin:cache-clear //清除缓存
```

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

### 多个实例

```php

config/autoload/casbin.php

return [
    'default' => [
        'model' => [
            'config_type' => 'file',
            'config_file_path' => BASE_PATH . '/config/autoload/casbin-rbac-model.conf',
            'config_text' => '',
        ],
        'adapter' => [
            'class' => \Donjan\Casbin\Adapters\DatabaseAdapter::class,
            'table_name' => 'casbin_rule_rbac',
            'connection' => 'default'
        ],
        //...
    ],
    'second' => [
        'model' => [
            'config_type' => 'file',
            'config_file_path' => BASE_PATH . '/config/autoload/casbin-restful-model.conf',
            'config_text' => '',
        ],
        'adapter' => [
            'class' => \Donjan\Casbin\Adapters\DatabaseAdapter::class,
            'table_name' => 'casbin_rule_restful',
            'connection' => 'default'
        ],
        //...
    ],
]

Enforcer::guard('second')->enforce("user1", "data1", "edit");

```

## 官方资源

* [官方文档](https://casbin.org/docs/zh-CN/overview)
* [模型编辑器](https://casbin.org/zh-CN/editor)
* [管理API](https://casbin.org/docs/zh-CN/management-api)
* [RBAC API](https://casbin.org/docs/zh-CN/rbac-api)

## 参考库

* [php-casbin](https://github.com/php-casbin/php-casbin)
* [database-adapter](https://github.com/php-casbin/database-adapter)
* [laravel-authz](https://github.com/php-casbin/laravel-authz)

## License

This project is licensed under the [Apache 2.0 license](LICENSE).
