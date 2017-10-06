
    <!-- sidebar: style can be found in sidebar.less -->
    <section  class="sidebar" data-active-color="blue" data-background-color="black" data-image="../assets/img/sidebar-1.jpg">


        <div class="sidebar-wrapper">

            <ul class="nav">

                @foreach(config(config('app.menu_key','menu')) as $key=>$value)
                    @if(isset($value['menu']) && $value['menu'] == 1 && can($key))
                        @if(isset($value['child']) && array_child_kv_exists($value['child'],'menu',1))
                            <li class="{{in_array($key,Yunshop::$currentItems) ? 'active' : ''}}">
                                <a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] or ''}}">
                                    <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                    {{--<span class="pull-right-container">--}}
                                        {{--<i class="fa fa-angle-left pull-right"></i>--}}
                                    {{--</span>--}}
                                    <p>{{$value['name']}}</p>
                                </a>
                                {{--@include('layouts.childMenu',['childs'=>$value['child'],'item'=>$key])--}}
                            </li>
                        @elseif($value['menu'] == 1)
                            <li class="{{in_array($key,Yunshop::$currentItems) ? 'active' : ''}}">
                                <a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] or ''}}">
                                    <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                    <p>{{$value['name'] or ''}}</p>
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
            {{--菜单结束--}}
        </div>
        <!-- Sidebar Menu -->

        <!-- /.sidebar-menu -->
    </section>












