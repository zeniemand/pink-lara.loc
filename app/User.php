<?php

namespace Corp;

use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'login'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];



    /**
     * Метод связывания один к многим с моделью Article
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany('Corp\Article');
    }

    public function roles()
    {
        return $this->belongsToMany('Corp\Role', 'role_user');
    }


    /**
     * Проверка на наличие прав доступа(привилегий)
     *
     * @param  string/array $permission
     * @param bool $require - true если надо, чтобы все передаваемые в массиве первого аргумента права были у пользователя.
     * @return bool
     */
    public function canDo($permission, $require = false)
    {
        if(is_array($permission)){
            foreach ($permission as $permName){
                $permName =  $this->canDo($permName);
                //Определение отдельных прав доступа(просеиваем их):
                //$require = false и находим право доступа, то вернем тру
                if($permName && !$require){
                    return true;
                }
                //Если вообще ни одного соответсввия прав:
                else if (!$permName && $require){
                    return false;
                }
            }

            //Если все права доступа есть, то вернем:
            return $require;
        } else {
            foreach ($this->roles as $role){
                foreach ($role->perms as $perm){
                    //Если попавшее разрешение в переменную соовтесвтует переданному в исходную функцию,
                    //то все ок, разрешение есть.
                    if(str_is($permission, $perm->name)){
                        return true;
                    }
                }
            }
        }
    }

    //Проверка привязанности пользователя к роли:
    public function hasRole($name, $require = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && !$require) {
                    return true;
                } elseif (!$hasRole && $require) {
                    return false;
                }
            }
            return $require;
        } else {
            foreach ($this->roles as $role) {
                if ($role->name == $name) {
                    return true;
                }
            }
        }

        return false;
    }
}
