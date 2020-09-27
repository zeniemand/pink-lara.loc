<?php

namespace Corp\Providers;

use Illuminate\Support\ServiceProvider;

use Blade;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Директива присваивания значения конктретной переменной
         *
         * @set($i,10)
         *
         * @param string $exp - содержимое передается ввиде строки
         */

        Blade::directive('set',function ($exp){
            list($name,$val) = explode(',', $exp);
            return "<?php $name = $val ?>";
        });

        /*DB::listen(function ($query){
            //echo '<h1>'.$query->sql.'</h1>';
        });*/
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
