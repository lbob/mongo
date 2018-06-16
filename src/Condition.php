<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/5
 * Time: 下午8:42
 */

namespace Xag\Mongo;


class Condition
{
    const TYPE_AND = 1;
    const TYPE_OR = 2;

    private $field;

    private $value;

    private function __construct($field, BaseConditionValue $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public static function eq($field, $value)
    {
        return new Condition($field, new StringConditionValue($value));
    }

    public static function lt($field, $value)
    {
        return new Condition($field, new LtConditionValue($value));
    }

    public static function lte($field, $value)
    {
        return new Condition($field, new LteConditionValue($value));
    }

    public static function gt($field, $value)
    {
        return new Condition($field, new GtConditionValue($value));
    }

    public static function gte($field, $value)
    {
        return new Condition($field, new GteConditionValue($value));
    }

    public static function ne($field, $value)
    {
        return new Condition($field, new StringConditionValue($value));
    }

    public static function in($field, array $values)
    {
        return new Condition($field, new InConditionValue($values));
    }

    public static function between($field, array $ranges, $includeLeft = true, $includeRight = true)
    {
        return new Condition($field, new BetweenConditionValue($ranges, $includeLeft, $includeRight));
    }

    public static function not($field, array $conditions)
    {
        return new Condition($field, new NotConditionValue($conditions));
    }

    public static function regex($field, $regex)
    {
        return new Condition($field, new RegexConditionValue($regex));
    }

    public function getValue()
    {
        return [$this->field => $this->value->getValue()];
    }
}