<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午8:58
 */

namespace Xag\Mongo;


class NeConditionValue extends BaseConditionValue
{

    public function getValue()
    {
        return ['$ne' => $this->value];
    }
}