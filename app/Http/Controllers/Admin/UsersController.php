<?php

namespace Corp\Http\Controllers\Admin;

use Corp\User;
use Illuminate\Http\Request;

use Corp\Http\Requests;
use Corp\Http\Controllers\Controller;

use Corp\Repositories\UsersRepository;
use Corp\Repositories\RolesRepository;
use Gate;
use Corp\Http\Requests\UserRequest;

class UsersController extends AdminController
{

    protected $us_rep;
    protected $rol_rep;

    public function __construct(UsersRepository $us_rep, RolesRepository $rol_rep)
    {
        parent::__construct();

        //Проверяем права доступа к просмотру раздела пользователей:
        if(Gate::denies('VIEW_ADMIN_USERS')){
            abort(403);
        }

        $this->us_rep = $us_rep;
        $this->rol_rep = $rol_rep;

        $this->template = env('THEME'). '.admin.users';

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //получить все записи $users
        $users = $this->getUsers();

        //Определяем переменную с видом контента:
        $content = view(env('THEME').'.admin.users_content')->with('users', $users)->render();

        //добавляем ее в общий массив переменных:
        $this->vars = array_add($this->vars, 'content', $content);


        //Вызываем вид:
        return $this->renderOutput();

    }

    public function getUsers()
    {
        return $this->us_rep->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Gate::denies('save', new User())){
            abort(403);
        }

        $this->title = 'Добавить пользователя';

        //Получаем покллекцию записей ролей:
        $roles = $this->rol_rep->get();

        //dd($roles);
        $list = array();

        foreach($roles as $role){
            $list[$role->id] = $role->name;
        }

        //dd($list);

        //Собираем массив ролей для выпадающего списка:
        $roles = $roles->reduce(function ($list, $role){
            $list[$role->id] = $role->name;
            return $list;
        });

        //dd($roles);

        $content = view(env('THEME') . '.admin.users_create_content')->with('roles', $roles)->render();

        $this->vars = array_add($this->vars, 'content', $content);

        return $this->renderOutput();
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        //dd($request);

        //Получаем результат сохранения данных пользователя из поступившего запроса:
        $result = $this->us_rep->addUser($request);

        //dd($request);

        //Если сохранение произошло с ошибками, возвращаемся назад с соответсвующими сообщениями:
        if(is_array($result) && !empty($result['error'])){
            return back()->with($result);
        }

        //Если сохранение прошло успешно, то перенаправляем пользователя на главную страницу с сообщением.
        return redirect('/admin')->with($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //Проверка прав на правку пользователей:
        if(Gate::denies('EDIT_USERS')){
            abort(403);
        }

        $this->title = 'Редактирование пользователя: ' . $user->name;

        //Получаем перечень ролей:
        $roles = $this->rol_rep->get();

        //dd($roles);

        //Трансформируем перечень ролей в массив для вывода в выпадающем списке:
        $roles = $roles->reduce(function ($returnRoles, $role){
            $returnRoles[$role->id] = $role->name;
            return $returnRoles;

        });

        //dd($roles);

        //Формируем переменную content вида, для передачи в обший шаблон:
        $this->content = view(env('THEME').'.admin.users_create_content')
            ->with(['roles' => $roles, 'user' => $user])->render();

        //Передаем переменную вида content в общий шаблон:
        $this->vars = array_add($this->vars, 'content', $this->content);

        return $this->renderOutput();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        //Обновляем данные пользователя:
        $result = $this->us_rep->updateUser($request, $user);

        //Проверяем поступившие данные в результате рекдактирования:
        if(is_array($result) && !empty($result['error'])){
            return back()->with($result);
        }

        return redirect('/admin')->with($result);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
        $result = $this->us_rep->deleteUser($user);

        if(is_array($result) && !empty($result['error'])){
            return back()->with($result);
        }

        return redirect('/admin')->with($result);
    }
}
