<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/6/16
 * Time: 下午10:35
 */

namespace Xag\Mongo;


class OrderInfo
{
    const SORT_DESC = 'DESC';
    const SORT_ASC = 'ASC';

    public $field;

    public $sort;

    public static function make($field, $sort)
    {
        $orderInfo = new OrderInfo();
        $orderInfo->field = $field;
        $orderInfo->sort = $sort;

        return $orderInfo;
    }
}