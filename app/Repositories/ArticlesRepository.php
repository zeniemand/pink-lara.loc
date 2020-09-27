<?php
namespace Corp\Repositories;

use Corp\Article;
use Gate;
use Image;
use Config;

class ArticlesRepository extends Repository {

    public function __construct(Article $articles)
    {
        $this->model = $articles;
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
            return $this->check($builder->paginate(Config::get('settings.paginate')));
        }


        return $this->check($builder->orderBy('id', 'desc')->get());
    }

    public function one($alias, $attr = [])
    {
        //dd($attr);
        $article = parent::one($alias, $attr);
        //dd($article);

        if ($article && !empty($attr)){
            $article->load('comments');
            $article->comments->load('user');
        }

        return $article;
    }

    public function addArticle($request)
    {
        if(Gate::denies('save', $this->model)){
            abort(403);
        }

        $data = $request->except('_token','image');

        if(empty($data)){
            return array('error' => 'Нет данных');
        }

        //Если алиас не указали, указать автоматически транслитерацией:
        if(empty($data['alias'])){
            $data['alias'] = $this->transliterate($data['title']);
        }

        //dd($data);

        //Если введенное название алиаса уже есть в БД, то ошибка:
        if($this->one($data['alias'], false)){
            //Добавляем введенные алиас в поле в объекте Request:
            $request->merge(array('alias' => $data['alias']));
            //dd($request);
            //Сохраняем в сессию все данные в объекте:
            $request->flash();

            return ['error' => 'Данный псевдоним уже исспользуется'];
        }

        //Убеждаемся в наличии файла в ячейке image:
        if($request->hasFile('image')){
            $image = $request->file('image');

            //Убеждаемся в успешной загрузке файла, и обрабатывем его как нам надо:
            if($image->isValid()){

                $str = str_random(8);

                //Создаем стандартный объект, и наполним его нужными нам свойствами с радномными именами:
                $obj = new \stdClass;

                $obj->mini = $str.'_mini.jpg';
                $obj->max = $str.'_max.jpg';
                $obj->path = $str.'.jpg';

                $img = Image::make($image);

                //dd($img);

                //Подгоняем под нужные размеры изображение, и сохраняем его по нужному нам пути в директории public:
                $img->fit(Config::get('settings.image')['width'],
                          Config::get('settings.image')['height'])
                    ->save(public_path().'/'.env('THEME').'/images/articles/'.$obj->path);


                $img->fit(Config::get('settings.articles_img')['max']['width'],
                    Config::get('settings.articles_img')['max']['height'])
                    ->save(public_path().'/'.env('THEME').'/images/articles/'.$obj->max);

                $img->fit(Config::get('settings.articles_img')['mini']['width'],
                    Config::get('settings.articles_img')['mini']['height'])
                    ->save(public_path().'/'.env('THEME').'/images/articles/'.$obj->mini);


                //dd(Config::get('settings.image')['width']);
                //сохраняем в ячейку img данные в JSON формате:
                $data['img'] = json_encode($obj);

                //Наполняем модель полученными данными для ячейки img:
                $this->model->fill($data);

                //dd($this->model);
                //dd($request->user()->articles());

                //Пересохраняем новую модель с данными фото:
                if($request->user()->articles()->save($this->model)){

                    return ['status' => 'Материал добавлен'];
                }
            }
        }
    }

    public function updateArticle($request, $article)
    {
        if(Gate::denies('edit', $this->model)){
            abort(403);
        }

        $data = $request->except('_token','image','_method');

        if(empty($data)){
            return array('error' => 'Нет данных');
        }

        if(empty($data['alias'])){
            $data['alias'] = $this->transliterate($data['title']);
        }

        //dd($data);

        //Получаем модель по переданному алиасу для дальнейшей проверки на совпадение:
        $result = $this->one($data['alias'], false);

        //dd($result);

        //Проверяем на совпадения с БД:
        if(isset($result->id) && $result->id != $article->id){
            //добавляем я чейку псевдонима в запросе с нашим совпадением:
            $request->merge(array('alias' => $data['alias']));
            //dd($request);
            //Возвращаем данные запроса в сессии:
            $request->flash();

            return ['error' => 'Данный псевдоним уже исспользуется'];
        }

        //dd($data);

        if($request->hasFile('image')){
            $image = $request->file('image');

            if($image->isValid()){

                $str = str_random(8);

                $obj = new \stdClass;

                $obj->mini = $str.'_mini.jpg';
                $obj->max = $str.'_max.jpg';
                $obj->path = $str.'.jpg';


                $img = Image::make($image);

                //dd($img);
                $img->fit(Config::get('settings.image')['width'],
                    Config::get('settings.image')['height'])
                    ->save(public_path().'/'.env('THEME').'/images/articles/'.$obj->path);


                $img->fit(Config::get('settings.articles_img')['max']['width'],
                    Config::get('settings.articles_img')['max']['height'])
                    ->save(public_path().'/'.env('THEME').'/images/articles/'.$obj->max);

                $img->fit(Config::get('settings.articles_img')['mini']['width'],
                    Config::get('settings.articles_img')['mini']['height'])
                    ->save(public_path().'/'.env('THEME').'/images/articles/'.$obj->mini);


                $data['img'] = json_encode($obj);

                }
            }

        //Заполнили модель:
        $article->fill($data);

        //Актуализировали модель:
        if($article->update()){
            return ['status' => 'Материал добавлен'];
        }

    }

    public function deleteArticle($article)
    {
        if(Gate::denies('destroy', $article)){
            abort(403);
        }

        //Удаляем все связанные комментарии из БД:
        $article->comments()->delete();

        if($article->delete()){
            return ['status' => 'Материал удален'];
        }
    }
}