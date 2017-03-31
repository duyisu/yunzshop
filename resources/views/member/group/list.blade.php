@extends('layouts.base')

@section('content')

    <div class="rightlist">

        <!-- 新增加右侧顶部三级菜单 -->
        <div class="right-titpos">
            <ul class="add-snav">
                <li class="active"><a href="#">会员管理</a></li>
                <li><a href="#">会员分组</a></li>
            </ul>
        </div>
        <!-- 新增加右侧顶部三级菜单结束 -->
        <div class='panel panel-default'>
            <div class='panel-body'>
                <table class="table">
                    <thead>
                        <tr>
                            <th>分组名称</th>
                            <th>会员数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>无分组</td>
                            <td>会员数</td>
                            <td><a class='btn btn-default' href=""><i class='fa fa-users'></i></a></td>
                        </tr>
                        @foreach($groupList as $list)
                        <tr>
                            <td>{{ $list->group_name }}</td>
                            <td>
                                <?php echo count($group['member']); ?>

                            </td>
                            <td>
                                <a class='btn btn-default' href="需要跳转会员列表页面">
                                    <i class='fa fa-users'></i></a>
                                <a class='btn btn-default' href="{{ yzWebUrl('member.member-group.update', array('group_id' => $list->id)) }}">
                                    <i class='fa fa-edit'></i></a>
                                <a class='btn btn-default' href="{{ yzWebUrl('member.member-group.destroy', array('group_id' => $list->id)) }}" onclick="return confirm('确认删除此会员分组吗？');return false;">
                                    <i class='fa fa-remove'></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {!! $pager !!}
            </div>
            <div class='panel-footer'>
                <a class='btn btn-primary' href="{{ yzWebUrl('member.member-group.store') }}"><i class="fa fa-plus"></i>
                    添加新分组</a>
            </div>
        </div>
    </div>


@endsection