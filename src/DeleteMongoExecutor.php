<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/6
 * Time: 下午5:33
 */

namespace Xag\Mongo;


use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\WriteConcern;

class DeleteMongoExecutor extends BaseMongoExecutor
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
        $conditions = $this->parseCondition();

        $this->bulk->delete($conditions, ['limit' => $this->instance->isDeleteLimit()]);
    }

    public function execute()
    {
        $result = $this->manager->executeBulkWrite($this->parseNamespace(), $this->bulk, $this->writeConcern);
        return $result->getInsertedCount();    }
}