<?php
/**
 * Created by Sublime Text.
 * User: rayyang
 * Date: 17/02/10
 * Time: 10:23
 */

global $_W, $_GPC;

$type = isset($_GPC['type']) ? $_GPC['type'] : 'province';

if ($type == 'province') {

	//获取省份
	$res = pdo_fetchall(" select * from " . tablename('sz_yi_address') . " where level = 1");
	echo json_encode($res);
} elseif ($type == 'city') {

	//获取城市
	$res = pdo_fetchall(" select * from " . tablename('sz_yi_address') . " where level = 2 and parentid = ( select id from " . tablename('sz_yi_address') . " where areaname = '".$_GPC['v1']."' )");
	echo json_encode($res);
} elseif ($type == 'district') {

	//获取区域
	$res = pdo_fetchall(" select * from " . tablename('sz_yi_address') . " where level = 3 and parentid = ( select id from " . tablename('sz_yi_address') . " where areaname = '".$_GPC['v2']."' )");
	echo json_encode($res);
} elseif ($type == 'street') {

	//获取区域
	$res = pdo_fetchall(" select * from " . tablename('sz_yi_street') . " where level = 4 and parentid = ( select id from " . tablename('sz_yi_address') . " where areaname = '".$_GPC['v3']."' )");
	echo json_encode($res);
}



// $res = pdo_fetchall(" select * from " . tablename('sz_yi_street') . " where province = '".$_GPC['v1']."' AND city = '".$_GPC['v2']."' AND county = '".$_GPC['v3']."'");
// echo "<pre>";print_r($res);exit;
// echo json_encode($res);
