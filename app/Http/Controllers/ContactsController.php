<?php

namespace Corp\Http\Controllers;

use Corp\Repositories\MenusRepository;
use Illuminate\Http\Request;

use Corp\Http\Requests;

use Mail;

class ContactsController extends SiteController
{
    //
    public function __construct()
    {
        parent::__construct(new \Corp\Repositories\MenusRepository(new \Corp\Menu));

        $this->bar = 'left';

        $this->template = env('THEME').'.contacts';
    }


    public function index(Request $request){

        //Проверка типа поступившего запроса
        if($request->isMethod('POST')) {

            //dd($request);


            //Определение выводимых ошибок при валидации вручную:
            $messages = [

                'required' => 'Поле :attribute обязательно к заполнению',
                'email' => 'Поле :attribute содержать правильный email'

            ];

            $this->validate($request, [
                'name' => 'required|max:255',
                'email' => 'required|email',
                'text' => 'required'
            ], $messages);

            //Собираем в массив данные запроса:
            $data = $request->all();

            $result = Mail::send(env('THEME') . '.email', ['data' => $data], function ($m) use ($data) {
                $mail_admin = env('MAIL_ADMIN');
                //От кого:
                $m->from($data['email'], $data['name']);

                //Кому:
                $m->to($mail_admin, 'Mr. Admin')->subject('Question');

            });

            //dd($result);

            if ($result) {
                return redirect()->route('contacts')->with('status', 'Email is send');
            }
        }

        $this->title = 'Контакты';

        $content = view(env('THEME').'.contact_content')->render();

        $this->vars = array_add($this->vars, 'content', $content);

        $this->contentLeftBar = view(env('THEME').'.contact_bar')->render();

        return $this->renderOutput();
    }
}
