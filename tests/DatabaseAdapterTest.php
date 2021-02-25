<?php

namespace Donjan\Casbin\Tests;

use Donjan\Casbin\Enforcer;
use Donjan\Casbin\Models\Rule;

class DatabaseAdapterTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Enforcer::addPermissionForUser('user1', 'data1', 'read');
        Enforcer::addPermissionForUser('user2', 'data2', 'write');
        Enforcer::addPermissionForUser('role1', 'data2', 'read');
        Enforcer::addPermissionForUser('role1', 'data2', 'write');
        Enforcer::addRoleForUser('user1', 'role1');
    }

    protected function tearDown(): void
    {
        Rule::truncate();
        parent::tearDown();
    }

    public function testSavePolicy()
    {
        $this->assertFalse(Enforcer::enforce('user1', 'data4', 'read'));

        $model = Enforcer::getModel();
        $model->clearPolicy();
        $model->addPolicy('p', 'p', ['user1', 'data4', 'read']);

        $adapter = Enforcer::getAdapter();
        $adapter->savePolicy($model);
        $this->assertTrue(Enforcer::enforce('user1', 'data4', 'read'));
    }

    public function testAddPolicies()
    {
        $this->assertFalse(Enforcer::enforce('user1', 'add-policies-1', 'read'));
        $this->assertFalse(Enforcer::enforce('user1', 'add-policies-2', 'read'));
//        $this->assertFalse(Enforcer::enforce('user1', 'add-policies-3', 'read'));
//        $this->assertFalse(Enforcer::enforce('user1', 'add-policies-4', 'read'));
        Enforcer::AddPolicies([
            ['user1', 'add-policies-1', 'read'],
            ['user1', 'add-policies-2', 'read']
        ]);
//        Enforcer::AddPermissionsForUser('user1', [
//            ['add-policies-3', 'read'],
//            ['add-policies-4', 'read']
//        ]);
        $this->assertTrue(Enforcer::enforce('user1', 'add-policies-1', 'read'));
        $this->assertTrue(Enforcer::enforce('user1', 'add-policies-2', 'read'));
//        $this->assertTrue(Enforcer::enforce('user1', 'add-policies-3', 'read'));
//        $this->assertTrue(Enforcer::enforce('user1', 'add-policies-4', 'read'));
    }

    public function testRemovePolicy()
    {
        $this->assertTrue(Enforcer::enforce('user1', 'data1', 'read'));
        Enforcer::RemovePolicy('user1', 'data1', 'read');
        $this->assertFalse(Enforcer::enforce('user1', 'data1', 'read'));
    }

    public function testRemovePolicies()
    {
        Enforcer::AddPolicies([
            ['user1', 'add-policies-1', 'read'],
            ['user1', 'add-policies-2', 'read']
        ]);
        $this->assertTrue(Enforcer::enforce('user1', 'add-policies-1', 'read'));
        $this->assertTrue(Enforcer::enforce('user1', 'add-policies-2', 'read'));
        Enforcer::deletePermissionsForUser('user1');
        $this->assertFalse(Enforcer::enforce('user1', 'add-policies-1', 'read'));
        $this->assertFalse(Enforcer::enforce('user1', 'add-policies-2', 'read'));
    }

    public function testRemoveFilteredPolicy()
    {
        $this->assertTrue(Enforcer::enforce('user1', 'data1', 'read'));
        Enforcer::removeFilteredPolicy(1, 'data1');
        $this->assertFalse(Enforcer::enforce('user1', 'data1', 'read'));
        $this->assertTrue(Enforcer::enforce('user2', 'data2', 'write'));
        $this->assertTrue(Enforcer::enforce('user1', 'data2', 'read'));
        $this->assertTrue(Enforcer::enforce('user1', 'data2', 'write'));
        Enforcer::removeFilteredPolicy(1, 'data2', 'read');
        $this->assertTrue(Enforcer::enforce('user2', 'data2', 'write'));
        $this->assertFalse(Enforcer::enforce('user1', 'data2', 'read'));
        $this->assertTrue(Enforcer::enforce('user1', 'data2', 'write'));
        Enforcer::removeFilteredPolicy(2, 'write');
        $this->assertFalse(Enforcer::enforce('user2', 'data2', 'write'));
        $this->assertFalse(Enforcer::enforce('user1', 'data2', 'write'));
    }

}
