<?php

namespace Corp\Http\Requests;

use Corp\Http\Requests\Request;

use Auth;



class UserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //Провереям права на добавление пользователей:
        return Auth::user()->canDo('ADD_USERS');
    }

    protected function getValidatorInstance()
    {
        //Получаем объект валидатора по умолчанию:
        $validator =  parent::getValidatorInstance();

        //Правило проверки параоля, если он вводится, т.к. при редактировании может и не вводится новый:
        $validator->sometimes('password', 'required|min:6|confirmed', function ($input){
            //истинна, если пароль ввели, либо если не ввели, но маршрут у нас - не редактирование:
            if(!empty($input->password) || (empty($input->password) && $this->route()->getName() !== 'admin.users.update')){
                return true;
            }

            return false;

        });

        return $validator;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        //Если пользователь будет редактироваться, то берем еще параметр идентификатора, для уникальности мейла
        $id = (isset($this->route()->parameter('users')->id)) ? $this->route()->parameter('users')->id : '';
        return [
            'name' => 'required|max:255',
            'login' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,'. $id,
            'role_id' => 'required|integer',
        ];
    }
}
