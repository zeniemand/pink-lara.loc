<?php


namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Corp\Http\Requests;
use Corp\Http\Controllers\Controller;

use Corp\Repositories\MenusRepository;
use Corp\Repositories\ArticlesRepository;
use Corp\Repositories\PortfoliosRepository;

use Corp\Http\Requests\MenusRequest;

use Gate;
use Menu;

use Corp\Category;
use Corp\Filter;


class MenusController extends AdminController
{

    protected $m_rep;


    public function __construct(MenusRepository $m_rep, ArticlesRepository $a_rep, PortfoliosRepository $p_rep)
    {
        parent::__construct();

        if(Gate::denies('VIEW_ADMIN_MENU')) {
            abort(403);
        }

        $this->m_rep = $m_rep;
        $this->a_rep = $a_rep;
        $this->p_rep = $p_rep;

        $this->template = env('THEME').'.admin.menus';

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $menu = $this->getMenus();

        $this->content = view(env('THEME').'.admin.menus_content')->with('menus',$menu)->render();

        return $this->renderOutput();
    }

    public function getMenus()
    {
        //

        $menu = $this->m_rep->get();

        if($menu->isEmpty()) {
            return FALSE;
        }

        return Menu::make('forMenuPart', function($m) use($menu) {

            //Формируем иерархический вывод меню:
            foreach($menu as $item) {
                if($item->parent == 0) {
                    $m->add($item->title,$item->path)->id($item->id);
                }

                else {
                    //Ищем родительский пункт меню:
                    if($m->find($item->parent)) {
                        //Добавляем дочерний к родительскому и присваиваем id:
                        $m->find($item->parent)->add($item->title,$item->path)->id($item->id);
                    }
                }
            }

        });


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $this->title = 'Новый пункт меню';

        //> подготавливаем переменные для использования в Grop-Down-Lists фасада Html

        //Получаем родительские пункты меню:
        $tmp = $this->getMenus()->roots();

        $menus = $tmp->reduce(function ($returnMenus, $menu){

            $returnMenus[$menu->id] = $menu->title;
            return $returnMenus;
        }, ['0' => 'Родительский пукнт меню']);

        //dd($menus);

        $categories = Category::select(['title', 'alias', 'parent_id', 'id'])->get();

        //dd($categories);


        //Резервируем массив для иерархии категорий.
        $list = array();
        $list = array_add($list, '0', 'Не используется');
        $list = array_add($list, 'parent', 'Раздел блог');

        //Наполняем массив с родительскими и дочерними категориями меню:
        foreach ($categories as $category){
            if($category->parent_id == 0){
                $list[$category->title] = array();
            } else {
                $list[$categories->where('id', $category->parent_id)->first()->title][$category->alias] = $category->title;
            }
        }

        //dd($list);

        $articles = $this->a_rep->get(['id', 'title', 'alias']);

        //dd($articles);

        $articles = $articles->reduce(function ($returnArticles, $article){
            $returnArticles[$article->alias] = $article->title;
            return $returnArticles;
        },[]);

        //dd($articles);

        $filtres = Filter::select(['id', 'title', 'alias'])->get();

        $filtres = $filtres->reduce(function ($returnFilters, $filter){
            $returnFilters[$filter->alias] = $filter->title;
            return $returnFilters;
        },['parent' => 'Раздел портфолио']);

        $portfolios = $this->p_rep->get(['id', 'title', 'alias']);
        $portfolios =  $portfolios->reduce(function ($returnPortfolios, $portfolio){
            $returnPortfolios[$portfolio->alias] = $portfolio->title;
            return $returnPortfolios;
        },[]);

        //<

        $this->content = view(env('THEME').'.admin.menus_create_content')->with(['menus' => $menus, 'categories' => $list, 'articles' => $articles, 'filters' => $filtres, 'portfolios' => $portfolios])->render();

        return $this->renderOutput();

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MenusRequest $request)
    {
        //
        $result = $this->m_rep->addMenu($request);

        if(is_array($result) && !empty($result['error'])){
            return back()->with($result);
        }

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
     * @param  obj  $menu
     * @return \Illuminate\Http\Response
     */
    public function edit(\Corp\Menu $menu)
    {
        //
        //dd($menu);

        $this->title = 'Редактирование ссылки - ' . $menu->title;

        //Тип пункта меню котрый редактируем:
        $type = false;

        //option выпадающего списка:
        $option = false;
        //dd(app('request'));

        //Получаем маршрут:
        $route = app('router')->getRoutes()->match(app('request')->create($menu->path));

        //Маршрут пути редактируемой ссылки:
        $aliasRoute = $route->getName();

        //Параметры для текущей ссылки:
        $parameters = $route->parameters();

        //dump($aliasRoute);
        //dump($parameters);

        //Реакции значений переменных на текущие пути:
        if($aliasRoute == 'articles.index' || $aliasRoute == 'articlesCat'){
            $type = 'blogLink';
            $option = isset($parameters['cat_alias']) ? $parameters['cat_alias'] : 'parent';
        } else if($aliasRoute == 'articles.show'){
            $type = 'blogLink';
            $option = isset($parameters['alias']) ? $parameters['alias'] : '';
        } else if($aliasRoute == 'portfolios.index'){
            $type = 'portfolioLink';
            $option = 'parent';
        } else if($aliasRoute == 'portfolios.show'){
            $type = 'portfolioLink';
            $option =  isset($parameters['alias']) ? $parameters['alias'] : '';
        } else {
            //Пользовательская ссылка:
            $type = 'customLink'
;        }

        //dd($type);


        //> подготавливаем переменные для использования в Grop-Down-Lists фасада Html

        //Получаем родительские пункты меню:
        $tmp = $this->getMenus()->roots();

        $menus = $tmp->reduce(function ($returnMenus, $menu){

            $returnMenus[$menu->id] = $menu->title;
            return $returnMenus;
        }, ['0' => 'Родительский пукнт меню']);

        //dd($menus);

        $categories = Category::select(['title', 'alias', 'parent_id', 'id'])->get();

        //dd($categories);


        //Резервируем массив для иерархии категорий.
        $list = array();
        $list = array_add($list, '0', 'Не используется');
        $list = array_add($list, 'parent', 'Раздел блог');

        //Наполняем массив с родительскими и дочерними категориями меню:
        foreach ($categories as $category){
            if($category->parent_id == 0){
                $list[$category->title] = array();
            } else {
                $list[$categories->where('id', $category->parent_id)->first()->title][$category->alias] = $category->title;
            }
        }

        //dd($list);

        $articles = $this->a_rep->get(['id', 'title', 'alias']);

        //dd($articles);

        $articles = $articles->reduce(function ($returnArticles, $article){
            $returnArticles[$article->alias] = $article->title;
            return $returnArticles;
        },[]);

        //dd($articles);

        $filtres = Filter::select(['id', 'title', 'alias'])->get();

        $filtres = $filtres->reduce(function ($returnFilters, $filter){
            $returnFilters[$filter->alias] = $filter->title;
            return $returnFilters;
        },['parent' => 'Раздел портфолио']);

        $portfolios = $this->p_rep->get(['id', 'title', 'alias']);
        $portfolios =  $portfolios->reduce(function ($returnPortfolios, $portfolio){
            $returnPortfolios[$portfolio->alias] = $portfolio->title;
            return $returnPortfolios;
        },[]);

        //<

        $this->content = view(env('THEME').'.admin.menus_create_content')->with(['menu' => $menu ,'type' => $type , 'option' => $option ,'menus' => $menus, 'categories' => $list, 'articles' => $articles, 'filters' => $filtres, 'portfolios' => $portfolios])->render();

        return $this->renderOutput();

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  obj  $menu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, \Corp\Menu $menu)
    {
        //
        $result = $this->m_rep->updateMenu($request, $menu);

        if(is_array($result) && !empty($result['error'])){
            return back()->with($result);
        }

        return redirect('/admin')->with($result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  obj  $menu
     * @return \Illuminate\Http\Response
     */
    public function destroy(\Corp\Menu $menu)
    {
        //
        $result = $this->m_rep->deleteMenu($menu);

        if(is_array($result) && !empty($result['error'])){
            return back()->with($result);
        }

        return redirect('/admin')->with($result);
    }
}
