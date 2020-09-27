<?php

namespace Corp;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    //
    protected $fillable = [
        'title', 'path', 'parent'
    ];

    public function delete(array $options = []){

        //обратимся к классу Menu и выберем все элементы с parent(т.е. дочерние)  у модели удаляемой ссылки (т.е. у родителя):
        if($child = self::where('parent', $this->id)){
            //Удаляем "детей"
            $child->delete();
        }

        return parent::delete($options);

    }
}
