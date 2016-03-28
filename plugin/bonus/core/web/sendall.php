<?php
global $_W, $_GPC;
ca('bonus.sendall');
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
$set = $this->getSet();
$time             = time();
$pindex    = max(1, intval($_GPC['page']));
$psize     = 20;
$day_times        = intval($set['settledaysdf']) * 3600 * 24;
$daytime = strtotime(date("Y-m-d 00:00:00"));
$sql = "select sum(o.price) from ".tablename('sz_yi_order')." o left join " . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and ({$time} - o.finishtime > {$day_times})  ORDER BY o.finishtime DESC,o.status DESC";
$ordermoney = pdo_fetchcolumn($sql);
$sql = "select count(*) from ".tablename('sz_yi_member')." m left join " . tablename('sz_yi_bonus_level') . " l on m.bonuslevel=l.id and m.bonus_status=1 where 1 and l.premier=1 and m.uniacid={$_W['uniacid']}";
$total = pdo_fetchcolumn($sql);
$sql = "select m.* from ".tablename('sz_yi_member')." m left join " . tablename('sz_yi_bonus_level') . " l on m.bonuslevel=l.id and m.bonus_status=1 where 1 and l.premier=1 and m.uniacid={$_W['uniacid']}";
$setshop = m('common')->getSysset('shop');
if (empty($_GPC['export'])) {
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
}
$list = pdo_fetchall($sql);
$totalmoney = 0;
foreach ($list as $key => &$row) {
	$bonuspremier = $this->model->premierInfo($row['openid'], array('ok', 'pay'));
	$level = pdo_fetch("select * from " . tablename('sz_yi_bonus_level') . " where id=".$row['bonuslevel']);
	$row['levelname'] = $level['levelname'];
	$row['commission_ok'] = round($bonuspremier['commission_ok']*$level['pcommission']/100,2);
	$row['commission_pay'] = number_format($bonuspremier['commission_pay'],2);
	$totalmoney += $row['commission_ok'];	
}
unset($row);
$send_bonus_sn = time();
$sendpay_error = 0;
$bonus_money = 0;
if (!empty($_POST)) {
	foreach ($list as $key => $value) {
		$send_money = $value['commission_ok'];
		$sendpay = 1;
		if(empty($set['paymethod'])){
			m('member')->setCredit($value['openid'], 'credit2', $send_money);
		}else{
			$logno = m('common')->createNO('bonus_log', 'logno', 'RB');
			$result = m('finance')->pay($value['openid'], 1, $send_money * 100, $logno, "【" . $setshop['name']. "】".$value['levelname']."分红");
	        if (is_error($result)) {
	            $sendpay = 0;
	            $sendpay_error = 1;
	        }
		}
		pdo_insert('sz_yi_bonus_log', array(
            "openid" => $value['openid'],
            "uid" => $value['uid'],
            "money" => $send_money,
            "uniacid" => $_W['uniacid'],
            "paymethod" => $set['paymethod'],
            "sendpay" => $sendpay,
            "isglobal" => 1,
			"status" => 1,
            "ctime" => time(),
            "send_bonus_sn" => $send_bonus_sn
        ));
        if($sendpay == 1){
        	$this->model->sendMessage($member['openid'], array('nickname' => $value['nickname'], 'levelname' => $value['levelname'], 'commission' => $send_money, 'type' => empty($set['paymethod']) ? "余额" : "微信钱包"), TM_BONUS_GLOPAL_PAY);
        }
	}
	$log = array(
            "uniacid" => $_W['uniacid'],
            "money" => $totalmoney,
            "status" => 1,
            "ctime" => time(),
            "paymethod" => $set['paymethod'],
            "sendpay_error" => $sendpay_error,
            "isglobal" => 1,
            'utime' => $daytime,
            "send_bonus_sn" => $send_bonus_sn,
            "total" => $total
            );
    pdo_insert('sz_yi_bonus', $log);
    message("全球分红发放成功", $this->createPluginWebUrl('bonus/detail', array("sn" => $send_bonus_sn)), "success");
}
include $this->template('sendall');
