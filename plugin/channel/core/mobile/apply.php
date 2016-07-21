<?php
global $_W, $_GPC;
$openid = m('user')->getOpenid();
if ($_W['isajax']) {
	$member 					= m('member')->getMember($openid);
	$channelinfo 				= $this->model->getInfo($openid);
	$commission_ok 				= $channelinfo['channel']['commission_ok'];
	$commission_ok 				= number_format($commission_ok, 2);
	$cansettle 					= $commission_ok >= 1;
	$member['commission_ok'] 	= number_format($commission_ok, 2);
	if ($_W['ispost']) {
		$time = time();
		$channel_goods = pdo_fetchall("SELECT og.id FROM " . tablename('sz_yi_order_goods') . " og left join " .tablename('sz_yi_order') . " o on (o.id=og.orderid) WHERE og.uniacid={$_W['uniacid']} AND og.channel_id={$member['id']} AND o.status=3 AND og.channel_apply_status=0");
		$applyno = m('common')->createNO('commission_apply', 'applyno', 'CA');
		$apply_ordergoods_ids = array();
        foreach ($channel_goods as $key => $value) {
            $apply_ordergoods_ids[] = $value['id'];
        }
        $apply_ordergoods_ids = implode(',', $apply_ordergoods_ids);
		$apply = array(
			'openid'				=> $openid,
			'type'					=> $_GPC['type'],
			'applyno'				=> $applyno,
			'apply_money'			=> $commission_ok,
			'apply_time'			=> $time,
			'status' 				=> 0,
			'uniacid'				=> $_W['uniacid'],
			'apply_ordergoods_ids' 	=> $apply_ordergoods_ids
			);
		pdo_insert('sz_yi_channel_apply', $apply);
		@file_put_contents(IA_ROOT . "/addons/sz_yi/data/apply.log", print_r($apply, 1), FILE_APPEND);
		if( pdo_insertid() ) {
			foreach ($channel_goods as $key => $value) {
				pdo_update('sz_yi_order_goods', array('channel_apply_status' => 1), array('id' => $value['id'], 'uniacid' => $_W['uniacid']));
			}
			$tmp_sp_goods 				= $channel_goods;
			$tmp_sp_goods['applyno'] 	= $applyno;
			@file_put_contents(IA_ROOT . "/addons/sz_yi/data/channel_goods.log", print_r($tmp_sp_goods, 1), FILE_APPEND);
		}

		$returnurl 	= urlencode($this->createPluginMobileUrl('channel/orderj'));
		$infourl 	= $this->createPluginMobileUrl('channel/orderj', array('returnurl' => $returnurl));
		//$this->model->sendMessage($openid, array('commission' => $commission_ok, 'type' => $apply['type'] == 2 ? '微信' : '线下'), TM_COMMISSION_APPLY);
		show_json(1, '已提交,请等待审核!');
	}
	$returnurl 	= urlencode($this->createPluginMobileUrl('commission/applyg'));
	$infourl 	= $this->createMobileUrl('member/info', array('returnurl' => $returnurl));
	show_json(1, array('commission_ok' => $member['commission_ok'], 'cansettle' => $cansettle, 'member' => $member, 'set' => $this->set, 'infourl' => $infourl, 'noinfo' => empty($member['realname'])));
}
include $this->template('apply');
