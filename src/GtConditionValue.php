<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午8:53
 */

namespace Xag\Mongo;


class GtConditionValue extends BaseConditionValue
{
    public function getValue()
    {
        return ['$gt' => $this->value];
    }
}