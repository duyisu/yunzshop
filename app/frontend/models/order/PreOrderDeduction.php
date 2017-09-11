<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/7/25
 * Time: 下午7:33
 */

namespace app\frontend\models\order;

use app\frontend\modules\order\models\PreGeneratedOrder;
use Yunshop\Love\Frontend\Models\LoveOrder;

class PreOrderDeduction extends \app\common\models\order\OrderDeduction
{
    public $order;
    //public $checked = false;
    /**
     * @var LoveOrder
     */
    public $deduction;
    public function setOrder(PreGeneratedOrder $order)
    {
        $this->order = $order;
        $this->_init();
    }
    private function _init(){
        $this->uid = $this->order->uid;
        $this->order->orderDeductions->push($this);
        $this->qty = $this->getActualCoin()->getAmountOfCoin();
        $this->amount = $this->getActualCoin()->getAmountOfMoney();
        $this->name = $this->getName();
        $this->deduction_id = $this->getDeductionId();
    }
//    public function save(array $options = [])
//    {
//        if(!$this->checked){
//            return false;
//        }
//        return parent::save($options);
//    }
    public function setInstance($deduction)
    {
        $this->deduction = $deduction;
    }
    public function getDeductionId(){
        return $this->deduction->getDeductionId();
    }
    public function getName(){
        return $this->deduction->getName();
    }
    public function getMaxCoin(){
        return $this->deduction->getMaxCoin();
    }
    public function getActualCoin(){
        return $this->deduction->getActualCoin();

    }
    public function isChecked(){
        return $this->deduction->isChecked();
    }
    public function save(array $options = [])
    {
        if(!$this->isChecked()){
            // todo 应该返回什么
            return true;
        }
        return parent::save($options); // TODO: Change the autogenerated stub
    }
}