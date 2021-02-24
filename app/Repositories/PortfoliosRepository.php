<?php
namespace Corp\Repositories;

use Gate;

use Corp\Portfolio;

use Image;
use Config;

class PortfoliosRepository extends Repository {

    public function __construct(Portfolio $portfolio)
    {
        $this->model = $portfolio;
    }

    public function get($select = '*', $take = false, $pagination = false, $where = false)
    {

        //Создаем конструктор SQL запросов. Выбираем все поля из БД:
        $builder = $this->model->select($select);



        //>Если переданы параметны выборки, то их применяем:
        if($take){
            $builder->take($take);
        }
        //dd($where);

        if($where){
            $builder->where($where[0], $where[1]);
        }
        //dd($builder);
        //<




        //Если есть пагинация, то запрашиваем установленное колличество элементов на вывод из таблицы:
        if($pagination){
            //dd($builder->orderBy('id', 'desc')->get());

            return $this->check($builder->paginate(Config::get('settings.paginate')));
        }



        return $this->check($builder->orderBy('id', 'desc')->get());
    }

    public function one($alias, $attr = [])
    {


        $result =  parent::one($alias, $attr);

        return $this->check($result);
    }

    public function addPortfolio($request)
    {
        if(Gate::denies('save', $this->model)){
            abort(403);
        }

        //dd($request);

        //Получаем массив с данными для сохранения в БД:
        $data = $request->except('_token', 'image');

        if(empty($data)){
            return [ 'error' => 'Нет данных' ];
        }

        //dd($data);

        if (empty($data['alias'])){
            //Если алиас не заполнен, то заполняем автоматически из заголовка. Если заголовок кириллицей, то переводим ее в латиницу:
            $data['alias'] = $this->transliterate($data['title']);
        }

        //dd($data);

        //Проверим есть ли уже в БД запись с переданным псевдонимом:
        if($this->one($data['alias'])){
            //добавляем в массив запроса полученный для исправления алиас:
            $request->merge(['alias' => $data['alias']]);
            //Сохраняем данные запроса в сессию, чтобы в форме их опять отобразить:
            $request->flash();

            return ['error' => 'Данный псевдоним уже используется'];

        }

        //Проверяем было ли загружено изображение в форме:
        if($request->hasFile('image')){
            //Сохраним объект файла картинки в переменную:
            $image = $request->file('image');

            //dd($image);

            //Если картинка успешно загрузилась на сервер:
            if($image->isValid()){

                //Генерируем случайную строку из 8 символов и сложим в переменную:
                $str = str_random(8);

                //Создаем пустой объект:
                $obj = new \stdClass;

                //Наполняем объект свойсвами - размерами фотографий:
                $obj->mini = $str . '_mini.jpg';
                $obj->max = $str . '_max.jpg';
                $obj->path = $str . '.jpg';

                //Сохраняем объект изображения расширения Image в переменную:
                $img = Image::make($image);

                //dd($img);

                //Меням размер изображения и сохраняем его до обычного размера:
                $img->fit(Config::get('settings.image')['width'], Config::get('settings.image')['height'])
                    ->save(public_path() . '/'.env('THEME').'/images/portfolios/'.$obj->path);

                //Меням размер изображения и сохраняем его до максимального размера:
                $img->fit(Config::get('settings.portfolios_img')['max']['width'], Config::get('settings.portfolios_img')['max']['height'])
                    ->save(public_path() . '/'.env('THEME').'/images/portfolios/'.$obj->max);

                //Меням размер изображения и сохраняем его до минимального размера:
                $img->fit(Config::get('settings.portfolios_img')['mini']['width'], Config::get('settings.portfolios_img')['mini']['height'])
                    ->save(public_path() . '/'.env('THEME').'/images/portfolios/'.$obj->mini);


                //Формируем и заполняем ячейку img для заполнения в БД:
                $data['img'] = json_encode($obj);

                //Наполняем текущую модель данными:
                $this->model->fill($data);

                //Сохраянем для текущего авторизированного пользователя заполненную модель:
                if($request->user()->articles()->save($this->model)){
                    return ['status' => 'Материал добавлен.'];
                }

            }
        }

    }

    public function updatePortfolio($request, $portfolio)
    {

        if(Gate::denies('edit', $this->model)){
            abort(403);
        }

        //dd($request->all());
        $data = $request->except('_token', 'image', '_method');

        //dd($data);

        if(empty($data)){
            return array('error' => 'Нет данных');
        }

        if(empty($data['alias'])){
            $data['alias'] = $this->transliterate($data['title']);
        }

        //dd($data);

        //Получаем модель записи по переданному алиасу:
        $result = $this->one($data['alias'], false);

        //dd($data);

        if(isset($result->id) && $result->id !== $portfolio->id){
            //Добавялем ячейку алиаса в запрос:
            $request->merge(['alias'=> $data['alias']]);
            //Возвращаем данные запроса в сессии:
            $request->flash();

            return ['error' => 'Данный псевдоним уже используется'];
        }

        //dd($data);

        //Если загружаем картинку:
        if($request->hasFile('image')){
            //Сохраняем в переменную загружаемый на сервер файл фотографии:
            $image = $request->file('image');

            //Если файл загрузился на сервер, подвергнем его дальнейшей обработке:
            if($image->isValid()){

                //Получаем случайный набор из 8 строковых символов:
                $str  = str_random(8);

                //Создаем пустой объект:
                $obj = new \stdClass();

                //>Заполняем поля созданного объекта названиями для разных форматов изображений:
                $obj->mini = $str.'_mini.jpg';
                $obj->max = $str.'_max.jpg';
                $obj->path = $str.'.jpg';
                //<

                //Создаем объект класса Image библиотеки Intervention Image.
                $img = Image::make($image);

                //>Обрабатываем(обрезаем) и сохраняем каждый из трех видов изображений:
                $img->fit(Config::get('settings.image')['width'],
                    Config::get('settings.image')['height'])
                    ->save(public_path() . '/' . env('THEME') . '/images/portfolios/'. $obj->path . '.jpg');

                $img->fit(Config::get('settings.portfolios_img')['max']['width'],
                    Config::get('settings.portfolios_img')['max']['height'])
                ->save(public_path() . '/' . env('THEME') . '/images/portfolios/' . $obj->max . '.jpg');

                $img->fit(Config::get('settings.portfolios_img')['mini']['width'],
                    Config::get('settings.portfolios_img')['mini']['height'])->save(public_path() . '/' . env('THEME') . '/images/portfolios/' . $obj->mini . '.jpg');

                //<

                //Созраняем в ячейку img строку JSON объекта для сохранения в БД:
                $data['img'] = json_encode($obj);


            }
        }
        //dd($data);
        //Наполняем модель собранными данными:
        $portfolio->fill($data);

        //dd($portfolio);

        //Обновляем текущую запись в БД:
        if($portfolio->update()){
            return ['status' => 'Портфолио успешно обновлено'];
        }

    }


    public function deletePortfolio($portfolio)
    {
        if(Gate::denies('destroy', $portfolio)){
            abort(403);
        }

        //dd($portfolio);
        if($portfolio->delete()){
            return ['status' => 'Портфолио удалено'];
        }
    }

}