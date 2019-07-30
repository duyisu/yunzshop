<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 02/03/2017
 * Time: 18:19
 */

namespace app\common\models\user;


use app\backend\modules\user\observers\UserObserver;
use app\common\models\BaseModel;
use Illuminate\Validation\Rule;

class User extends BaseModel
{
    public $primaryKey = 'uid';

    public $table = 'users';

    public $timestamps = false;

    public $widgets = [];

    public $attributes = [
        'groupid' => 0,
        'type'    => 1,
        'remark'  => '',
        'endtime' => 0
    ];

    protected $guarded = [''];

    /**
     * User constructor.
     * @param array $attributes
     * @throws \Exception
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (env('APP_Framework') == 'platform') {
            unset($this->attributes['groupid']);
            $this->timestamps = true;
            $this->table = 'yz_admin_users';
        } else {
            $this->attributes = $this->getNewAttributes();
        }
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getNewAttributes()
    {
        if ($this->hasColumn('owner_uid')) { //用于兼容新版微擎新增的字段
            $this->attributes = array_merge($this->attributes, ['owner_uid' => '0']);
        }
        if ($this->hasColumn('founder_groupid')) {
            $this->attributes = array_merge($this->attributes, ['founder_groupid' => '0']);
        }
        if ($this->hasColumn('register_type')) {
            $this->attributes = array_merge($this->attributes, ['register_type' => '0']);
        }
        if ($this->hasColumn('openid')) {
            $this->attributes = array_merge($this->attributes, ['openid' => '']);
        }
        if ($this->hasColumn('welcome_link')) {
            $this->attributes = array_merge($this->attributes, ['welcome_link' => '0']);
        }
        if ($this->hasColumn('is_bind')) {
            $this->attributes = array_merge($this->attributes, ['is_bind' => '0']);
        }
        if ($this->hasColumn('schoolid')) {
            $this->attributes = array_merge($this->attributes, ['schoolid' => '0']);
        }
        if ($this->hasColumn('credit1')) {
            $this->attributes = array_merge($this->attributes, ['credit1' => '0']);
        }
        if ($this->hasColumn('credit2')) {
            $this->attributes = array_merge($this->attributes, ['credit2' => '0']);
        }
        if ($this->hasColumn('agentid')) {
            $this->attributes = array_merge($this->attributes, ['agentid' => '0']);
        }
        if ($this->hasColumn('uniacid')) {
            $this->attributes = array_merge($this->attributes, ['uniacid' => '0']);
        }
        if ($this->hasColumn('token')) {
            $this->attributes = array_merge($this->attributes, ['token' => '']);
        }
        if ($this->hasColumn('registration_id')) {
            $this->attributes = array_merge($this->attributes, ['registration_id' => '']);
        }


        return $this->attributes;
    }

    public function uniAccounts()
    {
        return $this->hasMany('app\common\models\user\UniAccountUser', 'uid', 'uid');
    }

    /*
     *  One to one, each operator corresponds to an operator profile
     **/
    public function userProfile()
    {
        return $this->hasOne('app\common\models\user\UserProfile', 'uid', 'uid');
    }

    /*
     *  One to one, account each operator corresponds to an operator
     **/
    public function uniAccount()
    {
        return $this->belongsTo('app\common\models\user\UniAccountUser', 'uid', 'uid');
    }

    /*
     *  One to one, one operator has only one role
     **/
    public function userRole()
    {
        return $this->hasOne('app\common\models\user\YzUserRole', 'user_id', 'uid');
    }

    /*
     *  One to many, one operator has multiple operating privileges
     **/
    public function permissions()
    {
        return $this->hasMany('app\common\models\user\YzPermission', 'item_id', 'uid')
            ->where('type', '=', YzPermission::TYPE_USER);
    }

    /**
     * 排出供应商操作员
     * @param $query
     * @return mixed
     */
    public function scopeNoOperator($query)
    {
        /*if (Schema::hasTable('yz_supplier')) {
            $ids = DB::table('yz_supplier')->select('uid')->get();

            return $query->whereNotIn('uid',$ids);
        }*/
        return $query;
    }


