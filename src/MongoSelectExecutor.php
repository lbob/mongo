<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午5:16
 */

namespace Xag\Mongo;


use MongoDB\Driver\Cursor;
use MongoDB\Driver\Query;

class MongoSelectExecutor extends BaseMongoExecutor
{
    /**
     * @var Query
     */
    private $filters;

    private $iterator;

    /**
     * @return array
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function execute()
    {
        if (isset($this->iterator)) {
            $this->parseIterator();
        } else {
            $items = $this->parseToArray();
            if ($this->instance->isDetail()) {
                if (isset($items[0])) {
                    return $items[0];
                }
            }
            return $items;
        }
    }

    public function prepare()
    {
        $this->filters = new Query($this->parseCondition(), $this->parseOptions());
        $this->iterator = $this->instance->getSelectIterator();
    }

    /**
     * @throws \MongoDB\Driver\Exception\Exception
     */
    private function parseIterator()
    {
        /** @var Cursor $cursor */
        $cursor = $this->manager->executeQuery($this->parseNamespace(), $this->filters);
        $it = new \IteratorIterator($cursor);
        $it->rewind();
        while ($doc = $it->current()) {
            call_user_func($this->iterator, $doc);
            $it->next();
        }
    }

    /**
     * @return array
     * @throws \MongoDB\Driver\Exception\Exception
     */
    private function parseToArray()
    {
        $cursor = $this->manager->executeQuery($this->parseNamespace(), $this->filters);
        return $cursor->toArray();
    }
}