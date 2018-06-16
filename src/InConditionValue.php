<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/6
 * Time: 上午6:09
 */

namespace Xag\Mongo;


class InConditionValue extends BaseConditionValue
{

    public function getValue()
    {
        return ['$in' => $this->value];
    }
}