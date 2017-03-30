<?php
namespace  app\backend\controllers;

use app\common\components\BaseController;
use Illuminate\Support\Str;
use Setting;
use app\common\services\PluginManager;
use Datatables;
use Cookie;

class TestController extends BaseController
{

    public function index()
    {

        dd(\YunShop::app());
        dd($alipay = app('alipay.web'));

        $fans = weAccount()->fansAll();
        dd($fans);
        return view('test.index',['a'=>'f']);


    }

    public function testJson()
    {
        return $this->successJson('错误提示',$data = ['错误提示']);
    }

    public function testErrorJson()
    {
        return $this->errorJson($message = '错误提示', $data = ['错误提示']);
    }

    public function test()
    {
        return widget('app\backend\widgets\MenuWidget',['test'=>'bbbbb']);
    }

    public function view()
    {
        return view('test.index',['a'=>Str::random(10)]);
    }

    public function testSms()
    {
        $result=$this->sms->send("phone","name","content","code");
    }


    public function testPlugin()
    {
        //Illuminate\Session\Store;
        session()->put('test','jan');//设置session
        echo session('test','default');//获取session 后面的参数为默认值
        session()->forget('test');//注销session
        echo session('test','default');
        echo "<br />";
        //Illuminate\Contracts\Cookie;
        //设置cookie
         Cookie::queue('test', 'can you read me?', 99999999);
        echo  Cookie::queued('test','b');
        //注销cookie
        Cookie::unqueue('test');
        echo  Cookie::queued('test','a');
    }

    public function pluginData(PluginManager $plugins)
    {
        $installed = $plugins->getPlugins();

        return Datatables::of($installed)
            ->setRowId('plugin-{{ $name }}')
            ->editColumn('title', function ($plugin) {
                return trans($plugin->title);
            })
            ->editColumn('description', function ($plugin) {
                return trans($plugin->description);
            })
            ->editColumn('author', function ($plugin) {
                return "<a href='{$plugin->url}' target='_blank'>".trans($plugin->author)."</a>";
            })
            ->addColumn('status', function ($plugin) {
                return trans('admin.plugins.status.'.($plugin->isEnabled() ? 'enabled' : 'disabled'));
            })
            ->addColumn('operations', function ($plugin) {
                return view('vendor.admin-operations.plugins.operations', compact('plugin'));
            })
            ->make(true);
    }

    public function testSetting()
    {
        $value = Setting::set('config.test','default value');
        $value = Setting::set('config.test.t','default value t');
        $value = Setting::set('config.test.f','default value f');
        dd($value);


    }

    public function testOld()
    {
        $hm = Setting::get('shop.app');
        print_r($hm);
    }


}