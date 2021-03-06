<?php

namespace App\Models;

use App\Models\Traits\ActiveUserHelper;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable {
        notify as protected laravelNotify;
    }
    use HasRoles;
    use ActiveUserHelper;
    use Traits\LastActivedAtHelper;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name' , 'phone' , 'email' , 'password' , 'introduction' , 'avatar' ,
        'weixin_openid' , 'weixin_unionid'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password' , 'remember_token' ,
    ];

    public function setPasswordAttribute ( $value )
    {
        // 如果值的长度等于 60，即认为是已经做过加密的情况
        if ( strlen ( $value ) != 60 ) {

            // 不等于 60，做密码加密处理
            $value = bcrypt ( $value );
        }

        $this->attributes[ 'password' ] = $value;
    }

    public function setAvatarAttribute ( $path )
    {
        // 如果不是 `http` 子串开头，那就是从后台上传的，需要补全 URL
        if ( !starts_with ( $path , 'http' ) ) {

            // 拼接完整的 URL
            $path = config ( 'app.url' ) . "/uploads/images/avatars/$path";
        }

        $this->attributes[ 'avatar' ] = $path;
    }

    public function topics ()
    {
        return $this->hasMany ( Topic::class );
    }

    public function isAuthorOf ( $model )
    {
        return $this->id == $model->user_id;
    }

    public function replies ()
    {
        return $this->hasMany ( Reply::class );
    }

    public function notify ( $instance )
    {
        // 如果要通知的人是当前用户，就不必通知了！
        if ( $this->id == Auth::id () ) {
            return;
        }
        $this->increment ( 'notification_count' );
        $this->laravelNotify ( $instance );
    }

    public function markAsRead ()
    {
        $this->notification_count = 0;
        $this->save ();
        $this->unreadNotifications->markAsRead ();
    }

    public function syncUserActivedAt ()
    {
        // 获取昨天的日期，格式如：2017-10-21
        $yesterday_date = Carbon::yesterday ()->toDateString ();

        // Redis 哈希表的命名，如：larabbs_last_actived_at_2017-10-21
        $hash = $this->hash_prefix . $yesterday_date;

        // 从 Redis 中获取所有哈希表里的数据
        $dates = Redis::hGetAll ( $hash );

        // 遍历，并同步到数据库中
        foreach ( $dates as $user_id => $actived_at ) {
            // 会将 `user_1` 转换为 1
            $user_id = str_replace ( $this->field_prefix , '' , $user_id );

            // 只有当用户存在时才更新到数据库中
            if ( $user = $this->find ( $user_id ) ) {
                $user->last_actived_at = $actived_at;
                $user->save ();
            }
        }

        // 以数据库为中心的存储，既已同步，即可删除
        Redis::del ( $hash );
    }

    // Rest omitted for brevity

    public function getJWTIdentifier ()
    {
        return $this->getKey ();
    }

    public function getJWTCustomClaims ()
    {
        return [];
    }
}