    /**
     * @param $query
     * @return mixed
     */
    public function scopeRecords($query)
    {
        return $query->whereHas('uniAccount', function ($query) {
            return $query->uniacid()->where('role', '!=', 'clerk');
        })
            ->with(['userProfile' => function ($profile) {
                return $profile->select('uid', 'realname', 'mobile');
            }])
            ->with(['userRole' => function ($userRole) {
                return $userRole->select('user_id', 'role_id')
                    ->with(['role' => function ($role) {
                        return $role->select('id', 'name')->uniacid();
                    }]);
            }]);
    }

    public function scopeSearch($query, array $keyword)
    {
        if ($keyword['keyword']) {
            $query = $query->whereHas('userProfile', function ($profile) use ($keyword) {
                return $profile->select('uid', 'realname', 'mobile')
                    ->where('realname', 'like', '%' . $keyword['keyword'] . '%')
                    ->orWhere('mobile', 'like', '%' . $keyword['keyword'] . '%');
            })->orWhere('username', 'like', '%' . $keyword['keyword'] . '%');
        }
        if ($keyword['status']) {
            $query = $query->where('status', $keyword['status']);
        }
        if ($keyword['role_id']) {
            $query = $query->whereHas('userRole', function ($userRole) use ($keyword) {
                return $userRole->where('role_id', $keyword['role_id']);
            });
        }
        return $query;
    }


    /*
     * Get operator information through operator ID
     *
     * @parms int $userId
     *
     * @return object
     **/
    public static function getUserById($userId)
    {
        return self::where('uid', $userId)
            ->with(['userProfile' => function ($profile) {
                return $profile->select('uid', 'realname', 'mobile');
            }])
            ->with(['userRole' => function ($userRole) {
                return $userRole->select('user_id', 'role_id')
                    ->with(['role' => function ($role) {
                        return $role->select('id', 'name')->uniacid();
                    }]);
            }])
            ->with(['permissions' => function ($userPermission) {
                return $userPermission->select('permission', 'item_id');
            }])
            ->whereHas('uniAccount', function ($query) {
                return $query->uniacid()->where('role', '!=', 'clerk');
            })
            ->first();
    }

    /*
     *  Delete operator
     **/
    public static function destroyUser($userId)
    {
        return static::where('uid', $userId)->delete();
    }

    /**
     * 数据库获取用户权限
     *
     * @return mixed
     */
    public static function getUserPermissionsCache()
    {
        $key = 'user.permissions.' . \YunShop::app()->uid;
        $list = \Cache::get($key);
        if ($list === null) {
            $list = static::select(['uid'])
                ->where(['uid' => \YunShop::app()->uid])
                //->where('type','!=', '1')
                ->with([
                    'userRole' => function ($query) {
                        return $query->select(['user_id', 'role_id'])
                            ->with(['permissions']);
                    },
                    'permissions'
                ])
                ->get();

            \Cache::put($key, $list, 3600);
        }
        return $list;
    }

    /**
     * 获取并组合用户权限
     *
     * @return array
     */
    public static function getAllPermissions()
    {
        set_time_limit(0);
        $userPermissions = self::getUserPermissionsCache()->toArray();
        //dd($userPermissions);
        $permissions = [];
        if ($userPermissions) {
            foreach ($userPermissions as $v) {
                if ($v['permissions']) {
                    foreach ($v['permissions'] as $v1) {
                        $permissions[] = $v1['permission'];
                    }
                }
                if ($v['user_role']['permissions']) {
                    foreach ($v['user_role']['permissions'] as $key => $v2) {
                        !in_array($v2['permission'], $permissions) && $permissions[] = $v2['permission'];
                    }
                }
            }
        }
        //dd($permissions);
        return $permissions;
    }

    /**
     * 定义字段名
     *
     * @return array
     */
    public function atributeNames()
    {
        return [
            'username' => "操作员用户名",
            'password' => "操作员密码"
        ];
    }

    /**
     * 字段规则
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => ['required', Rule::unique($this->table)->ignore($this->id)],
            'password' => 'required'
        ];
    }

    /**
     * 在boot()方法里注册下模型观察类
     * boot()和observe()方法都是从Model类继承来的
     * 主要是observe()来注册模型观察类，可以用TestMember::observe(new TestMemberObserve())
     * 并放在代码逻辑其他地方如路由都行，这里放在这个TestMember Model的boot()方法里自启动。
     */
    public static function boot()
    {
        parent::boot();
        //注册观察者
        static::observe(new UserObserver());
    }

}
