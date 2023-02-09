<?php

declare(strict_types=1);

namespace Donjan\Casbin\Adapters\Mysql;

use Hyperf\DbConnection\Model\Model;

/**
 * Rule Model.
 */
class Rule extends Model
{

    /**
     * Create a new Eloquent model instance.
     *
     * @param array  $attributes
     * @param string $table
     */
    public function __construct(array $attributes = [], string $table = 'rule')
    {
        $this->setTable($table);
        $this->timestamps = false;
        $this->fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];
        parent::__construct($attributes);
    }

}
