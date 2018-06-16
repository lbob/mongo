<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午4:48
 */

namespace Xag\Mongo;


class MongoEndpoint
{
    public $host;

    public $port;

    public $username;

    public $password;

    /**
     * @param $name
     * @return MongoEndpoint
     * @throws \Exception
     */
    public static function get($name)
    {
        $endpoint = new MongoEndpoint();
        $endpoint->username = config("mongo.{$name}.username");
        $endpoint->password = config("mongo.{$name}.password");
        $endpoint->host = config("mongo.{$name}.host");
        $endpoint->port = config("mongo.{$name}.port");

        if (!isset($endpoint->host) || !isset($endpoint->port)) {
            throw new \Exception("MongoDB Config Error.");
        }

        return $endpoint;
    }

    public function getManager()
    {
        $manager = new \MongoDB\Driver\Manager($this->parseConnection(), $this->parseOptions());

        return $manager;
    }

    private function parseConnection()
    {
        return "mongodb://{$this->host}:{$this->port}";
    }

    private function parseOptions()
    {
        $options = [];

        if (!empty($this->username)) {
            $options["username"] = $this->username;
        }
        if (!empty($this->password)) {
            $options["password"] = $this->password;
        }

        return $options;
    }
}