<?php
namespace Yangzie\Dingtalk\Providers;

use Illuminate\Support\ServiceProvider;
use Yangzie\Dingtalk\DingtalkLogin;

class DingtalkServiceProvider extends ServiceProvider
{
    protected $defer = true;

    // php artisan vendor:publish --provider="Lnmput\DingtalkEnterpriseLogin\Providers\DingServiceProvider"
    public function register()
    {
        $this->app->singleton(DingtalkLogin::class, function(){
            return new DingtalkLogin();
        });

        $this->app->alias(DingtalkLogin::class, 'dinglogin');

        include dirname(dirname(__FILE__)).'/routes.php';

        $this->publishes([
            dirname(dirname(__FILE__)).'/Config/dingtalk.php' => config_path('dingtalk.php')
        ], 'config');
    }

    public function provides()
    {
        return [DingtalkLogin::class, 'dinglogin'];
    }
}
