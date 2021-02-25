<?php

declare(strict_types=1);

namespace Donjan\Casbin\Adapters;

use Donjan\Casbin\Models\Rule;
use Hyperf\DbConnection\Db;
use Casbin\Persist\Adapter as AdapterContract;
use Casbin\Persist\BatchAdapter;
use Casbin\Model\Model;
use Casbin\Persist\AdapterHelper;

/**
 * DatabaseAdapter.
 */
class DatabaseAdapter implements AdapterContract, BatchAdapter
{

    use AdapterHelper;

    /**
     * Rules eloquent model.
     *
     * @var Rule
     */
    protected $eloquent;

    /**
     * Db
     * @var Db 
     */
    protected $db;

    /**
     * the DatabaseAdapter constructor.
     *
     * @param Rule $eloquent
     */
    public function __construct(Rule $eloquent, Db $db)
    {
        $this->eloquent = $eloquent;
        $this->db = $db;
    }

    /**
     * savePolicyLine function.
     *
     * @param string $ptype
     * @param array  $rule
     */
    public function savePolicyLine(string $ptype, array $rule)
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . strval($key)] = $value;
        }
        return $col;
    }

    /**
     * loads all policy rules from the storage.
     *
     * @param Model $model
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->eloquent->getAllFromCache();

        foreach ($rows as $row) {
            $line = implode(', ', array_filter($row, function ($val) {
                        return '' != $val && !is_null($val);
                    }));
            $this->loadPolicyLine(trim($line), $model);
        }
    }

    /**
     * saves all policy rules to the storage.
     *
     * @param Model $model
     */
    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $row = $this->savePolicyLine($ptype, $rule);
                $this->eloquent->create($row);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $row = $this->savePolicyLine($ptype, $rule);
                $this->eloquent->create($row);
            }
        }
    }

    /**
     * adds a policy rule to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $row = $this->savePolicyLine($ptype, $rule);
        $this->eloquent->create($row);
    }

    /**
     * Adds a policy rules to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[][] $rules
     */
    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        $rows = [];
        foreach ($rules as $rule) {
            $rows[] = $this->savePolicyLine($ptype, $rule);
        }
        $this->eloquent->insert($rows);
        $this->eloquent->refreshCache();
    }

    /**
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $query = $this->eloquent->where('ptype', $ptype);
        foreach ($rule as $key => $value) {
            $query->where('v' . strval($key), $value);
        }
        $query->delete();
    }

    /**
     * Removes policy rules from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[][] $rules
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($rules as $rule) {
                $this->removePolicy($sec, $ptype, $rule);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * RemoveFilteredPolicy removes policy rules that match the filter from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param int    $fieldIndex
     * @param string ...$fieldValues
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $query = $this->eloquent->where('ptype', $ptype);
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $query->where('v' . strval($value), $fieldValues[$value - $fieldIndex]);
                }
            }
        }
        $query->delete();
    }

}
