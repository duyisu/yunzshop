<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/2
 * Time: 下午4:55
 */

namespace app\frontend\modules\order\services\status;


use app\common\models\Order;

class WaitSend implements StatusService
{
    private $order;
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getStatusName()
    {
        return '待发货';
    }

    public function getButtonModels()
    {
        $result =
            [

            ];
        if(empty($this->order->refund_id)){
            $result[] = [
                'name' => '申请退款',
                'api' => 'order.refund.apply', //todo
                'value' => static::REFUND
            ];
        }
        return $result;
    }
}