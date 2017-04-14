<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2017/3/17
 * Time: 下午12:00
 */

namespace app\common\services;

use app\common\exceptions\AppException;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\Member;
use app\common\models\Order;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order as easyOrder;

class WechatPay extends Pay
{
    public function doPay($data = [])
    {
        $op = '微信订单支付 订单号：' . $data['order_no'];
        $pay_order_model = $this->log($data['extra']['type'], Pay::PAY_MODE_WECHAT, $data['amount'], $op, $data['order_no'], Pay::ORDER_STATUS_NON);

        if (empty(\YunShop::app()->getMemberId())) {
            throw new AppException('无法获取用户ID');
        }

        $openid = Member::getOpenId(\YunShop::app()->getMemberId());
        $pay = \Setting::get('shop.pay');

        if (empty($pay['weixin_mchid']) || empty($pay['weixin_apisecret'])
            || empty($pay['weixin_appid']) || empty($pay['weixin_secret'])) {

            throw new AppException('没有设定支付参数');
        }

        $notify_url = Url::shopUrl('payment/wechat/notifyUrl.php');
        $app     = $this->getEasyWeChatApp($pay, $notify_url);
        $payment = $app->payment;
        $order = $this->getEasyWeChatOrder($data, $openid, $pay_order_model);
        $result = $payment->prepare($order);
        $prepayId = null;

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id;

            $this->changeOrderStatus($pay_order_model, Pay::ORDER_STATUS_WAITPAY);
        } else {
            throw new AppException('微信预下单失败');
        }

        $config = $payment->configForJSSDKPayment($prepayId);
        $config['appId'] = $pay['weixin_appid'];

//        $js = $app->js->config(array('chooseWXPay'));
//
//        return ['config'=>$config, 'js'=>json_decode($js, 1)];

        $js = $app->js;

