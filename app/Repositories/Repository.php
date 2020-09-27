<?php

namespace Corp\Repositories;

use Config;

abstract class Repository {

    //Свойство для хранения объекта модели.
    protected $model = false;

    /**
     * Получение коллекции записей в зависимости от переданных параметров.
     *
     * @param string $select
     * @param bool $take
     * @param bool $pagination
     * @param bool $where
     * @return mixed
     */
    public function get($select = '*', $take = false, $pagination = false, $where = false)
    {

        //Создаем конструктор SQL запросов. Выбираем все поля из БД:
        $builder = $this->model->select($select);

        //>Если переданы параметны выборки, то их применяем:
        if($take){
            $builder->take($take);
        }

        if($where){
            $builder->where($where[0], $where[1]);
        }
        //<

        //Если есть пагинация, то запрашиваем установленное колличество элементов на вывод из таблицы:
        if($pagination){
            return $this->check($builder->paginate(Config::get('settings.paginate')));
        }

        return $this->check($builder->get());
    }

    /**
     * Метод проверки поступивших данных и разбор JSON при надобности
     *
     * @param $result
     * @return mixed
     */
    protected function check($result)
    {

        //Проверяем коллекция или модель попала к нам:
        if ($result instanceof \Illuminate\Database\Eloquent\Collection || $result instanceof \Illuminate\Pagination\LengthAwarePaginator){

            //Пуста ли коллекция:
            if($result->isEmpty()){
                return false;
            }

            //Получаем объект с преобразованными данными из JSON:
            $result->transform(function ($item,$key){
                if(is_string($item->img) && is_object(json_decode($item->img)) && (json_last_error() == JSON_ERROR_NONE)) {
                    $item->img = json_decode($item->img);
                }
                return $item;
            });

            return $result;

        } else {

            //Проверяем состояние свойства img, и декодируем его из JSON в нужный нам объект:
            if(isset($result->img) && is_string($result->img) && is_object(json_decode($result->img)) && (json_last_error() == JSON_ERROR_NONE)) {
                $result->img = json_decode($result->img);
            }

            return $result;
        }
    }

    //выборка одной записи:

    /**
     * Выборка одной записи по переданному псевдониму.
     *
     * @param $alias
     * @param array $attr
     * @return mixed
     */
    public function one($alias, $attr = [])
    {
        $result = $this->model->where('alias', $alias)->first();

        return $result;
    }

    /**
     * Транслитерация переданной строки из кириллицы в латиницу.
     *
     * @param $string
     * @return bool|mixed|null|string|string[]
     */
    public function transliterate($string)
    {
        //Переводим переданную строку в нижний регистр, и передаем нужную кодировку
        $str = mb_strtolower($string, 'UTF-8');

        //Массив алфавита латиница => кириллица:
        $letter_array = array(
            'a' => 'а',
            'b' => 'б',
            'v' => 'в',
            'g' => 'г,ґ',
            'd' => 'д',
            'e' => 'е,є,э',
            'jo' => 'ё',
            'zh' => 'ж',
            'z' => 'з',
            'i' => 'и,і',
            'ji' => 'ї',
            'j' => 'й',
            'k' => 'к',
            'l' => 'л',
            'm' => 'м',
            'n' => 'н',
            'o' => 'о',
            'p' => 'п',
            'r' => 'р',
            's' => 'с',
            't' => 'т',
            'u' => 'у',
            'f' => 'ф',
            'kh' => 'х',
            'ts' => 'ц',
            'ch' => 'ч',
            'sh' => 'ш',
            'shch' => 'щ',
            '' => 'ъ',
            'y' => 'ы',
            '' => 'ь',
            'yu' => 'ю',
            'ya' => 'я',
        );

        //заменяем все кириллические символы строки поиска на латиницу:
        foreach ($letter_array as $letter => $kyr){
            $kyr = explode(',',$kyr);

            $str = str_replace($kyr, $letter, $str);
        }

        //пробелы и символя не входящие в нужный нам диапазон - меняем на тире:
        $str = preg_replace('/(\s|[^A-Za-z0-9\-])+/','-', $str);

        //удалим из конца возомжный символ тире:
        $str = trim($str,'-');

        return $str;
    }

}