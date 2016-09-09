<?php
global $_W, $_GPC;
$openid = m('user')->getOpenid();
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'order';
$storeid = intval($_GPC['storeid']);
$order = pdo_fetchall(" SELECT * FROM ".tablename('sz_yi_order')." WHERE storeid=:id and uniacid=:uniacid ", array(':uniacid' => $_W['uniacid'], ':id' => $storeid));

$ordercount = count($order);
$totalprice = 0;
foreach ($order as $value) {
    $totalprice += $value['price'];
}


if ($_W['isajax']) {

    if ($operation == 'order') {
        //echo "<pre>"; print_r(1);exit;
        $status = trim($_GPC['status']);
        if ($status != ''){
            $conditionq = '  and o.status=' . intval($status);
        }else {
            $conditionq = '  and o.status>=0';
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "select o.id,o.ordersn,o.price,o.openid,o.status,o.address,o.createtime from " . tablename('sz_yi_order') . " o " . " left join  ".tablename('sz_yi_order_goods')."  og on o.id=og.orderid left join " . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and o.status>=0 " . " where 1 {$conditionq} and o.uniacid=".$_W['uniacid']." and o.storeid=".$storeid." ORDER BY o.createtime DESC,o.status DESC  ";
        $sql .= "LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $listsd = pdo_fetchall($sql);
        foreach ($listsd as &$rowp) {
            $sql = 'SELECT og.goodsid,og.total,g.title,g.thumb,og.price,og.optionname as optiontitle,og.optionid FROM ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on og.goodsid = g.id ' . ' where og.orderid=:orderid order by og.id asc';
            $rowp['goods'] = set_medias(pdo_fetchall($sql, array(':orderid' => $rowp['id'])), 'thumb');
            $rowp['goodscount'] = count($rowp['goods']);
            $address = unserialize($rowp['address']);
            $rowp['address'] = $address['address'];
            $rowp['province'] = $address['province'];
            $rowp['city'] = $address['city'];
            $rowp['area'] = $address['area'];
            $rowp['commission_total'] = number_format($rowp['franchisee_money'], 2);
            $rowp['createtime'] = date('Y-m-d H:i', $rowp['createtime']);
            $rowp['isstatus'] = $rowp['status'];
            if ($rowp['status'] == 0) {
                $rowp['status'] = '待付款';
            } else {
                if ($rowp['status'] == 1) {
                    $rowp['status'] = '已付款';
                } else {
                    if ($rowp['status'] == 2) {
                        $rowp['status'] = '待收货';
                    } else {
                        if ($rowp['status'] == 3) {
                            $rowp['status'] = '已完成';
                        }
                    }
                }
            }
        }
        /*foreach ($listsd as &$rowp) {
             $commission = iunserializer($rowp['franchisee_money']);
             $commission_total = isset($commission['level1']) ? $commission['level1'] : $commission['default'];
             $rowp['commission']=$commission_total;
             $address = unserialize($rowp['address']);
             $rowp['address'] = $address['address'];
             $rowp['province'] = $address['province'];
             $rowp['city'] = $address['city'];
             $rowp['area'] = $address['area'];
             $rowp['commission_total'] = number_format($rowp['franchisee_money'], 2);
             $rowp['createtime'] = date('Y-m-d H:i', $rowp['createtime']);
             $rowp['isstatus'] = $rowp['status'];
             if ($rowp['status'] == 0) {
             $rowp['status'] = '待付款';
            } else {
                 if ($rowp['status'] == 1) {
                     $rowp['status'] = '已付款';
                 } else {
                     if ($rowp['status'] == 2) {
                         $rowp['status'] = '待收货';
                     } else {
                         if ($rowp['status'] == 3) {
                             $rowp['status'] = '已完成';
                         }
                     }
                 }
            }
        }*/
        show_json(2, array('list' => $listsd,'pagesize' => $psize,'setlevel'=>$setids));


    } elseif ($operation == 'order_cancel') {
        $orderid = $_GPC['orderid'];
        pdo_update('sz_yi_order', array('agentuid' => 0, 'ownerid' => 0), array('id' => $orderid, 'uniacid' => $_W['uniacid']));
        $data = array(
            'uniacid'       => $_W['uniacid'],
            'orderid'       => $orderid,
            'from_agentuid' => $member['uid']
        );
        pdo_insert("sz_yi_cancel_goods", $data);
        show_json(1,'取消订单成功');
    } elseif ($operation == 'order_send') {
        $orderid = $_GPC['orderid'];
        pdo_update('sz_yi_order', array('status' => 2), array('id' => $orderid, 'uniacid' => $_W['uniacid']));
        m('notice')->sendOrderMessage($orderid);
        show_json(1,"");
    }
}


include $this->template('order');
