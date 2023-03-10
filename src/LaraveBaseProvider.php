<?php
/**
 * Copyright (C), 2016-2018, Shall Buy Life info. Co., Ltd.
 * FileName: LaraveBaseProvider.php
 * Description: 说明
 *
 * @author Administrator
 * @Create Date    2018/11/1 14:35
 * @Update Date    2018/11/1 14:35 By Administrator
 * @version v1.0
 */

namespace Zl\Common;
use Illuminate\Support\ServiceProvider;
use Zl\Common\Exception\ExceptionFactory;


class LaraveBaseProvider extends ServiceProvider
{

    public function boot()
    {

    }


    public function register(){
        $this->app->singleton("ExceptionFactory", function ($app) {
            return  new  ExceptionFactory();
        });
    }

}