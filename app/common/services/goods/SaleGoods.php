<?php
/**
 * Created date 2017/12/14 10:03
 * Author: 芸众商城 www.yunzshop.com
 */

namespace app\common\services\goods;

use app\common\models\Goods;
use app\common\models\Sale;
use app\common\services\goods\VideoDemandCourseGoods;

class SaleGoods extends Sale
{

    /**
     * 获取推广商品
     * @param  [int] $goods_id [商品id]
     * @return [array]         [推广的商品数据]
     */
    public static function getPushGoods($goods_id)
    {
        $data = self::where('goods_id', $goods_id)->first();
        $video_demand = new VideoDemandCourseGoods();
        if ($data->is_push == 1) {
            $goods_ids = explode('-', $data->push_goods_ids);
            $push_goods = Goods::getPushGoods($goods_ids);
            
            foreach ($push_goods as &$value) {
               $value['thumb'] = replace_yunshop(yz_tomedia($value['thumb']));
               $value['is_course'] = $video_demand->isCourse($value['id']);
            }
        } else {
            return array();
        }

        if (count($push_goods) > 4) {
            $push_goods = array_slice(self::shuffle_assoc($push_goods), 0, 4);
        }
        return $push_goods;
    }

     /**
     * 打乱二维数组
     * @param  [type] $list [description]
     * @return [type]       [description]
     */
    public static function shuffle_assoc($list) { 
        if (!is_array($list)) return $list; 
        $keys = array_keys($list); 
        shuffle($keys); 

        $random = array(); 

        foreach ($keys as $key) {
            $random[$key] = $list[$key];
        } 
        return $random; 
    } 
}