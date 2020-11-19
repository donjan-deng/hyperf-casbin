<?php

namespace Donjan\Casbin\Tests;

use Donjan\Casbin\Enforcer;
use Donjan\Casbin\Models\Rule;

class DatabaseAdapterTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Enforcer::addPermissionForUser('user1', 'data1', 'read');
        Enforcer::addPermissionForUser('user2', 'data2', 'write');
        Enforcer::addPermissionForUser('role1', 'data2', 'read');
        Enforcer::addPermissionForUser('role1', 'data2', 'write');
        Enforcer::addRoleForUser('user1', 'role1');
    }

    public function tearDown()
    {
        Rule::truncate();
        parent::tearDown();
    }

    public function testEnforce()
    {
        $this->assertTrue(Enforcer::enforce('user1', 'data1', 'read'));

        $this->assertFalse(Enforcer::enforce('user2', 'data1', 'read'));
        $this->assertTrue(Enforcer::enforce('user2', 'data2', 'write'));

        $this->assertTrue(Enforcer::enforce('user1', 'data2', 'read'));
        $this->assertTrue(Enforcer::enforce('user1', 'data2', 'write'));
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

    public function testRemovePolicy()
    {
        $this->assertFalse(Enforcer::enforce('user1', 'data5', 'read'));

        Enforcer::addPermissionForUser('user1', 'data5', 'read');
        $this->assertTrue(Enforcer::enforce('user1', 'data5', 'read'));

        Enforcer::deletePermissionForUser('user1', 'data5', 'read');
        $this->assertFalse(Enforcer::enforce('alice', 'data5', 'read'));
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
