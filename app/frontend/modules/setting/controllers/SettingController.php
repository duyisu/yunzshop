<?php
namespace app\frontend\modules\setting\controllers;

use app\common\components\ApiController;
use app\common\components\BaseController;
use app\common\facades\Setting;

/**
 * Created by PhpStorm.
 * User: luckystar_D
 * Date: 2017/3/25
 * Time: 上午11:57
 */
class SettingController extends BaseController
{
    /**
     * 商城设置接口
     * @param string $key  setting表key字段值
     * @return json
     */
    public function getSetting()
    {
        $key = \YunShop::request()->setting_key ? \YunShop::request()->setting_key : 'shop';
        if (!empty($key)) {
            $setting = Setting::get('shop.' . $key);
        } else {
            $setting = Setting::get('shop');
        }
//        echo "<pre>"; print_r($setting);exit;
        if (!$setting) {
            $this->errorJson('未进行设置.');
        }
        $setting['logo'] = tomedia($setting['logo']);
        $this->successJson('获取商城设置成功', $setting);
    }
}