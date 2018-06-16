<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/6
 * Time: 上午9:52
 */

namespace Xag\Mongo;


use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\WriteConcern;

class InsertMongoExecutor extends BaseMongoExecutor
{
    /**
     * @var WriteConcern
     */
    private $writeConcern;
    /**
     * @var BulkWrite
     */
    private $bulk;

    public function prepare()
    {
        $this->writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
        $this->bulk = new BulkWrite(['ordered' => true]);

        foreach ($this->instance->values as &$item) {
            $_id = $this->bulk->insert($item);
            $item["_id"] = $_id;
        }
    }

    public function execute()
    {
        $result = $this->manager->executeBulkWrite($this->parseNamespace(), $this->bulk, $this->writeConcern);
        return $result->getInsertedCount();
    }
}