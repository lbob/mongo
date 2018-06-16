<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/6
 * Time: 下午5:03
 */

namespace Xag\Mongo;


use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\WriteConcern;

class UpdateMongoExecutor extends BaseMongoExecutor
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
        $upsert = $this->instance->isUpsert();
        $conditions = $this->parseCondition();

        foreach ($this->instance->values as &$item) {
            $this->bulk->update($conditions, ['$set' => $item], ["upsert" => $upsert, "multi" => $this->instance->isUpdateMulti()]);
        }
    }

    public function execute()
    {
        $result = $this->manager->executeBulkWrite($this->parseNamespace(), $this->bulk, $this->writeConcern);
        return $result->getInsertedCount();
    }
}