<?php

namespace Donjan\Casbin\Tests;

use Hyperf\DbConnection\Db;
use Donjan\Casbin\Models\Rule;

class RuleCacheTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testEnableCache()
    {
        $this->config->set('casbin.default.cache.enabled', true);
        Db::enableQueryLog();
        Db::flushQueryLog();

        $ruleModel = new Rule();
        $ruleModel->forgetCache();

        $ruleModel->getAllFromCache();
        $this->assertCount(1, Db::getQueryLog());

        $ruleModel->getAllFromCache();
        $this->assertCount(1, Db::getQueryLog());

        Db::flushQueryLog();
        $ruleModel->getAllFromCache();
        $this->assertCount(0, Db::getQueryLog());

        $rule = Rule::create(['ptype' => 'p', 'v0' => 'alice', 'v1' => 'data1', 'v2' => 'read']);
        $ruleModel->refreshCache();
        $ruleModel->getAllFromCache();
        $this->assertCount(2, Db::getQueryLog());

        $rule->delete();
        $ruleModel->refreshCache();
        $ruleModel->getAllFromCache();
        $ruleModel->getAllFromCache();
        $this->assertCount(4, Db::getQueryLog());

        Db::flushQueryLog();
    }

    public function testDisableCache()
    {
        $this->config->set('casbin.default.cache.enabled', false);
        $ruleModel = new Rule();

        Db::enableQueryLog();
        Db::flushQueryLog();
        
        $ruleModel->getAllFromCache();
        $this->assertCount(1, Db::getQueryLog());

        $rule = Rule::create(['ptype' => 'p', 'v0' => 'alice', 'v1' => 'data1', 'v2' => 'read']);
        $ruleModel->refreshCache();
        $ruleModel->getAllFromCache();
        $this->assertCount(3, Db::getQueryLog());

        $rule->delete();
        $ruleModel->refreshCache();
        $ruleModel->getAllFromCache();
        $ruleModel->getAllFromCache();
        $this->assertCount(6, Db::getQueryLog());

        Db::flushQueryLog();
    }

}
