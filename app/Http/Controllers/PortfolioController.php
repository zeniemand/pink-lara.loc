<?php

namespace Corp\Http\Controllers;

use Corp\Repositories\PortfoliosRepository;

use Illuminate\Http\Request;

use Corp\Http\Requests;

class PortfolioController extends SiteController
{
    public function __construct(PortfoliosRepository $p_rep)
    {
        parent::__construct(new \Corp\Repositories\MenusRepository(new \Corp\Menu()));

        $this->p_rep = $p_rep;

        $this->template = env('THEME').'.portfolios';
    }

    //
    public function index(){

       //Переопределяем заголовки:
       $this->title = 'Портфолио';
       $this->keywords = 'Портфолио';
       $this->meta_desc = 'Портфолио';

       //Получаем записи портволио из БД:
       $portfolios = $this->getPortfolios();

       //dd($portfolios);

       $content = view(env('THEME').'.portfolios_content')->with('portfolios', $portfolios)->render();

       $this->vars = array_add($this->vars, 'content', $content);



    return $this->renderOutput();

    }

    public function getPortfolios($take = false, $paginate = true){

        $portfolios = $this->p_rep->get('*', $take, $paginate);

        //dd($portfolios);

        if($portfolios){
            //Подгрузаем связанные модели filter:
            $portfolios->load('filter');
        }

        return $portfolios;
    }

    public function show($alias){

        $portfolio = $this->p_rep->one($alias);

        //dd($portfolio);

        $this->title = $portfolio->title;
        $this->keywords = $portfolio->keywords;
        $this->meta_desc = $portfolio->meta_desc;


        $portfolios = $this->getPortfolios(config('settings.other_portfolios'), false);

        $content = view(env('THEME').'.portfolio_content')->with([
            'portfolio'=> $portfolio,
            'portfolios' => $portfolios
        ])->render();

        $this->vars = array_add($this->vars, 'content', $content);


        return $this->renderOutput();

    }
}
