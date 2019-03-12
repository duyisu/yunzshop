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

    //用户管理
    Route::group(['namespace' => 'platform\modules\user\controllers'], function () {
        //权限管理路由
        Route::get('permission/{parentId}/create', ['as' => 'admin.permission.create', 'uses' => 'PermissionController@create']);
        Route::post('permission/{parentId}/create', ['as' => 'admin.permission.create', 'uses' => 'PermissionController@store']);
        Route::get('permission/{id}/edit', ['as' => 'admin.permission.edit', 'uses' => 'PermissionController@edit']);
        Route::post('permission/{id}/edit', ['as' => 'admin.permission.edit', 'uses' => 'PermissionController@update']);
        Route::get('permission/{id}/delete', ['as' => 'admin.permission.destroy', 'uses' => 'PermissionController@destroy']);
        Route::get('permission/{parentId}/index', ['as' => 'admin.permission.index', 'uses' => 'PermissionController@index']);
        Route::get('permission/index', ['as' => 'admin.permission.index', 'uses' => 'PermissionController@index']);
        Route::post('permission/index', ['as' => 'admin.permission.index', 'uses' => 'PermissionController@index']);

        //角色管理路由
        Route::get('role/index', ['as' => 'admin.role.index', 'uses' => 'RoleController@index']);
        Route::post('role/index', ['as' => 'admin.role.index', 'uses' => 'RoleController@index']);
        Route::get('role/create', ['as' => 'admin.role.create', 'uses' => 'RoleController@create']);
        Route::post('role/create', ['as' => 'admin.role.create', 'uses' => 'RoleController@store']);
        Route::get('role/{id}/edit', ['as' => 'admin.role.edit', 'uses' => 'RoleController@edit']);
        Route::post('role/{id}/edit', ['as' => 'admin.role.edit', 'uses' => 'RoleController@update']);
        Route::get('role/{id}/delete', ['as' => 'admin.role.destroy', 'uses' => 'RoleController@destroy']);
    });

    // 站点管理
    Route::group(['prefix' => 'system', 'namespace' => 'platform\modules\system\controllers'], function (){
        // 站点设置
        Route::any('site', 'SiteController@index');
        // 附件设置-全局设置
        Route::any('attachment', 'AttachmentController@index');
        // 系统升级
        Route::any('update/index', 'UpdateController@index');
        // 检查更新
        Route::get('update/verifyCheck', 'UpdateController@verifyCheck');
        // 更新
        Route::any('update/fileDownload', 'UpdateController@fileDownload');
        // 版权
        Route::any('update/pirate', 'UpdateController@pirate');
        // 初始程序
        Route::any('update/startDownload', 'UpdateController@startDownload');
        /* 上传 */
        // 图片
        Route::any('upload/image', 'UploadController@image');
    });

    // 用户管理
    Route::group(['prefix' => 'user', 'namespace' => 'platform\modules\user\controllers'], function (){
        // 用户列表
        Route::get('index', 'UserController@index');
        // 添加用户
        Route::any('create', 'UserController@create');
        // 用户编辑
        Route::any('edit', 'UserController@edit');
        // 用户修改状态
        Route::any('status', 'UserController@status');
        // 用户修改密码
        Route::any('change', 'UserController@change');
    });

    Route::group(['namespace' => 'platform\modules\application\controllers'], function () {
		// 平台管理
		Route::get('application/', 'ApplicationController@index');
		//修改应用
		Route::post('application/update/{id}', 'ApplicationController@update');
		//启用禁用或恢复应用及跳转链接
		Route::get('application/switchStatus/{id}', 'ApplicationController@switchStatus');
		//添加应用
		Route::post('application/add/', 'ApplicationController@add');
		//删除 加入回收站
		Route::delete('application/{id}', 'ApplicationController@delete');
		//回收站
		Route::get('application/recycle/', 'ApplicationController@recycle');
		//图片上传
		Route::post('application/upload/', 'ApplicationController@upload');
		Route::get('application/temp/', 'ApplicationController@temp');

		//平台用户管理
		Route::get('appuser/', 'AppuserController@index');
		Route::post('appuser/{id}', 'AppuserController@update');
		Route::delete('appuser/{id}', 'AppuserController@delete');
	});
});

Route::get('/', function () {
    return redirect('/admin/index');
});