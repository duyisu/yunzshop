<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/9
 * Time: 上午9:25
 */

namespace app\frontend\modules\dispatch\models;

use app\common\events\dispatch\OrderDispatchWasCalculated;
use app\common\models\goods\GoodsDispatch;
use app\frontend\modules\order\models\PreOrder;
use app\common\models\MemberShopInfo;


class OrderDispatch
{
    private $order;

    public function __construct(PreOrder $preOrder)
    {
        $this->order = $preOrder;
    }

    /**
     * 订单运费
     * @return float|int
     */
    public function getDispatchPrice()
    {
        if (!isset($this->order->hasOneDispatchType) || !$this->order->hasOneDispatchType->needSend()) {
            // 没选配送方式 或者 不需要配送配送
            return 0;
        }

        //临时解决，是柜子的不算运费
        if (!empty($this->order->mark)) {
            return 0;
        }

        $event = new OrderDispatchWasCalculated($this->order);
        event($event);
        $data = $event->getData();

        $freight = array_sum(array_column($data, 'price'));

        $freight_reduction = $this->levelFreeFreight($freight);

        $result = max(($freight - $freight_reduction), 0);

        return $result;
    }

    /**
     * Author: aaa Date: 2018/4/2
     * 会员等级运费优惠
     * @return [int] [优惠金额]
     */
    public function levelFreeFreight($freight)
    {
        $uid = intval($this->order->belongsToMember->uid);
        $member = MemberShopInfo::select('level_id')->with('level')->find($uid);

        if (isset($member->level) && isset($member->level->freight_reduction)) {
            $freight_reduction = intval($member->level->freight_reduction);

            return ($freight * ($freight_reduction / 100));
        }
        return 0;
    }

}