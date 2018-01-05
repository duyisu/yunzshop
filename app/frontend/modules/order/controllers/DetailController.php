<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/3
 * Time: 上午9:10
 */

namespace app\frontend\modules\order\controllers;

use app\common\components\ApiController;
use app\common\models\DispatchType;
use app\common\models\Order;
use app\common\requests\Request;
use app\frontend\models\OrderAddress;
use Yunshop\StoreCashier\common\models\StoreDelivery;
use app\frontend\modules\order\services\VideoDemandOrderGoodsService;

class DetailController extends ApiController
{
    public function index(Request $request)
    {
        $this->validate([
            'order_id' => 'required|integer'
        ]);
        $orderId = $request->query('order_id');

        $order = $this->getOrder()->with(['hasManyOrderGoods','orderDeduction','orderDiscount','orderCoupon'])->find($orderId);

//        if ($order->uid != \YunShop::app()->getMemberId()) {
//            throw new AppException('(ID:' . $order->id . ')该订单属于其他用户');
//        }
        $data = $order->toArray();
        $data['button_models'] = array_merge($data['button_models'],$order->getStatusService()->getRefundButtons($order));

        //$this->getStatusService()->
        //todo 配送类型
        if ($order['dispatch_type_id'] == DispatchType::EXPRESS) {
            $data['address_info'] = OrderAddress::select('address', 'mobile', 'realname')->where('order_id', $order['id'])->first();
        }
        if(app('plugins')->isEnabled('store-cashier')){
            if ($order['dispatch_type_id'] == DispatchType::SELF_DELIVERY) {
                $data['address_info'] = \Yunshop\StoreCashier\common\models\SelfDelivery::where('order_id', $order['id'])->first();
            }elseif($order['dispatch_type_id'] == DispatchType::STORE_DELIVERY){
                $data['address_info'] = \Yunshop\StoreCashier\common\models\StoreDelivery::where('order_id', $order['id'])->first();
            }
        }
        //todo 临时解决
        if (!$order) {
            return $this->errorJson($msg = '未找到数据', []);
        } else {

            //视频点播
            if (VideoDemandOrderGoodsService::whetherEnabled()) {
                foreach ($data['has_many_order_goods'] as &$value) {
                    $value['is_course'] = VideoDemandOrderGoodsService::whetherCourse($value['goods_id']);
                }
            }
            
            return $this->successJson($msg = 'ok', $data);
        }

    }

    protected function getOrder()
    {
        return app('OrderManager')->make('Order');
    }
}