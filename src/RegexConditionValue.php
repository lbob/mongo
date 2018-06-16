<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/6
 * Time: 上午9:34
 */

namespace Xag\Mongo;


class RegexConditionValue extends BaseConditionValue
{

    public function getValue()
    {
        return ['$regex' => $this->value];
    }
}