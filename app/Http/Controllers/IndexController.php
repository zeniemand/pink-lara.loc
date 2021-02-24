<?php

namespace Corp\Http\Controllers;

use Corp\Repositories\ArticlesRepository;
use Corp\Repositories\PortfoliosRepository;
use Corp\Repositories\SlidersRepository;
use Config;


class IndexController extends SiteController
{
    public function __construct(SlidersRepository $s_rep, PortfoliosRepository $p_rep, ArticlesRepository $a_rep)
    {
        //Обращаемся к родительскому конструктору, и передаем ему нужные объекты в зависимость:
        parent::__construct(new \Corp\Repositories\MenusRepository(new \Corp\Menu));

        $this->a_rep = $a_rep;
        $this->s_rep = $s_rep;
        $this->p_rep = $p_rep;
        $this->bar = 'right';
        $this->template = env('THEME'). '.index';
    }


    /**
     * Вид главной страницы.
     *
     * @return IndexController|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index()
    {
        //phpinfo();
        //Переменная содержащая элементы portfolio:
        $portfolios = $this->getPortfolio();

        //Формируем переменную контента в которую передаем данные portfolio:
        $content = view(env('THEME').'.content')->with('portfolios', $portfolios)->render();

        //Добавляем переменную content в массив передаваемых переменных:
        $this->vars = array_add($this->vars, 'content', $content);

        //Переменная содержащая элементы слайдера:
        $sliderItems = $this->getSliders();

        //Формируем переменную слайдера, и передаем соотвествующие данные слайдера:
        $sliders = view(env('THEME').'.slider')->with('sliders', $sliderItems)->render();

        //Добавляем переменную слайдера в массив передаваемых переменных:
        $this->vars = array_add($this->vars, 'sliders',$sliders);

        //>Переопределяем содержимое метатегов: заголовок, ключивые слова, описание соответственно:
        $this->keywords ='Home Page';
        $this->title = 'Главная страница';
        $this->meta_desc = 'Home Page';
        //<

        //Переменная содержащая статьи:
        $articles = $this->getArticles();

        //Переменная правого сайдбара:
        $this->contentRightBar = view(env('THEME').'.indexBar')->with('articles', $articles)->render();

        return $this->renderOutput();
    }

    /**
     * Метод получения списка статей в виде коллекции моделей
     *
     * @return mixed
     */
    protected function getArticles(){

        //Выбираем все записи:
        $articles = $this->a_rep->get(['title', 'img', 'alias', 'created_at'], Config::get('settings.home_articles_count'));

        return $articles;
    }

    /**
     * Метод получения списка работ портфолио в виде коллекции моделей
     *
     * @return mixed
     */
    protected function getPortfolio(){

        //Выбираем все записи:
        $portfolio = $this->p_rep->get('*',Config::get('settings.home_port_count'));

        return $portfolio;
    }

    /**
     * Метод получения элементов слайдера в виде коллекции
     *
     * @return  mixed
     */
    public function getSliders(){

        //Переменная получающая коллекцию моделей:
        $sliders = $this->s_rep->get();

        //Проверяем заполненна ли коллекция слайдеров:
        if ($sliders->isEmpty()){
            return false;
        }

        //Какждому элементу коллекции добавим свойство img с путем к изображению:
        $sliders->transform(function ($item,$key){
            $item->img = Config::get('settings.slider_path').'/'.$item->img;
            return $item;
        });

       return $sliders;
    }

}
