<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2019/5/15
 * Time: 17:12
 */

namespace app\frontend\modules\orderPay\controllers;


use app\common\components\ApiController;
use app\common\models\OrderPay;
use app\common\models\Order;
use app\common\services\finance\CalculationPointService;
use app\common\listeners\point\PointListener;
use  app\common\models\finance\PointLog;

class SuccessfulPaymentController extends ApiController
{


    /**
     * 支付跳转页面
     */
    public function paymentJump($outtradeno)
    {
        $outtradeno = \YunShop::request()->outtradeno;
        $data = [];
        $data['app_links'] = '';
        $data['integral'] = '';
        /**
         * 判断是余额还是第三方支付
         */
        if ($outtradeno){

            if(preg_match('/^([1-9][0-9]*){1,10}$/',$outtradeno)){
                //余额
                $orderPay = OrderPay::find($outtradeno);
            }else{
                $orderPay = OrderPay::where('pay_sn', $outtradeno)->first();
            }
//            $orders = Order::find($orderPay->order_ids);

            $orders = Order::where('id', $orderPay->order_ids)->with('orderGoods')->first();


            $order_integral = PointListener::getPointDateByOrder($orders);//point

            $integral = PointListener::byGoodsGivePoint($orders);
//
//            foreach ($orders as $itme){
//                $integral = PointLog::where('order_id',$itme->id)->first();
//                $data['integral'] += $integral['point'];
//            }
            $data['integral'] = bcadd($integral['point_data']['point'] , $order_integral['point'],2);
        }

        if (app('plugins')->isEnabled('app-set')) {
            $set = \Setting::get('shop_app.pay');
            $data['app_links'] = $set['app_links'];
        }
        return $this->successJson('请求成功',$data);
    }


}