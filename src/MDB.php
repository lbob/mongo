<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午4:47
 */

namespace Xag\Mongo;
use MongoDB\BSON\ObjectId;
use Xag\Mongo\OrderInfo;


/**
 * Class MDB
 *
 * TODO: SELECT(MULTI\ONE)
 * TODO: INSERT
 * TODO: UPDATE
 * TODO: DELETE
 * TODO: 实现MDBModel类
 * TODO: MDBModel类中，包含嵌套MDBModel类的查询
 *
 * @package Xag\Mongo
 */
class MDB
{
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;
    const SELECT = 4;

    private $endpoint;

    private $type;

    private $whereCondition = false;

    private $conditions = [];

    private $orConditions = [];

    private $database;

    private $collection;

    private $options = [];

    private $selectIterator;

    private $orWhereCondition = false;

    private $orders = [];

    public $values = [];

    private $upsert = false;

    private $updateMulti = false;

    private $deleteLimit = false;

    private $detail = false;

    private $softDeleteFieldExists = false;

    private $softDeleteLess = false;

    private function __construct(MongoEndpoint $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param $name
     * @return MDB
     * @throws \Exception
     */
    public static function endpoint($name = "default")
    {
        return new MDB(MongoEndpoint::get($name));
    }

    private function clear()
    {
        $this->conditions = [];
    }

    /**
     * @param $name
     * @return MDB
     */
    public function db($name)
    {
        $this->database = $name;
        $this->clear();

        return $this;
    }

    /**
     * @param $name
     * @param bool $softDeleteFieldExists
     * @return MDB
     */
    public function col($name, $softDeleteFieldExists = false)
    {
        $this->collection = $name;
        $this->clear();
        $this->softDeleteFieldExists = $softDeleteFieldExists;

        return $this;
    }

    /**
     * @return MDB
     */
    public function where()
    {
        $this->whereCondition = true;

        if (func_num_args() === 2) {
            $this->parseWhereArgs2(func_get_args(), Condition::TYPE_AND);
        }
        if (func_num_args() === 1) {
            $this->parseWhereArgs1(func_get_args(), Condition::TYPE_AND);
        }

        return $this;
    }

    /**
     * @param $field
     * @param array $values
     * @return MDB
     */
    public function whereIn($field, array $values)
    {
        $this->where(Condition::in($field, $values));

        return $this;
    }

    /**
     * @param $field
     * @param array $ranges
     * @param bool $includeLeft
     * @param bool $includeRight
     * @return MDB
     */
    public function whereBetween($field, array $ranges, $includeLeft = true, $includeRight = true)
    {
        $this->where(Condition::between($field, $ranges, $includeLeft, $includeRight));

        return $this;
    }

    /**
     * @param $field
     * @param array $conditions
     * @return MDB
     */
    public function whereNot($field, array $conditions)
    {
        $this->where(Condition::not($field, $conditions));

        return $this;
    }

    /**
     * @param $field
     * @param $regex
     * @return MDB
     */
    public function whereRegex($field, $regex)
    {
        $this->where(Condition::regex($field, $regex));

        return $this;
    }

    /**
     * @return MDB
     */
    public function orWhere()
    {
        $this->orWhereCondition = true;

        if (func_num_args() === 2) {
            $this->parseWhereArgs2(func_get_args(), Condition::TYPE_OR);
        }
        if (func_num_args() === 1) {
            $this->parseWhereArgs1(func_get_args(), Condition::TYPE_OR);
        }

        return $this;
    }

    /**
     * @param $field
     * @param array $values
     * @return MDB
     */
    public function orWhereIn($field, array $values)
    {
        $this->orWhere(Condition::in($field, $values));

        return $this;
    }

    /**
     * @param $field
     * @param array $ranges
     * @param bool $includeLeft
     * @param bool $includeRight
     * @return MDB
     */
    public function orWhereBetween($field, array $ranges, $includeLeft = true, $includeRight = true)
    {
        $this->orWhere(Condition::between($field, $ranges, $includeLeft, $includeRight));

        return $this;
    }

    /**
     * @param $field
     * @param array $conditions
     * @return MDB
     */
    public function orWhereNot($field, array $conditions)
    {
        $this->orWhere(Condition::not($field, $conditions));

        return $this;
    }

    /**
     * @param $field
     * @param $regex
     * @return MDB
     */
    public function orWhereRegex($field, $regex)
    {
        $this->orWhere(Condition::regex($field, $regex));

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return MDB
     */
    public function option($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @param $count
     * @return MDB
     */
    public function take($count)
    {
        if ($count >= 0) {
            $this->option("limit", $count);
        }

        return $this;
    }

    /**
     * @param $count
     * @return MDB
     */
    public function skip($count)
    {
        if ($count >= 0) {
            $this->option("skip", $count);
        }

        return $this;
    }

    /**
     * @param int $pageIndex
     * @param $pageSize
     * @return MDB
     */
    public function page($pageIndex = 1, $pageSize)
    {
        $offset = ($pageIndex - 1) * $pageSize;
        $offset = $offset > 0 ? $offset : 0;

        return $this->take($pageSize)->skip($offset);
    }

    /**
     * @param $field
     * @param string $orderType
     * @return MDB
     */
    public function orderBy($field, $orderType = OrderInfo::SORT_ASC)
    {
        $this->orders[$field] = $orderType === OrderInfo::SORT_ASC ? 1 : -1;
        $this->option('sort', $this->orders);

        return $this;
    }

    /**
     * @return MDB
     */
    public function detail()
    {
        $this->detail = true;
        return $this->take(1)->select();
    }

    /**
     * @param callable|null $iterator
     * @return MDB
     */
    public function select(callable $iterator = null)
    {
        $this->type = self::SELECT;
        $this->selectIterator = $iterator;

        return $this;
    }

    /**
     * @param array $values
     * @return MDB
     */
    public function insert(array &$values)
    {
        $this->type = self::INSERT;
        $this->values = &$values;

        return $this;
    }

    /**
     * @param $value
     * @return MDB
     */
    public function insertOne(&$value)
    {
        $items[] = &$value;
        return $this->insert($items);
    }

    /**
     * @return MDB
     */
    public function delete($limit = true)
    {
        $this->type = self::DELETE;
        $this->deleteLimit = $limit;

        return $this;
    }

    /**
     * @return MDB
     */
    public function deleteAll()
    {
        return $this->delete(false);
    }

    /**
     * @param array $values
     * @param bool $upsert
     * @return MDB
     */
    public function updateOne(array $values, $upsert = true)
    {
        $this->type = self::UPDATE;
        $this->values[] = $values;
        $this->upsert = $upsert;
        $this->updateMulti = false;

        return $this;
    }

    public function updateMulti(array $values, $upsert = true)
    {
        $this->type = self::UPDATE;
        $this->values[] = $values;
        $this->upsert = $upsert;
        $this->updateMulti = true;

        return $this;
    }

    /**
     * @return MDB
     */
    public function softDeleteLess()
    {
        $this->softDeleteLess = true;

        return $this;
    }

    /**
     * @return array
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function execute()
    {
        if ($this->softDeleteFieldExists && !$this->softDeleteLess) {
            $this->where("delete_at", 0);
        }
        switch ($this->type) {
            case self::SELECT:
                $executor = new MongoSelectExecutor($this);
                break;
            case self::INSERT:
                $executor = new InsertMongoExecutor($this);
                break;
            case self::UPDATE:
                $executor = new UpdateMongoExecutor($this);
                break;
            case self::DELETE:
                $executor = new DeleteMongoExecutor($this);
                break;
        }
        if (isset($executor)) {
            $executor->prepare();
            return $executor->execute();
        }
        return null;
    }

    /**
     * @return MongoEndpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return bool
     */
    public function isWhereCondition()
    {
        return $this->whereCondition;
    }

    private function parseWhereArgs2(array $args, $type)
    {
        if ($type === Condition::TYPE_AND) {
            $this->conditions[$args[0]] = $args[1];
        }
        if ($type === Condition::TYPE_OR) {
            $this->orConditions[] = [$args[0] => $args[1]];
        }
    }

    private function parseWhereArgs1(array $args, $type)
    {
        if ($type === Condition::TYPE_AND) {
            if ($args[0] instanceof Condition) {
                $items = $args[0]->getValue();
                foreach ($items as $key => $value) {
                    $this->conditions[$key] = $value;
                }
            } else {
                foreach ($args[0] as $key => $value) {
                    $this->conditions[$key] = $value;
                }
            }
        }

        if ($type === Condition::TYPE_OR) {
            if ($args[0] instanceof Condition) {
                $items = $args[0]->getValue();
                foreach ($items as $key => $value) {
                    $this->orConditions[] = [$key => $value];
                }
            } else {
                foreach ($args[0] as $key => $value) {
                    $this->orConditions[] = [$key => $value];
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return mixed
     */
    public function getSelectIterator()
    {
        return $this->selectIterator;
    }

    /**
     * @return bool
     */
    public function isOrWhereCondition()
    {
        return $this->orWhereCondition;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    public static function newId()
    {
        return new ObjectId();
    }

    /**
     * @return bool
     */
    public function isUpsert()
    {
        return $this->upsert;
    }

    /**
     * @return bool
     */
    public function isUpdateMulti()
    {
        return $this->updateMulti;
    }

    /**
     * @return bool
     */
    public function isDeleteLimit()
    {
        return $this->deleteLimit;
    }

    /**
     * @return bool
     */
    public function isDetail()
    {
        return $this->detail;
    }

    /**
     * @return array
     */
    public function getOrConditions()
    {
        return $this->orConditions;
    }

    /**
     * @return bool
     */
    public function isSoftDeleteLess()
    {
        return $this->softDeleteLess;
    }
}