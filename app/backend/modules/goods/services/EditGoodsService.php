<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/5/2
 * Time: 上午11:51
 */

namespace app\backend\modules\goods\services;

use app\backend\modules\goods\models\GoodsSpecItem;
use app\backend\modules\goods\models\GoodsParam;
use app\backend\modules\goods\models\GoodsSpec;
use app\backend\modules\goods\models\GoodsOption;
use app\backend\modules\goods\models\Brand;
use app\common\models\Goods;
use app\common\models\GoodsCategory;
use Setting;

class EditGoodsService
{
    public $goods_id;
    public $goods_model;
    public $request;
    public $catetory_menus;
    public $brands;
    public $optionsHtml;
    public $type;

    public function __construct($goods_id, $request, $type = 0)
    {
        $this->type = $type;
        $this->goods_id = $goods_id;
        $this->request = $request;
        $this->goods_model = Goods::with('hasManyParams')->with('hasManySpecs')->with('hasManyGoodsCategory')->find($goods_id);
    }

    public function edit()
    {
        //商品属性默认值
        $arrt_default = [
            'is_recommand' => 0,
            'is_new' => 0,
            'is_hot' => 0,
            'is_discount' => 0
        ];

        //获取规格名及规格项
        $goods_data = $this->request->goods;
        $goods_data = array_merge($arrt_default, $goods_data);

        foreach ($this->goods_model->hasManySpecs as &$spec) {
            $spec['items'] = GoodsSpecItem::where('specid', $spec['id'])->get()->toArray();
        }

        //获取具体规格内容html
        $this->optionsHtml = GoodsOptionService::getOptions($this->goods_id, $this->goods_model->hasManySpecs);

        //商品其它图片反序列化
        $this->goods_model->thumb_url = !empty($this->goods_model->thumb_url) ? unserialize($this->goods_model->thumb_url) : [];

        if ($goods_data) {
            if ($this->type == 1) {
                $goods_data['status'] = 0;
            }
            $goods_data['has_option'] = $goods_data['has_option'] ? $goods_data['has_option'] : 0;
            //将数据赋值到model
            $goods_data['thumb'] = tomedia($goods_data['thumb']);

            if(isset($goods_data['thumb_url'])){
                $goods_data['thumb_url'] = serialize(
                    array_map(function($item){
                        return tomedia($item);
                    }, $goods_data['thumb_url'])
                );
            }

            $category_model = GoodsCategory::where("goods_id", $this->goods_model->id)->first();
            if (!empty($category_model)) {
                $category_model->delete();
            }
            GoodsService::saveGoodsCategory($this->goods_model, \YunShop::request()->category, Setting::get('shop.category'));

            $this->goods_model->setRawAttributes($goods_data);
            $this->goods_model->widgets = $this->request->widgets;
            //其他字段赋值
            $this->goods_model->uniacid = \YunShop::app()->uniacid;
            $this->goods_model->id = $this->goods_id;
            //数据保存
            $validator = $this->goods_model->validator($this->goods_model->getAttributes());
            if ($validator->fails()) {
                return ['status' => -1, 'msg' => $validator->messages()];
                //$this->error($validator->messages());
            } else {
                if ($this->goods_model->save()) {
                    GoodsParam::saveParam($this->request, $this->goods_model->id, \YunShop::app()->uniacid);
                    GoodsSpec::saveSpec($this->request, $this->goods_model->id, \YunShop::app()->uniacid);
                    GoodsOption::saveOption($this->request, $this->goods_model->id, GoodsSpec::$spec_items, \YunShop::app()->uniacid);
                    //显示信息并跳转
                    return ['status' => 1];
                } else {
                    return ['status' => -1];
                }
            }

        }

        $this->brands = Brand::getBrands()->get();

        if (isset($this->goods_model->hasManyGoodsCategory[0])){
            foreach($goods_categorys = $this->goods_model->hasManyGoodsCategory->toArray() as $goods_category){
                $this->catetory_menus = CategoryService::getCategoryMenu(['catlevel' => Setting::get('shop.category')['cat_level'], 'ids' => explode(",", $goods_category['category_ids'])]);
            }
        }
    }
}