<?php

namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Corp\Http\Requests;
use Corp\Http\Requests\PortfolioRequest;

use Corp\Http\Controllers\Controller;

use Gate;

use Corp\Portfolio;
use Corp\Filter;
use Corp\Repositories\PortfoliosRepository;

class PortfoliosController extends AdminController
{

    public function __construct(PortfoliosRepository $p_rep)
    {
        parent::__construct();

        //dd($this->user);

        //dd($p_rep);
        if(Gate::denies('VIEW_ADMIN_PORTFOLIOS')){
            abort(403);
        }

        $this->p_rep = $p_rep;

        $this->template = env('THEME').'.admin.portfolios';

        if(!$this->user){
            abort(403);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $this->title = "Менеджер портфолио";

        //dd($this->title);
        $portfolios = $this->getPortfolios();

        //dd($portfolios); //далее еще над видом поработать.

        $this->content = view(env('THEME').'.admin.portfolios_content')->with('portfolios', $portfolios)->render();
        //dd($this->content);

        //$this->content = 'hiihih';

        return $this->renderOutput();
    }

    public function getPortfolios(){

        return $this->p_rep->get();

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if(Gate::denies('save', new Portfolio())){
            abort(403);
        }

        $this->title = "Добавить новое портфолио";

        $filters = Filter::select(['id', 'title', 'alias'])->get();

        //dd($filters);

        $list = array();

        //Подготавливем массив для выбадающего списка:
        foreach ($filters as $filter){
            $list[$filter->alias] = $filter->title;
        }

        //dd($list);

        $this->content = view(env('THEME').'.admin.portfolios_create_content')->with('filters', $list)->render();


        return $this->renderOutput();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PortfolioRequest $request)
    {
        //
        //dd($request);

        $result = $this->p_rep->addPortfolio($request);

        //Если ошибки выдало, то возвращаемся назад с ошибками и старыми данными:
        if(is_array($result) && !empty($result['error'])){
            back()->with($result);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Portfolio $portfolio)
    {
        //dd($portfolio);
        //Проверка разрешений:
        if(Gate::denies('edit', new Portfolio())){
            abort(403);
        }

        //dd($portfolio);

        $portfolio->img = json_decode($portfolio->img);

        //dd($portfolio);

        $filters = Filter::select(['id', 'title', 'alias'])->get();

        //dd($filters);

        $this->title = 'Редактирование портфолио: ' . $portfolio->title;

        //dd($this->title);

        //Подготавливем массив для выбадающего списка:
        foreach ($filters as $filter){
            $list[$filter->alias] = $filter->title;
        }

        $this->content = view(env('THEME').'.admin.portfolios_create_content')->with(['filters' => $list, 'portfolio' => $portfolio])->render();

        return $this->renderOutput();


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PortfolioRequest $request, Portfolio $portfolio)
    {
        //
        //dd($portfolio);
        $result = $this->p_rep->updatePortfolio($request, $portfolio);

        if(is_array($result) && !empty($result['error'])){
            return back()->with($result);
        }

        //dd($portfolio);

        return redirect('/admin')->with($result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Portfolio $portfolio)
    {
        //dd($portfolio);

        $result = $this->p_rep->deletePortfolio($portfolio);

        if(is_array($result) && !empty($result['error'])){
            return back()->with($result);
        }

        return redirect('/admin')->with($result);

    }
}
