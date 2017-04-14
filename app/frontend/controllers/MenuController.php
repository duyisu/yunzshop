<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 22/03/2017
 * Time: 14:25
 */

namespace app\frontend\controllers;


use app\common\components\BaseController;
use app\common\models\Menu;

class MenuController extends BaseController
{
    public function toList()
    {
        return $this->successJson('',Menu::get());
    }
}