        return ['config'=>$config, 'js'=>$js->config(array('chooseWXPay'))];
    }

    /**
     * 微信退款
     *
     * @param 订单号 $out_trade_no
     * @param 订单总金额 $totalmoney
     * @param 退款金额 $refundmoney
     * @return array
     */
    public function doRefund($out_trade_no, $totalmoney, $refundmoney)
    {
        $out_refund_no = $this->setUniacidNo(\YunShop::app()->uniacid);

        $op = '微信退款 订单号：' . $out_trade_no . '退款单号：' . $out_refund_no . '退款总金额：' . $totalmoney;
        $pay_order_model = $this->log(Pay::PAY_TYPE_REFUND, Pay::PAY_MODE_WECHAT, $refundmoney, $op, $out_trade_no, Pay::ORDER_STATUS_NON);

        $pay = \Setting::get('shop.pay');

        if (empty($pay['weixin_mchid']) || empty($pay['weixin_apisecret'])) {
            return error(1, '没有设定支付参数');
        }

        if (empty($pay['weixin_cert']) || empty($pay['weixin_key']) || empty($pay['weixin_root'])) {
            message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
        }

        $notify_url = Url::shopUrl('payment/wechat/refundUrl.php');
        $app     = $this->getEasyWeChatApp($pay, $notify_url);
        $payment = $app->payment;

        $result = $payment->refund($out_trade_no, $out_refund_no, $totalmoney*100, $refundmoney*100);

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $this->changeOrderStatus($pay_order_model, Pay::ORDER_STATUS_WAITPAY);

            $this->payResponseDataLog();

        } else {
            throw new \AppException('退款失败');
        }
    }

    /**
     * @param 提现者用户ID $member_id
     * @param 提现金额 $money
     * @param string $desc
     * @param int $type
     * @return array
     */
    public function doWithdraw($member_id, $out_trade_no, $money, $desc='', $type=1)
    {
        //$out_trade_no = $this->setUniacidNo(\YunShop::app()->uniacid);

        $op = '微信钱包提现 订单号：' . $out_trade_no . '提现金额：' . $money;
        $pay_order_model = $this->log(Pay::PAY_TYPE_REFUND, Pay::PAY_MODE_WECHAT, $money, $op, $out_trade_no, Pay::ORDER_STATUS_NON);

        $pay = \Setting::get('shop.pay');

        if (empty($pay['weixin_mchid']) || empty($pay['weixin_apisecret'])) {
            return error(1, '没有设定支付参数');
        }

        if (empty($pay['weixin_cert']) || empty($pay['weixin_key']) || empty($pay['weixin_root'])) {
            message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
        }

        $order_info = Order::getOrderInfoByMemberId($member_id)->first();

        if (!empty($order_info) && $order_info['status'] == 3) {
            $openid = Member::getOpenId($order_info['uid']);
        }

//        if (config('app.debug')) {
//            $openid = 'oNnNJwqQwIWjAoYiYfdnfiPuFV9Y';
//        }

        $notify_url = Url::shopUrl('payment/wechat/withdrawUrl.php');
        $app = $this->getEasyWeChatApp($pay, $notify_url);

        if ($type == 1) {//钱包
            $merchantPay = $app->merchant_pay;

            $merchantPayData = [
                'partner_trade_no' => empty($out_trade_no) ? time() . Client::random(4, true) : $out_trade_no,
                'openid' => $openid,
                'check_name' => 'NO_CHECK',
                'amount' => $money * 100,
                'desc' => empty($desc) ? '佣金提现' : $desc,
                'spbill_create_ip' => $this->ip,
            ];

            //请求数据日志
            $this->payRequestDataLog($pay_order_model->id, $pay_order_model->type,
                $pay_order_model->third_type, json_encode($merchantPayData));

            $result = $merchantPay->send($merchantPayData);
        } else {//红包
            $luckyMoney = $app->lucky_money;

            $luckyMoneyData = [
                'mch_billno'       => $app['weixin_mchid'] . date('YmdHis') . rand(1000, 9999),
                'send_name'        => \YunShop::app()->account['name'],
                're_openid'        => $openid,
                'total_num'        => 1,
                'total_amount'     => $money * 100,
                'wishing'          => empty($desc) ? '佣金提现红包' : $desc,
                'client_ip'        => $this->ip,
                'act_name'         => empty($act_name) ? '佣金提现红包' : $act_name,
                'remark'           => empty($remark) ? '佣金提现红包' : $remark,
            ];

            //请求数据日志
            $this->payRequestDataLog($pay_order_model->id, $pay_order_model->type,
                $pay_order_model->third_type, json_encode($luckyMoneyData));

            $result = $luckyMoney->sendNormal($luckyMoneyData);
        }

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $this->changeOrderStatus($pay_order_model, Pay::ORDER_STATUS_WAITPAY);

            $this->payResponseDataLog();

            return true;

        } else {
            throw new \AppException('退款失败');
        }

        return false;
    }

    /**
     * 构造签名
     *
     * @var void
     */
    public function buildRequestSign()
    {
        // TODO: Implement buildRequestSign() method.
    }

    /**
     * 创建支付对象
     *
     * @param $pay
     * @return \EasyWeChat\Payment\Payment
     */
    public function getEasyWeChatApp($pay, $notify_url)
    {
        $options = [
            'app_id'  => $pay['weixin_appid'],
            'secret'  => $pay['weixin_secret'],
            // payment
            'payment' => [
                'merchant_id'        => $pay['weixin_mchid'],
                'key'                => $pay['weixin_apisecret'],
                'cert_path'          => $pay['weixin_cert'],
                'key_path'           => $pay['weixin_key'],
                'notify_url'         => $notify_url
            ]
        ];

        $app = new Application($options);

        return $app;
    }

    /**
     * 创建预下单
     *
     * @param $data
     * @param $openid
     * @param $pay_order_model
     * @return easyOrder
     */
    public function getEasyWeChatOrder($data, $openid, &$pay_order_model)
    {
        $attributes = [
            'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...
            'body'             => $data['body'],
            'out_trade_no'     => $data['order_no'],
            'total_fee'        => $data['amount'] * 100, // 单位：分
            'nonce_str'        => Client::random(8) . "",
            'device_info'      => 'yun_shop',
            'attach'           => $data['extra']['type'],
            'spbill_create_ip' => $this->ip,
            'openid'           => $openid
        ];

        //请求数据日志
        $this->payRequestDataLog($pay_order_model->id, $pay_order_model->type,
            $pay_order_model->third_type, json_encode($attributes));

        return new easyOrder($attributes);
    }

    private function changeOrderStatus($model, $status)
    {
        $model->status = $status;
        $model->save();
    }
}