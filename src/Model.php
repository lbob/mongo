<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/6
 * Time: 下午5:44
 */

namespace Xag\Mongo;


use MongoDB\BSON\ObjectId;
use Xag\Mongo\Strings;
use Xag\Mongo\DI;

abstract class Model
{
    public $_id;

    protected $endpoint;

    protected $database;

    protected $col;

    protected $fields;

    protected $exists = false;

    public function __construct()
    {
        if (isset($this->col)) {
            $col = $this->col;
        } else {
            $statements = explode('\\', get_called_class());
            $col = $statements[count($statements) - 1];
            $col = Strings::camelToSnake($col);
        }
        $this->col = $col;
        $this->fields = $this->getFields();
        if (empty($this->endpoint)) {
            $this->endpoint = "default";
        }
    }

    /**
     * @return Model
     */
    public static function model()
    {
        $col = get_called_class();
        /**
         * @var Model $model
         */
        $model = DI::get($col);
        return $model;
    }

    /**
     * @return MDB
     * @throws \Exception
     */
    private function createQuery()
    {
        $softDeleteFieldExists = array_search("delete_at", $this->fields) !== false;
        return MDB::endpoint($this->endpoint)->db($this->database)->col($this->col, $softDeleteFieldExists);
    }

    /**
     * @return MDB
     * @throws \Exception
     */
    public static function query()
    {
        return self::model()->createQuery();
    }

    /**
     * @param $arg
     * @return Model
     * @throws \Exception
     * @throws \Exception
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public static function find($arg)
    {
        $model = self::model();
        if ($arg instanceof MDB) {
            if ($arg->getCollection() !== $model->col) {
                throw new \Exception("MDB Query must be collection [" . $model->col . "]'s query.");
            }
            $query = $arg->detail();
        } else if (is_string($arg) && !empty($arg)) {
            $query = $model->query()->where("_id", $arg)->detail();
        }

        $model = self::loadData($query);

        return $model;
    }

    /**
     * @throws \Exception
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function save()
    {
        return $this->execSave();
    }

    /**
     * @param bool $softDelete
     * @return array
     * @throws \Exception
     * @throws \MongoDB\Driver\Exception\Exception
     */
    private function execSave($softDelete = false)
    {
        $values = $this->getPairs();
        $exists = $this->exists || $softDelete;
        if (!$softDelete && !$this->exists && !empty($values["_id"])) {
            $record = MDB::endpoint($this->endpoint)->db($this->database)->col($this->col)->detail()->execute();
            if (!empty($record) && isset($record->_id)) {
                $exists = true;
                $this->initializeData($record);
            }
        }

        if ($exists) {
            $result = $this->execUpdate($values);
        } else {
            $result = $this->execInsert($values);
        }
        $this->_id = $values['_id'];
        if (!$softDelete) {
            $this->exists = true;
        }
        return $result;
    }

    /**
     * @param array $values
     * @return array
     * @throws \Exception
     * @throws \MongoDB\Driver\Exception\Exception
     */
    private function execUpdate(array &$values)
    {
        if (array_search("update_at", $this->fields) !== false) {
            $values["update_at"] = time();
            $this->update_at = $values["update_at"];
        }
        if (array_search("create_at", $this->fields) !== false) {
            $values["create_at"] = $values['create_at'] > 0 ? $values['create_at'] : time();
            $this->create_at = $values["create_at"];
        }
        if (array_search("delete_at", $this->fields) !== false) {
            $values["delete_at"] = 0;
            $this->delete_at = $values["delete_at"];
        }
        $result = MDB::endpoint($this->endpoint)
            ->db($this->database)
            ->col($this->col)
            ->where("_id", $values['_id'])
            ->updateOne($values)
            ->execute();

        return $result;
    }

    /**
     * @param array $values
     * @return array
     * @throws \Exception
     * @throws \MongoDB\Driver\Exception\Exception
     */
    private function execInsert(array &$values)
    {
        $values['_id'] = MDB::newId();
        if (array_search("create_at", $this->fields) !== false) {
            $values["create_at"] = time();
            $this->create_at = $values["create_at"];
        }
        if (array_search("update_at", $this->fields) !== false) {
            $values["update_at"] = time();
            $this->update_at = $values["update_at"];
        }
        if (array_search("delete_at", $this->fields) !== false) {
            $values["delete_at"] = 0;
            $this->delete_at = $values["delete_at"];
        }
        $result = MDB::endpoint($this->endpoint)
            ->db($this->database)
            ->col($this->col)
            ->insertOne($values)
            ->execute();

        return $result;
    }

    /**
     * @throws \Exception
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function delete()
    {
        if ($this->exists) {
            if (array_search("delete_at", $this->fields) !== false) {
                $this->delete_at = time();
                $this->exists = false;
                $this->execSave(true);
            } else {
                MDB::endpoint($this->endpoint)->db($this->database)->col($this->col)->delete()->execute();
            }
        }
    }

    protected function getFields()
    {
        $fields = get_class_vars(get_called_class());
        unset($fields['database']);
        unset($fields['col']);
        unset($fields['fields']);
        unset($fields['endpoint']);
        unset($fields['exists']);

        return array_keys($fields);
    }

    protected function getPairs()
    {
        $values = get_object_vars($this);
        unset($values['database']);
        unset($values['col']);
        unset($values['fields']);
        unset($values['endpoint']);
        unset($values['exists']);

        if (isset($values["_id"])) {
            if (is_string($values["_id"])) {
                $values["_id"] = new ObjectId($values["_id"]);
            }
        }

        return $values;
    }

    public function test()
    {
        var_dump($this->database);
        var_dump($this->col);
        var_dump($this->fields);
    }

    /**
     * @param MDB $query
     * @return Model
     * @throws \MongoDB\Driver\Exception\Exception
     */
    private static function loadData(MDB $query)
    {
        $data = $query->execute();
        if (isset($data->exists)) {
            unset($data->exists);
        }

        if (empty($data)) {
            $model = self::model();
            $model->exists = false;
            return $model;
        }

        /** @var Model $model */
        $model = self::arrayToObject($data, get_called_class());
        if (isset($model)) {
            $model->exists = true;
            $model->_id = new ObjectId($model->_id['$oid']);
        }

        return $model;
    }

    private static function arrayToObject(array $data, $className)
    {
        if (class_exists($className)) {
            $class = new \ReflectionClass($className);
            $model = $class->newInstance();
            foreach ($data as $key => $value) {
                $model->{$key} = $value;
            }
            return $model;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isExists()
    {
        return $this->exists;
    }

    private function initializeData($record)
    {
        $data = self::arrayToObject($record, get_called_class());
        foreach ($this->fields as $field) {
            if (!isset($this->$field)) {
                $this->$field = $data->$field;
            }
        }
    }
}