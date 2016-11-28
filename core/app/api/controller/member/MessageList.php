<?php
namespace app\api\controller\member;
@session_start();
use app\api\YZ;
use yii\helpers\ArrayHelper;

/**
 * 通知列表API
 */
class MessageList extends YZ
{
    public function index(){
        if (!defined('IN_IA')) {
            exit('Access Denied');
        }

        $openid = m('user')->getOpenid();
        
        $page = max(1, intval($_GPC['page']));
        $psize = 20;

        $list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_message') . " WHERE `openid` = '" . $openid . "' ORDER BY `id` DESC LIMIT ".($page - 1)*$psize . ',' . $psize);
        $result = show_json(1, array('list'=>$list));

        $this->returnSuccess($result);
    }
}
