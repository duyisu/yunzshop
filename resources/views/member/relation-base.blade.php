@extends('layouts.base')
@section('title', '会员关系基础设置')
@section('content')
    <section class="content">

        <form id="setform" action="" method="post" class="form-horizontal form">
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    {{trans('基础设置')}}
                </div>
                <div class='panel-body'>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">Banner</label>
                        <div class="col-sm-9 col-xs-12">
                            {!! app\common\helpers\ImageHelper::tplFormFieldImage('base[banner]', $banner)!!}
                            <span class='help-block'>长方型图片</span>
                        </div>
                    </div>

                    <div class="form-group" style="display: none;">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">内容</label>
                        <div class="col-sm-9 col-xs-12">
                            {!! tpl_ueditor('base[content]', $content) !!}

                        </div>
                    </div>

                </div>

                <div class='panel-heading'>
                    {{trans('通知设置')}}
                </div>
                <div class='panel-body'>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">任务处理通知</label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="text" name="base[template_id]" class="form-control" value="{{$base['template_id']}}" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">获得推广权限通知</label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="text"  name="base[generalize_title]" class="form-control" value="{{$base['generalize_title']}}" ></input>
                            标题: 默认'获得推广权限通知'
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <textarea  name="base[generalize_msg]" class="form-control" >{{$base['generalize_msg']}}</textarea>
                            模板变量: [昵称] [时间]
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">新增下线通知</label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="text"  name="base[agent_title]" class="form-control" value="{{$base['agent_title']}}" ></input>
                            标题: 默认'新增下线通知'
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <textarea  name="base[agent_msg]" class="form-control" >{{$base['agent_msg']}}</textarea>
                            模板变量: [昵称] [时间] [下级昵称]
                        </div>
                    </div>

                    <div class="form-group"></div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9">
                            <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1"
                                   onclick='return formcheck()'/>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </section>@endsection