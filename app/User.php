<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身ではないかの確認
        $its_me = $this->id == $userId;
    
        if ($exist || $its_me) {
            // 既にフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身ではないかの確認
        $its_me = $this->id == $userId;
    
        if ($exist && !$its_me) {
            // 既にフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }
    
    public function yes_favorites($micropostsId)
    {
        // 既にお気に入りしているかの確認
        $exist = $this->is_favorites($micropostsId);
        // 投稿が自分自身ではないかの確認
        // マイクロポストのユーザーを取り出す
        $micropost = Micropost::find($micropostsId);
        $its_me = $this->id == $micropost->user_id;
    
        if ($exist || $its_me) {
            // 既にお気に入りしていれば何もしない
            return false;
        } else {
            // 未お気に入りであれば追加する
            $this->favorites()->attach($micropostsId);
            return true;
        }
    }
    
    public function no_favorites($micropostsId)
    {
        // 既にお気に入りしているかの確認
        $exist = $this->is_favorites($micropostsId);
        // 投稿が自分自身ではないかの確認
        // マイクロポストのユーザーを取り出す
        $micropost = Micropost::find($micropostsId);
        $its_me = $this->id == $micropost->user_id;
    
        if ($exist && !$its_me) {
            // 既にお気に入りしていればお気に入りを外す
            $this->favorites()->detach($micropostsId);
            return true;
        } else {
            // 未お気に入りであれば何もしない
            return false;
        }
    }
    
    public function is_following($userId)
    {
        // 既存のフォローと照合し、True、Falseを返す
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function is_favorites($micropostsId)
    {
        // 既存のフォローと照合し、True、Falseを返す
        return $this->favorites()->where('micropost_id', $micropostsId)->exists();
    }
    
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()->pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
}
