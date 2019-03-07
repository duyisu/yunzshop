<?php
Route::group(['namespace' => 'platform\controllers'], function () {
    Route::get('login', 'LoginController@showLoginForm')->name('admin.login');
    Route::post('login', 'LoginController@login');
    Route::get('logout', 'LoginController@logout');
    Route::post('logout', 'LoginController@logout');

    Route::get('register', 'RegisterController@showRegistrationForm')->name('admin.register');
    Route::post('register', 'RegisterController@register');

    Route::get('/', 'IndexController@index');
});

Route::group(['middleware' => ['auth:admin', 'authAdmin', 'globalparams']], function () {

    Route::get('index', ['as' => 'admin.index', 'uses' => '\app\platform\controllers\IndexController@index']);

    // 站点管理
    Route::group(['prefix' => 'system'], function (){
        // 站点设置
        Route::any('site', ['as' => 'admin.system.site', 'uses' => '\app\platform\modules\system\controllers\SiteController@index']);
        // 附件设置-全局设置
        Route::any('attachment', ['as' => 'admin.system.attachment', 'uses' => '\app\platform\modules\system\controllers\AttachmentController@index']);
        // 系统升级
        Route::any('update/index', ['as' => 'admin.system.update.index', 'uses' => '\app\platform\modules\system\controllers\UpdateController@index']);
        // 检查更新
        Route::get('update/verifyCheck', ['as' => 'admin.system.update.verifyCheck', 'uses' => '\app\platform\modules\system\controllers\UpdateController@verifyCheck']);
        // 更新
        Route::any('update/fileDownload', ['as' => 'admin.system.update.fileDownload', 'uses' => '\app\platform\modules\system\controllers\UpdateController@fileDownload']);
        // 版权
        Route::any('update/pirate', ['as' => 'admin.system.update.pirate', 'uses' => '\app\platform\modules\system\controllers\UpdateController@pirate']);
        // 初始程序
        Route::any('update/startDownload', ['as' => 'admin.system.update.startDownload', 'uses' => '\app\platform\modules\system\controllers\UpdateController@startDownload']);
        /* 上传 */
        // 图片
        Route::any('upload/image', ['as' => 'admin.system.upload.image', 'uses' => '\app\platform\modules\system\controllers\UploadController@image']);
    });

    Route::group(['namespace' => 'platform\modules\application\controllers'], function () {
		// 平台管理
		Route::get('application/', 'ApplicationController@index');
		//修改应用
		Route::post('application/{id}', 'ApplicationController@update');
		//启用禁用或恢复应用及跳转链接
		Route::get('application/switchStatus/{id}', 'ApplicationController@switchStatus');
		//添加应用
		Route::post('application/', 'ApplicationController@add');
		//删除 加入回收站
		Route::delete('application/{id}', 'ApplicationController@delete');
		//回收站
		Route::get('application/recycle/', 'ApplicationController@recycle');

		//平台用户管理
		// Route::get('appuser/', 'AppuserController@index');
		Route::post('appuser/{id}', 'AppuserController@update');
		Route::get('appuser/', 'AppuserController@add');
		Route::get('appuser/{id}', 'AppuserController@delete');
	});
});

Route::get('/', function () {
    return redirect('/admin/index');
});