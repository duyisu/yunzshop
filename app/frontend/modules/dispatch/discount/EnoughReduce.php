<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/5/23
 * Time: 上午11:17
 */

namespace app\frontend\modules\dispatch\discount;

use app\common\facades\Setting;

/**
 * 全场运费满额减
 * Class EnoughReduce
 * @package app\frontend\modules\dispatch\discount
 */
class EnoughReduce extends BaseFreightDiscount
{
    protected $name = '全场运费满额减';
    protected $code = 'freightEnoughReduce';

    protected function _getAmount()
    {
        if (!Setting::get('enoughReduce.freeFreight.open')) {
            debug_log()->freight('全场运费满额减','设置未开启');
            return 0;
        }
        //只有商城,供应商订单参加
        if($this->order->plugin_id != 0){
            debug_log()->freight('全场运费满额减','只有商城,供应商订单参加');
            return 0;
        }
        // 不参与包邮地区
        if (in_array($this->order->orderAddress->city_id, Setting::get('enoughReduce.freeFreight.city_ids'))) {
            debug_log()->freight('全场运费满额减',"{$this->order->orderAddress->city_id}地区不参加");
            return 0;
        }
        // 设置为0 全额包邮
        if (Setting::get('enoughReduce.freeFreight.enough') === 0 || Setting::get('enoughReduce.freeFreight.enough') === '0') {
            debug_log()->freight('全场运费满额减',"全额包邮");
            return $this->order->getDispatchAmount();
        }
        // 订单金额满足满减金额
        if ($this->order->price >= Setting::get('enoughReduce.freeFreight.enough')) {
            debug_log()->freight('全场运费满额减',"订单金额{$this->order->price}满足".Setting::get('enoughReduce.freeFreight.enough'));
            return $this->order->getDispatchAmount();
        }
        debug_log()->freight('全场运费满额减',"订单金额{$this->order->price}不满足".Setting::get('enoughReduce.freeFreight.enough'));
        return 0;
    }
}