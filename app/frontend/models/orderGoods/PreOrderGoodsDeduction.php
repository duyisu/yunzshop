<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/7/25
 * Time: 下午7:10
 */

namespace app\frontend\models\orderGoods;

use app\frontend\modules\orderGoods\models\PreGeneratedOrderGoods;

class PreOrderGoodsDeduction extends \app\common\models\orderGoods\OrderGoodsDeduction
{
    public $orderGoods;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function setOrderGoods(PreGeneratedOrderGoods $orderGoods)
    {
        $this->orderGoods = $orderGoods;
        $orderGoods->orderGoodsDeductions->push($this);

    }
    public function save(array $options = [])
    {
        $this->uid = $this->orderGoods->uid;
        return parent::save($options); // TODO: Change the autogenerated stub
    }
}