<?php

namespace Donjan\Casbin\Tests;

use Donjan\Casbin\Enforcer;

class RbacApiTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Enforcer::loadPolicy();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetRolesForUser()
    {
        Enforcer::addRoleForUser('user1', 'admin1');
        Enforcer::addRoleForUser('user1', 'admin2');

        $this->assertEquals(Enforcer::getRolesForUser('user1'), ['admin1', 'admin2']);
        $this->assertEquals(Enforcer::getRolesForUser('admin1'), []);
        $this->assertEquals(Enforcer::getRolesForUser('user2'), []);
    }

    public function testGetUsersForRole()
    {
        Enforcer::addRoleForUser('user1', 'admin1');
        Enforcer::addRoleForUser('user2', 'admin1');
        $this->assertEquals(Enforcer::getUsersForRole('admin1'), ['user1', 'user2']);
        $this->assertEquals(Enforcer::getUsersForRole('role'), []);
    }

    public function testHasRoleForUser()
    {
        Enforcer::addRoleForUser('user1', 'admin1');
        $this->assertTrue(Enforcer::hasRoleForUser('user1', 'admin1'));
        $this->assertFalse(Enforcer::hasRoleForUser('user1', 'admin2'));
    }

    public function testAddRolesForUser()
    {
        Enforcer::addRolesForUser('user1', ['admin1', 'admin2'], '');
        $this->assertEquals(Enforcer::getRolesForUser('user1'), ['admin1', 'admin2']);
    }

    public function testDeleteRoleForUser()
    {
        Enforcer::addRoleForUser('user1', 'admin1');
        $this->assertTrue(Enforcer::hasRoleForUser('user1', 'admin1'));
        Enforcer::deleteRoleForUser('user1', 'admin1');
        $this->assertFalse(Enforcer::hasRoleForUser('user1', 'admin1'));
    }

    public function testDeleteRolesForUser()
    {
        Enforcer::addRoleForUser('user1', 'admin1');
        Enforcer::addRoleForUser('user1', 'admin2');
        Enforcer::deleteRolesForUser('user1');
        $this->assertEquals(Enforcer::getRolesForUser('user1'), []);
    }

    public function testDeleteUser()
    {
        Enforcer::addRoleForUser('user1', 'admin1');
        Enforcer::addRoleForUser('user1', 'admin2');
        Enforcer::deleteUser('user1');
        $this->assertEquals(Enforcer::getRolesForUser('user1'), []);
    }

    public function testDeleteRole()
    {
        Enforcer::addRoleForUser('user1', 'admin1');
        Enforcer::addRoleForUser('user1', 'admin2');
        Enforcer::addPermissionForUser('admin1', 'data1', 'read');
        Enforcer::addPermissionForUser('admin2', 'data2', 'read');
        Enforcer::deleteRole('admin2');
        $this->assertTrue(Enforcer::enforce('user1', 'data1', 'read'));
        $this->assertFalse(Enforcer::enforce('user1', 'data2', 'read'));
    }

    public function testDeletePermission()
    {
        Enforcer::addPermissionForUser('user1', 'data1', 'read');
        Enforcer::addPermissionForUser('user1', 'data2', 'read');
        Enforcer::deletePermission('data2');
        $this->assertTrue(Enforcer::enforce('user1', 'data1', 'read'));
        $this->assertFalse(Enforcer::enforce('user1', 'data2', 'read'));
    }

    public function testDeletePermissionForUser()
    {
        Enforcer::addPermissionForUser('user1', 'data1', 'read');
        Enforcer::addPermissionForUser('user1', 'data2', 'read');
        Enforcer::deletePermissionForUser('user1', 'data2');
        Enforcer::loadPolicy(); //php-casbin 未能删除model的数据
        $this->assertTrue(Enforcer::enforce('user1', 'data1', 'read'));
        $this->assertFalse(Enforcer::enforce('user1', 'data2', 'read'));
    }

    public function testDeletePermissionsForUser()
    {
        Enforcer::addPermissionForUser('user1', 'data1', 'read');
        Enforcer::addPermissionForUser('user1', 'data2', 'read');
        Enforcer::deletePermissionsForUser('user1');
        $this->assertFalse(Enforcer::enforce('user1', 'data1', 'read'));
        $this->assertFalse(Enforcer::enforce('user1', 'data2', 'read'));
    }

    public function testGetPermissionsForUser()
    {
        Enforcer::addPermissionForUser('user1', 'data1', 'read');
        Enforcer::addPermissionForUser('user1', 'data2', 'read');
        $this->assertEquals(Enforcer::getPermissionsForUser('user1'), [['user1', 'data1', 'read'], ['user1', 'data2', 'read']]);
    }

    public function testHasPermissionForUser()
    {
        Enforcer::addPermissionForUser('user1', 'data1', 'read');
        $this->assertTrue(Enforcer::hasPermissionForUser('user1', ...['data1', 'read']));
        $this->assertFalse(Enforcer::hasPermissionForUser('user1', ...['data2', 'read']));
    }

    public function testGetImplicitRolesForUser()
    {
        Enforcer::addRoleForUser('user1', 'admin1');
        Enforcer::addRoleForUser('admin1', 'admin2');
        $this->assertEquals(Enforcer::getImplicitRolesForUser('user1'), ['admin1', 'admin2']);
    }

    public function testGetImplicitPermissionsForUser()
    {
        Enforcer::addPermissionForUser('user1', 'data1', 'read');
        Enforcer::addPermissionForUser('admin1', 'data2', 'read');
        Enforcer::addRoleForUser('user1', 'admin1');
        $this->assertEquals(Enforcer::getImplicitPermissionsForUser('user1'), [
            ['user1', 'data1', 'read'],
            ['admin1', 'data2', 'read']
        ]);
    }

}
