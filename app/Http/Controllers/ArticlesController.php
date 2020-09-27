<?php

namespace Corp\Http\Controllers;

use Corp\Category;
use Corp\Repositories\CommentsRepository;
use Corp\Repositories\ArticlesRepository;
use Corp\Repositories\PortfoliosRepository;
use URL;


class ArticlesController extends SiteController
{
    public function __construct(PortfoliosRepository $p_rep, ArticlesRepository $a_rep, CommentsRepository $c_rep)
    {
        parent::__construct(new \Corp\Repositories\MenusRepository(new \Corp\Menu));

        $this->a_rep = $a_rep;
        $this->p_rep = $p_rep;
        $this->c_rep = $c_rep;
        $this->bar = 'right';
        $this->template = env('THEME'). '.articles';
    }

    /**
     * Вид главнойс страницы раздела статей
     *
     * @param bool $cat_alias
     * @return ArticlesController|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index($cat_alias = false)
    {
        //Переопределяем заголовок:
        $this->title = 'Блог';

        //Получаем коллекцию статей:
        $articles = $this->getArticles($cat_alias);



        if ($cat_alias) {

            $cats = Category::all();

            //Находим категорию с текущим алиасом и берем у нее название в название страницы:
            foreach($cats as $cat){
                if($cat->alias == $cat_alias){
                    $this->title = $cat->title;
                }
            }
        }


        //Переменная содержащая код верстки слайдера в виде строки, и передаем данные контента:
        $content = view(env('THEME').'.articles_content')->with('articles',$articles)->render();

        //Добавляем переменную content в массив передаваемых переменных:
        $this->vars = array_add($this->vars, 'content', $content);

        $comments = $this->getComments(config('settings.recent_comments'));
        $portfolios = $this->getPortfolios(config('settings.recent_portfolios'));
        //dd($comments);
        $this->contentRightBar = view(env('THEME').'.articlesBar')->with(['comments' => $comments, 'portfolios' => $portfolios]);

        return $this->renderOutput();
    }

    public function getComments($take)
    {
        $comments = $this->c_rep->get(['text', 'name', 'email', 'site', 'article_id', 'user_id'], $take);
        if($comments){
            $comments->load('user', 'article');
        }
        return $comments;
    }

    public function getPortfolios($take)
    {
        $portfolios = $this->p_rep->get(['title', 'alias', 'text', 'customer', 'img', 'filter_alias'], $take);
        return $portfolios;
    }

    /**
     * Метод получения статей из БД:
     *
     *
     */

    /**
     * Коллекция статей или отдельная запись, если передан псевдоним:
     *
     * @param bool $alias
     * @return bool|mixed
     */
    public function getArticles($alias = false)
    {
        //инициализация переменно выборки:
        $where = false;

        //Если передан псевдоним, то получаем модель конкретной записи
        if ($alias){
            //WHERE `alias` = $alias
            $id = Category::select('id')->where('alias', $alias)->first()->id;
            //WHERE `category_id` = $id
            $where = ['category_id', $id];
        }

        $articles = $this->a_rep->get(['id','title', 'alias', 'created_at', 'img', 'desc', 'user_id', 'category_id','keywords','meta_desc'], false, true, $where);

        //dd($articles);

        if($articles){
            $articles->load('user','category','comments');
        }
        return $articles;
    }

    public function show($alias = false)
    {
        //dd($alias);

        //$currUrl = \Illuminate\Support\Facades\URL::current();

        //dd($currUrl);

        $article = $this->a_rep->one($alias, ['comments' => true]);

        //dd($article);

        if ($article->img){
            $article->img = json_decode($article->img);
        }
        //dd($article->comments->groupBy('parent_id'));

        //Фильтр от ошибки при обращении к не сущетсвующему материалу, проверим на наполненность модель статей:
        if(isset($articles->id)){
            $this->title = $article->title;
            $this->keywords = $article->keywords;
            $this->meta_desc = $article->meta_desc;
        }


        $content = view(env('THEME').'.article_content')->with('article', $article)->render();
        $this->vars = array_add($this->vars, 'content', $content);

        $comments = $this->getComments(config('settings.recent_comments'));
        $portfolios = $this->getPortfolios(config('settings.recent_portfolios'));

        $this->contentRightBar = view(env('THEME').'.articlesBar')->with(['comments' => $comments, 'portfolios' => $portfolios]);

        return $this->renderOutput();
    }


}
