<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午5:17
 */

namespace Xag\Mongo;


abstract class BaseMongoExecutor
{
    protected $instance;

    protected $manager;

    protected $endpoint;

    public function __construct(MDB $instance)
    {
        $this->instance = $instance;
        $this->endpoint = $instance->getEndpoint();
        $this->manager = $this->endpoint->getManager();
    }

    abstract public function prepare();

    abstract public function execute();

    protected function parseCondition()
    {
        $conditions = [];
        if ($this->instance->isWhereCondition()) {
            $andConditions = $this->instance->getConditions();
            foreach ($andConditions as $key => $value) {
                $conditions[$key] = $value;
            }
        }
        if ($this->instance->isOrWhereCondition()) {
            $orConditions = $this->instance->getOrConditions();
            $conditions['$or'] = $orConditions;
        }

        return $conditions;
    }

    protected function parseNamespace()
    {
        return "{$this->instance->getDatabase()}.{$this->instance->getCollection()}";
    }

    protected function parseOptions()
    {
        return $this->instance->getOptions();
    }
}