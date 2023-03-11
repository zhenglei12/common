<?php
namespace Zl\Common;

use Illuminate\Support\ServiceProvider;
use SebastianBergmann\Environment\Console;
use Zl\Common\Console\YiMqMigrationUpgrade;
use Zl\Common\Console\YiMqPublishCommand;

class YiMqServiceProvider extends ServiceProvider
{
    public function boot(){
        $this->publishes([
            __DIR__ . '/../config/tm.php' => config_path('tm.php')
        ]);
        $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->commands([
            YiMqPublishCommand::class,
            YiMqMigrationUpgrade::class
        ]);
    }


    public function register()
    {

        if(!class_exists('TM')){
            class_alias(YiMqFacade::class,'TM');
        }

        $this->app->singleton(YiMqManager::class, function ($app) {
            return new YiMqManager($app);
        });
    }


}
