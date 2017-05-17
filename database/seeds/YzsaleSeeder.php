<?php

/**
 * Created by PhpStorm.
 * User: yanglei
 * Date: 2017/3/9
 * Time: 上午9:54
 */
use Illuminate\Database\Seeder;
class YzSaleSeeder extends Seeder
{
    protected $oldTable = 'sz_yi_goods';
    protected $newTable = 'yz_goods_sale';
    
    public function run()
    {
        $newList = DB::table($this->newTable)->get();
        if($newList->isNotEmpty()){
            echo "yz_goods_sale 已经有数据了跳过\n";
            return ;
        }
        $list =  DB::table($this->oldTable)->get();
        if($list) {
            foreach ($list as $v) {
                DB::table($this->newTable)->insert([
                    'goods_id'=> $v['id'],
                    'max_point_deduct'=> $v['deduct'] * 100,
                    'max_balance_deduct'=> $v['deduct2'] * 100,
                    'is_sendfree'=> $v['issendfree'],
                    'ed_num'=> $v['ednum'],
                    'ed_money'=> $v['edmoney'],
                    'ed_areas'=> $v['edareas'],
                    'point'=> $v['Credit'] * 100,
                    'bonus'=> $v['redprice'] * 100
                ]);

            }
        }
    }

}