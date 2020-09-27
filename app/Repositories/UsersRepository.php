<?php
namespace Corp\Repositories;

use Gate;
use Config;
use Corp\User;


class UsersRepository extends Repository
{
    public function __construct(User $user)
    {
     $this->model = $user;
    }

    public function addUser($request)
    {
        //Проверяем права на сохранение пользователей:
        if(Gate::denies('save', $this->model)){
            abort(403);
        }

        //Вибираем нужные нам для сохранения в БД данные из запроса:
        $data = $request->except('_token');

        //dd($data);

        //Првоереяем не пустые ли данные:
        if(empty($data)){
            return array('error' => 'Нет данных');
        }

        //Сохраняем данные в БД:
        $user = $this->model->create([
            'name' => $data['name'],
            'login' => $data['login'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        //Если данные успешно сохранились, делаем привязку ролей юрера:
        if($user){
            $user->roles()->attach($data['role_id']);
        }

        return ['status' => 'Пользователь добавлен'];

    }

    public function updateUser($request, $user)
    {
        //Проверка прав на редактирование:
        if(Gate::denies('edit', $this->model)){
            abort(403);
        }

        //Собираем в массив данные из запроса:
        $data = $request->all();

        //dd($data);

        if(empty($data)){
            return array('error' => 'Нет данных');
        }

        //Если обновился пароль, то хешируем его:
        if($data['password']){
            $data['password'] = bcrypt($data['password']);
        }

        //Если пароль не трогали - не меняли, то поле пустое и удаляем его:
        if(empty($data['password'])){
            //dd('пуст');
            unset($data['password']);
        }

        //Обновялем данные в модели, и обноляем связанную роль:
        if($user->fill($data)->update()){
            $user->roles()->sync([$data['role_id']]);
        }

        //return ['status' => ['Пользователь ' . $user->name . ' отредактирован']];
        return ['status' => 'Пользователь '. $user->name .'  отредактирован'];
    }

    public function deleteUser($user)
    {
        //Проверка прав на удаление:
        if(Gate::denies('destroy', $this->model)){
            abort(403);
        }

        //Отвязваем связанную модель:
        $user->roles()->detach();

        if($user->delete()){
         return ['status' => 'Пользователь удален'];
        }
    }


}