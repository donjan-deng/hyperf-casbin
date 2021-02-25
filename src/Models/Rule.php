<?php

namespace Donjan\Casbin\Models;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Events\Deleted;
use Psr\SimpleCache\CacheInterface;
use Hyperf\Utils\ApplicationContext;

/**
 * Rule Model.
 */
class Rule extends Model
{

    /**
     *
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * the guard config for casbin.
     *
     * @var array
     */
    protected $config;

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];

    /**
     * timestamps
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array  $attributes
     * @param string $guard
     */
    public function __construct(array $attributes = [], string $guard = 'default')
    {
        $this->config = config("casbin.{$guard}");
        $this->cache = ApplicationContext::getContainer()->get(CacheInterface::class);
        $connection = $this->config['adapter']['connection'];

        $this->setConnection($connection);
        $this->setTable($this->config['adapter']['table_name']);

        parent::__construct($attributes);
    }

    /**
     * Saved Event
     * 
     * @param Saved $event
     */
    public function saved(Saved $event)
    {
        $this->refreshCache();
    }

    /**
     * Deleted Event
     * 
     * @param Deleted $event
     */
    public function deleted(Deleted $event)
    {
        $this->refreshCache();
    }

    /**
     * Gets rules from caches.
     *
     * @return mixed
     */
    public function getAllFromCache()
    {
        $get = function () {
            return $this->select('ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5')->get()->toArray();
        };
        if (!$this->config['cache']['enabled']) {
            return $get();
        }
        $result = $this->cache->get($this->config['cache']['key']);
        if (is_null($result)) {
            $result = $get();
            $this->cache->set($this->config['cache']['key'], $result, $this->config['cache']['ttl']);
        }
        return $result;
    }

    /**
     * Refresh Cache.
     */
    public function refreshCache()
    {
        if (!$this->config['cache']['enabled']) {
            return;
        }
        $this->forgetCache();
    }

    /**
     * Forget Cache.
     */
    public function forgetCache()
    {
        $this->cache->delete($this->config['cache']['key']);
    }

}
