<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午8:44
 */

namespace Xag\Mongo;


class StringConditionValue extends BaseConditionValue
{
    public function getValue()
    {
        return $this->value;
    }
}