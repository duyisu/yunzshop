<?php
/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/11/8
 * Time: 上午11:04
 */

namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;
use app\common\helpers\Url;
use app\common\services\notice\WechatApi;
use app\common\models\TemplateMessageDefault;
use app\common\models\notice\MessageTemp;

class WechatNoticeController extends BaseController
{
    private $WechatApiModel;
    private $list;
    private $industry_text;

    public function __construct()
    {
        $this->WechatApiModel = new WechatApi();
        $this->list = $this->WechatApiModel->getTmpList();
        $this->industry_text = $this->WechatApiModel->getIndustryText($this->WechatApiModel->getIndustry());
    }
    public function index()
    {
        return view('setting.wechat-notice.wechat_tmp_list', [
            'industry_text' => $this->industry_text,
            'list'          => $this->list['template_list'],
        ])->render();
    }

    public function returnJson()
    {
        return $this->successJson('获取公众号模板列表成功', ['tmp_list' => $this->list['template_list']]);
    }

    public function addTmp()
    {
        if (!request()->templateidshort) {
            return $this->errorJson('请填写模板编码');
        }
        $ret = $this->WechatApiModel->getTmpByTemplateIdShort(request()->templateidshort);
        if ($ret['status'] == 0) {
            return $this->errorJson($ret['msg']);
        } else {
            return $this->successJson($ret['msg'], []);
        }
    }

    public function del()
    {
        $tmp_id = request()->tmp_id;
        if (is_array($tmp_id)) {
            foreach ($tmp_id as $id) {
                $this->WechatApiModel->delTmpByTemplateId($id);
                TemplateMessageDefault::delData($id);//删除微信消息模版
                MessageTemp::delTempDataByTempId($id);//删除默认消息模版
            }
        } else {
            $this->WechatApiModel->delTmpByTemplateId($tmp_id);
            TemplateMessageDefault::delData($tmp_id);//删除消息模版
            MessageTemp::delTempDataByTempId($tmp_id);//删除默认消息模版
        }
        return $this->message('删除成功', Url::absoluteWeb('setting.wechat-notice.index'));
    }

    public function see()
    {
        $tmp = '';
        $list = $this->WechatApiModel->getTmpList();
        foreach ($list['template_list'] as $temp) {
            while ($temp['template_id'] == request()->tmp_id)
            {
                $tmp = $temp;
                break;
            }
        }
        return view('setting.wechat-notice.see', [
            'template' => $tmp
        ])->render();
    }
}