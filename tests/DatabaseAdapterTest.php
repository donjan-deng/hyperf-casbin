<?php

namespace Donjan\Casbin\Tests;

use Donjan\Casbin\Enforcer;
use Casbin\Persist\Adapters\Filter;
use Casbin\Exceptions\InvalidFilterTypeException;

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

    public function testUpdatePolicy()
    {
        $this->assertEquals([
            ['user1', 'data1', 'read'],
            ['user2', 'data2', 'write'],
            ['role1', 'data2', 'read'],
            ['role1', 'data2', 'write'],
                ], Enforcer::getPolicy());

        Enforcer::updatePolicy(
                ['user1', 'data1', 'read'],
                ['user1', 'data1', 'write']
        );

        Enforcer::updatePolicy(
                ['user2', 'data2', 'write'],
                ['user2', 'data2', 'read']
        );

        $this->assertEquals([
            ['user1', 'data1', 'write'],
            ['user2', 'data2', 'read'],
            ['role1', 'data2', 'read'],
            ['role1', 'data2', 'write'],
                ], Enforcer::getPolicy());
    }

    public function testLoadFilteredPolicy()
    {
        Enforcer::clearPolicy();
        $adapter = Enforcer::getAdapter();
        $adapter->setFiltered(true);
        $this->assertEquals([], Enforcer::getPolicy());

        // invalid filter type
        try {
            $filter = ['user1', 'data1', 'read'];
            Enforcer::loadFilteredPolicy($filter);
            $e = InvalidFilterTypeException::class;
            $this->fail("Expected exception $e not thrown");
        } catch (InvalidFilterTypeException $e) {
            $this->expectExceptionMessage($e->getMessage());
        }
        
        // string
        $filter = "v0 = 'user2'";
        Enforcer::loadFilteredPolicy($filter);
        $this->assertEquals([
            ['user2', 'data2', 'write']
                ], Enforcer::getPolicy());
        // Filter
        $filter = new Filter(['v2'], ['read']);
        Enforcer::loadFilteredPolicy($filter);
        $this->assertEquals([
            ['user1', 'data1', 'read'],
            ['role1', 'data2', 'read'],
                ], Enforcer::getPolicy());
        var_dump('here');
        // Closure
        Enforcer::loadFilteredPolicy(function ($query) {
            $query->where('v1', 'data1');
        });

        $this->assertEquals([
            ['user1', 'data1', 'read'],
                ], Enforcer::getPolicy());
    }

    public function testUpdatePolicies()
    {

        $this->assertEquals([
            ['user1', 'data1', 'read'],
            ['user2', 'data2', 'write'],
            ['role1', 'data2', 'read'],
            ['role1', 'data2', 'write'],
                ], Enforcer::getPolicy());

        $oldPolicies = [
            ['user1', 'data1', 'read'],
            ['user2', 'data2', 'write'],
        ];
        $newPolicies = [
            ['user1', 'data1', 'write'],
            ['user2', 'data2', 'read'],
        ];

        Enforcer::updatePolicies($oldPolicies, $newPolicies);

        $this->assertEquals([
            ['user1', 'data1', 'write'],
            ['user2', 'data2', 'read'],
            ['role1', 'data2', 'read'],
            ['role1', 'data2', 'write'],
                ], Enforcer::getPolicy());
    }

}
