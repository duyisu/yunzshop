<?php
/**
 * 管理后台APP API公众号列表接口
 *
 * PHP version 5.6.15
 *
 * @package   公众号模块
 * @author    shenyang <shenyang@yunzshop.com>
 * @version   v1.0
 */
namespace controller\api\account;
class Display extends \api\YZ
{
    public function __construct()
    {
        parent::__construct();
    
    }
    public function index()
    {
        $list[] = array(
            'uniacid'=>'2',
            'name'=>'沈阳的secretgarden',
            'thumb'=>'/headimg_2.jpg?t='.time(),
            'setmeal'=>'未设置'
        );
        $list = set_medias($list, "thumb");
        $this->returnSuccess($list);
    }
    public function index1()
    {
        global $_W,$_GPC;

        $condition = '';
        $pars = array();

        $_W['isfounder'] = $this->isFonder();


        if (empty($_W['isfounder'])) {
            $condition .= " AND a.`uniacid` IN (SELECT `uniacid` FROM " . tablename('uni_account_users') . " WHERE `uid`=:uid)";
            $pars[':uid'] = $_W['uid'];
        }

        $tsql = "SELECT COUNT(*) FROM " . tablename('uni_account') . " as a LEFT JOIN" . tablename('account') . " as b ON a.default_acid = b.acid WHERE a.default_acid <> 0 {$condition}";
        $total = pdo_fetchcolumn($tsql, $pars);
//$pager = pagination($total, $pindex, $psize);
        $sql = "SELECT * FROM " . tablename('uni_account') . " as a LEFT JOIN" . tablename('account') . " as b ON a.default_acid = b.acid WHERE a.default_acid <> 0 {$condition} ORDER BY a.`rank` DESC, a.`uniacid` DESC LIMIT {$start}, {$psize}";
        $list = pdo_fetchall($sql, $pars);
        if (!empty($list)) {
            foreach ($list as $unia => &$account) {
                $account['details'] = uni_accounts($account['uniacid']);
                $account['role'] = uni_permission($_W['uid'], $account['uniacid']);
                $account['setmeal'] = uni_setmeal($account['uniacid']);
            }
        }
        /*
        if(!$_W['isfounder']) {
            $stat = user_account_permission();
        }

        if (!empty($_W['setting']['platform']['authstate'])) {
            load()->classs('weixin.platform');
            $account_platform = new WeiXinPlatform();
            $authurl = $account_platform->getAuthLoginUrl();
        }
        */
        dump($list);
        $this->returnSuccess($list);
    }
}