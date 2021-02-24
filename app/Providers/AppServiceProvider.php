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

        //dd($_ENV);

        //$env = env('APP_ENV');
        //dd(['APP_ENV value: ' => $env]);
        //dd($this->app->environment('production'));
       // dd($this->app->environment());

        if($this->app->environment('production')) {
            //\URL::forceScheme('https');
            //dd(['message' => 'stooop']);

            $this->app['request']->server->set('HTTPS', true);


            //$env = env('APP_ENV');
            //dd(['APP_ENV value: ' => $env]);
            //\URL::forceScheme('https');
        }


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
