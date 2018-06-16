<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/6/16
 * Time: 下午10:41
 */

namespace Xag\Mongo;


use Illuminate\Support\ServiceProvider;

class MongoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->publishes([
            __DIR__ . "/config/mongo.php" => config_path("mongo.php")
        ], "config");
    }
}