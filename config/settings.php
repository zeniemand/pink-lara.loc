<?php
return [
    'slider_path'         => 'slider-cycle', //Путь к картинкам для слайдера
    'home_port_count'     => 5, //Колличество последних работ, которые будут отображаться на главной стр сайта
    'home_articles_count' => 3, //Колличество последних статей, которые будут отображаться на главной стр сайта
    'paginate'            => 2, //Колличество элементов выводимых на странице пагинации
    'recent_comments'     => 3, //Кол-во отображаемых коментариев
    'recent_portfolios'   => 3, //Кол-во отображаемых портфолио
    'articles_img'        => [
                                'max'  => ['width' => 816, 'height' => 282],
                                'mini' => ['width' => 55,  'height' => 55]
                             ],
    'portfolios_img'        => [
                                'max'  => ['width' => 816, 'height' => 282],
                                'mini' => ['width' => 55,  'height' => 55]
    ],
    'image'               => ['width'  => 1024,
                              'height' => 768
                             ],
    'other_portfolios' => 8, //выводимое число других портфолио на странице детального просмотра портфолио.


];