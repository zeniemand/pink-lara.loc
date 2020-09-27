<?php

namespace Corp\Http\Controllers;

use Corp\Repositories\MenusRepository;
use Menu;

class SiteController extends Controller
{
    //Свойство для хранения объекта класса portfolio репозиторий
    protected $p_rep;

    //Свойство для хранения объекта класса slider репозиторий
    protected $s_rep;

    //Свойство для хранения объекта класса menus репозиторий
    protected $m_rep;

    //Свойство для хранения объекта класса articles репозиторий
    protected $a_rep;

    //Свойство для хранения keywords
    protected $keywords;

    //Свойство для хранения meta описания
    protected $meta_desc;

    //Свойство для хранения заголвка страницы
    protected $title;

    //Свойство для хранения имени шаблона для отображения инфомрации на конктретной странице
    protected $template;

    //Массив передаваемых переменных в шаблон($template)
    protected $vars = array();

    //Идентификатор наличия бокового меню(sitebar)
    protected $bar = 'no';

    //Данные бокового меню (правого):
    protected $contentRightBar = false;

    //Данные бокового меню (левого):
    protected $contentLeftBar = false;

    public function __construct(MenusRepository $m_rep)
    {
        $this->m_rep = $m_rep;
    }


    /**
     * Метод получения адаптированного под проект вида(представления),
     * в зависимости от переопределяемых параметров.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    protected function renderOutput(){

        //Формируем объект навигационного меню:
        $menu = $this->getMenu();

        //Помещаем в переменную содержание шаблона навигационного меню, преобразовывая содержимое в строку(render):
        $navigation = view(env('THEME').'.navigation')->with('menu', $menu)->render();

        //Передаем переменную содержащую код представления меню навигации в массив переменных:
        $this->vars = array_add($this->vars, 'navigation', $navigation);

        //Если существует правая колонка, то отображать ее:
        if($this->contentRightBar){
            $rightBar = view(env('THEME').'.rightBar')->with('content_rightBar', $this->contentRightBar)->render();
            $this->vars = array_add($this->vars, 'rightBar', $rightBar);
        }

        //Если существует левая колонка, то отображать ее:
        if($this->contentLeftBar){
            $leftBar = view(env('THEME').'.leftBar')->with('content_leftBar', $this->contentLeftBar)->render();
            $this->vars = array_add($this->vars, 'leftBar', $leftBar);
        }

        //>Добавялем остальные параметры по умолчанию:
        $this->vars = array_add($this->vars, 'bar', $this->bar);
        $this->vars = array_add($this->vars, 'meta_desc', $this->meta_desc);
        $this->vars = array_add($this->vars, 'title', $this->title);
        $this->vars = array_add($this->vars, 'keywords', $this->keywords);
        //<

        //>Добавляем вид футера:
        $footer = view(env('THEME').'.footer')->render();
        $this->vars = array_add($this->vars, 'footer', $footer);
        //<


        return view($this->template)->with($this->vars);
    }

    /**
     * Метод формирования объекта Builder для навигационного меню.
     *
     * @return object Builder()
     */
    public function getMenu(){

        //Переменная содержащая данные меню:
        $menu = $this->m_rep->get();

        //Конструктор меню:
        $mBuilder = Menu::make('MyNav', function ($m) use ($menu){
            foreach ($menu as $item){

                //Формируем родительский эелемент меню:
                if ($item->parent == 0){
                    //Получаем данные элемента меню и назначем ему идентификатор:
                    $m->add($item->title, $item->path)->id($item->id);
                } else {
                    //формируем дочерний элемент меню:
                    if ($m->find($item->parent)){
                        $m->find($item->parent)->add($item->title, $item->path)->id($item->id);
                    }
                }
            }

        });

        return $mBuilder;
    }

}