<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午8:43
 */

namespace Xag\Mongo;


abstract class BaseConditionValue
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    abstract public function getValue();
}