<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/10/27
 * Time: 下午8:01
 */

namespace app\frontend\modules\payment\paymentSettings;

use app\common\models\Order;

abstract class PaymentSetting implements PaymentSettingInterface
{
    function __construct()
    {
    }
}