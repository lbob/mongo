<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午8:56
 */

namespace Xag\Mongo;


class GteConditionValue extends BaseConditionValue
{

    public function getValue()
    {
        return ['$gte' => $this->value];
    }
}