<?php


namespace Corp\Repositories;

use Corp\Permission;

use Gate;

use Corp\Repositories\RolesRepository;

class PermissionsRepository extends Repository
{
    protected $rol_rep;

    public function __construct(Permission $permission, RolesRepository $rol_rep)
    {
        $this->model = $permission;
        $this->rol_rep = $rol_rep;
    }

    public function changePermissions($request){

        if(Gate::denies('change', $this->model)){
            abort(403);
        }

        $data = $request->except('_token');

        $roles = $this->rol_rep->get();

        //dd($roles);
        //dd($data);

        foreach ($roles as $role){
            //Если на текущей итерации есть совпадение с ролью в массиве, то записываем ее:
            if(isset($data[$role->id])){
                //dd($data[$role->id]);
                $role->savePermissions($data[$role->id]);
            } else {
                $role->savePermissions([]);
            }
        }

        return ['status' => 'Права обновлены'];

    }

}