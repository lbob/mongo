<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/6
 * Time: 上午6:31
 */

namespace Xag\Mongo;


class NotConditionValue extends BaseConditionValue
{

    public function getValue()
    {
        return ['$not' => $this->value];
    }
}