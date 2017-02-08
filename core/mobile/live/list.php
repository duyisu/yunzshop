<?php 
global $_W, $_GPC;
$domain = $_SERVER['HTTP_HOST'];
$uniacid = $_W['uniacid'];
$openid = m('user')->isLogin();
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

define('SZ_YI_LIVE_SIGN_CLOUD_URL','http://live.tbw365.cn'); //云端获取sign签名的服务器

if ($operation == 'display'){

    //curl请求"获取直播间列表"的API
    load()->func('communication');
    $url = SZ_YI_LIVE_CLOUD_URL.'/shop_live.php?api=room&domain='.$domain.'&uniacid='.$uniacid;
    // $url = SZ_YI_LIVE_CLOUD_URL . '/test/shop_live.php?api=room&domain=demo.yunzshop.com&uniacid=3'; //测试用 todo
    $result = ihttp_get($url);
    $result_array = json_decode($result['content'], true);
    $room_list = $result_array['data'];

    //获取banner列表
    $banner_list = pdo_fetchall('SELECT advname, link, thumb FROM ' . tablename('sz_yi_live_banner') . ' WHERE enabled = 1 AND uniacid = :uniacid ORDER BY displayorder DESC', array('uniacid' => $uniacid)); //todo 是否需要数量限制
    $banner_list = set_medias($banner_list, 'thumb');

    //获取sig
    if(!empty($openid)){
        $result_02 = ihttp_get( SZ_YI_LIVE_SIGN_CLOUD_URL.'/shop_live.php?api=IM/Get/sign&openid='.$openid.'&domain='.$domain);
        $result_02_array = json_decode($result_02['content'], true);
        $sig = $result_02_array['data']['sign'];
    }

}

include $this->template('live/list');
