<?php
/**
 * Created by PhpStorm.
 * User: liubo
 * Date: 2018/2/6
 * Time: 上午6:14
 */

namespace Xag\Mongo;


class BetweenConditionValue extends BaseConditionValue
{
    private $includeLeft;

    private $includeRight;

    public function __construct($value, $includeLeft, $includeRight)
    {
        $this->includeLeft = $includeLeft;
        $this->includeRight = $includeRight;
        parent::__construct($value);
    }

    public function getValue()
    {
        $leftOp = '$gt';
        if ($this->includeLeft) {
            $leftOp = '$gte';
        }
        $rightOp = '$lt';
        if ($this->includeRight) {
            $rightOp = '$lte';
        }

        if (!isset($this->value[0]) || !isset($this->value[1])) {
            throw new \Exception("Argument invalid!");
        }

        return [$leftOp => $this->value[0], $rightOp => $this->value[1]];
    }
}