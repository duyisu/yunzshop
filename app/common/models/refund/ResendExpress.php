<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/23
 * Time: 上午10:49
 */

namespace app\common\models\refund;

use app\common\models\BaseModel;

class ResendExpress extends BaseModel
{
    protected $fillable = [];
    protected $guarded = ['id'];
    public $table = 'yz_resend_express';

}