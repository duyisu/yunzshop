<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/11/14 上午10:22
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/

namespace app\backend\modules\finance\controllers;


use app\backend\modules\finance\models\Withdraw;
use app\backend\modules\member\models\MemberBankCard;
use app\backend\modules\member\models\MemberShopInfo;
use app\backend\modules\order\services\ExportService;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use Illuminate\Support\Facades\Config;

class WithdrawRecordsController extends BaseController
{
    public function index()
    {
        $pageSize = 10;

        $starttime = strtotime('-1 month');
        $endtime = time();

        $requestSearch = \YunShop::request()->search;
        if ($requestSearch) {

            if ($requestSearch['searchtime']) {
                if ($requestSearch['times']['start'] != '请选择' && $requestSearch['times']['end'] != '请选择') {
                    $requestSearch['times']['start'] = strtotime($requestSearch['times']['start']);
                    $requestSearch['times']['end'] = strtotime($requestSearch['times']['end']);
                    $starttime = strtotime($requestSearch['times']['start']);
                    $endtime = strtotime($requestSearch['times']['end']);
                } else {
                    $requestSearch['times'] = '';
                }
            } else {
                $requestSearch['times'] = '';
            }
            $requestSearch = array_filter($requestSearch, function ($item) {
                return $item !== '';// && $item !== 0;
            });
        }
        $configs = Config::get('income');
        foreach ($configs as $config) {
            $type[] = $config['class'];
        }
        $list = Withdraw::getWithdrawList($requestSearch)
            ->whereIn('type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize);

        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());
        $incomeConfug = Config::get('income');
        if (!$requestSearch['searchtime']) {
            $requestSearch['times']['start'] = time();
            $requestSearch['times']['end'] = time();
        }
//        echo '<pre>'; print_r(yzWebUrl('finance.withdraw.index&search',['search[status]'=>$requestSearch['status']])); exit;
        return view('finance.withdraw.withdraw-list', [
            'list' => $list,
            'pager' => $pager,
            'search' => $requestSearch,
            'starttime' => $starttime,
            'endtime' => $endtime,
            'types' => $incomeConfug,
        ])->render();
    }





    public function export()
    {

        $requestSearch = \YunShop::request()->search;
        if ($requestSearch) {
            if ($requestSearch['searchtime']) {
                if ($requestSearch['times']['start'] != '请选择' && $requestSearch['times']['end'] != '请选择') {
                    $requestSearch['times']['start'] = strtotime($requestSearch['times']['start']);
                    $requestSearch['times']['end'] = strtotime($requestSearch['times']['end']);
                    $starttime = strtotime($requestSearch['times']['start']);
                    $endtime = strtotime($requestSearch['times']['end']);
                } else {
                    $requestSearch['times'] = '';
                }
            } else {
                $requestSearch['times'] = '';
            }
            $requestSearch = array_filter($requestSearch, function ($item) {
                return $item !== '';// && $item !== 0;
            });
        }
        $configs = Config::get('income');
        foreach ($configs as $config) {
            $type[] = $config['class'];
        }
        $list = Withdraw::getWithdrawList($requestSearch)
            ->whereIn('type', $type);

        $export_page = request()->export_page ? request()->export_page : 1;
        $export_model = new ExportService($list, $export_page);

        $file_name = date('Ymdhis', time()) . '提现记录导出';

        $export_data[0] = [
            '提现编号',
            '粉丝',
            '姓名、手机',
            '收入类型',
            '提现方式',
            '申请金额',
            '申请时间',

            '打款至',

            '打款微信号',

            '支付宝姓名',
            '支付宝账号',

            '开户行',
            '开户行省份',
            '开户行城市',
            '开户行支行',
            '银行卡信息',
            '开户人姓名'
        ];
        foreach ($export_model->builder_model as $key => $item)
        {
            $export_data[$key + 1] = [
                $item->withdraw_sn,
                $item->hasOneMember->nickname,
                $item->hasOneMember->realname.'/'.$item->hasOneMember->mobile,
                $item->type_name,
                $item->pay_way_name,
                $item->amounts,
                $item->created_at->toDateTimeString(),
            ];
            if ($item->pay_way == 'manual') {
                switch ($item->manual_type) {
                    case 2:
                        $export_data[$key + 1][] = '微信';
                        $export_data[$key + 1] = array_merge($export_data[$key + 1], $this->getMemberWeChat($item->member_id));
                        break;
                    case 3:
                        $export_data[$key + 1][] = '支付宝';
                        $export_data[$key + 1] = array_merge($export_data[$key + 1], $this->getMemberAlipay($item->member_id));
                        break;
                    default:
                        $export_data[$key + 1][] = '银行卡';
                        $export_data[$key + 1] = array_merge($export_data[$key + 1], $this->getMemberBankCard($item->member_id));
                        break;
                }
            }
        }
        $export_model->export($file_name, $export_data, \Request::query('route'));
    }



    private function getMemberAlipay($member_id)
    {
        $yzMember = MemberShopInfo::select('alipayname','alipay')->where('member_id',$member_id)->first();
        return $yzMember ? [ '', $yzMember->alipayname ?: '', $yzMember->alipay ?: '' ] : ['', ''];
    }

    private function getMemberWeChat($member_id)
    {
        $yzMember = MemberShopInfo::select('wechat')->where('member_id',$member_id)->first();
        return $yzMember ? [ $yzMember->wechat ?: '' ] : [''];
    }

    private function getMemberBankCard($member_id)
    {
        $bankCard = MemberBankCard::where('member_id',$member_id)->first();
        if ($bankCard) {
            return [
                '', '', '',
                $bankCard->bank_name ?: '',
                $bankCard->bank_province ?: '',
                $bankCard->bank_city ?: '',
                $bankCard->bank_branch ?: '',
                $bankCard->bank_card ? $bankCard->bank_card . ",": '',
                $bankCard->member_name ?: ''
            ];
        }
        return ['','','','','','','','',''];
    }



}