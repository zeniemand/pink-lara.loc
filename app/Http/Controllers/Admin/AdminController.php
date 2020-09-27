<?php

namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Corp\Http\Requests;
use Corp\Http\Controllers\Controller;
use Auth;
use Menu;
use Gate;


class AdminController extends \Corp\Http\Controllers\Controller
{
    protected $user;
    protected $p_rep;
    protected $a_rep;
    protected $template;
    protected $content = false;
    protected $title;
    protected $vars;

    public function __construct()
    {
        $this->user = Auth::user();
        if(!$this->user){
            abort(403);
        }
    }

    public function renderOutput() {
        $this->vars = array_add($this->vars,'title',$this->title);

        $menu = $this->getMenu();

        $navigation = view(env('THEME').'.admin.navigation')->with('menu',$menu)->render();
        $this->vars = array_add($this->vars,'navigation',$navigation);

        if($this->content) {
            $this->vars = array_add($this->vars,'content',$this->content);
        }

        $footer = view(env('THEME').'.admin.footer')->render();
        $this->vars = array_add($this->vars,'footer',$footer);

        return view($this->template)->with($this->vars);




    }

    public function getMenu() {
        return Menu::make('adminMenu', function($menu) {

            if(Gate::allows('VIEW_ADMIN_ARTICLES')){
                $menu->add('Статьи',array('route' => 'admin.articles.index'));
            }

            if(Gate::allows('VIEW_ADMIN_PORTFOLIOS')){
                $menu->add('Портфолио',  array('route'  => 'admin.portfolios.index'));
            }

            if(Gate::allows('VIEW_ADMIN_MENU')){
                $menu->add('Меню',  array('route'  => 'admin.menus.index'));
            }

            if(Gate::allows('VIEW_ADMIN_USERS')){
                $menu->add('Пользователи',  array('route'  => 'admin.users.index'));
            }

            if(Gate::allows('EDIT_USERS')){
                $menu->add('Привилегии',  array('route'  => 'admin.permissions.index'));
            }

            $menu->add('Выйти из админки',  'logout');

        });
    }

}
