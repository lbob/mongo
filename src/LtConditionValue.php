<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午8:57
 */

namespace Xag\Mongo;


class LtConditionValue extends BaseConditionValue
{

    public function getValue()
    {
        return ['$lt' => $this->value];
    }
}