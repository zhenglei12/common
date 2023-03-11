<?php
use Zl\Common\Http\YiMqLogMiddleware;

Route::prefix(config('yimq.route.prefix'))->name(config('yimq.route.name'))->group(function (){
    Route::post('yimq', 'Zl\Common\Http\Controllers\YiMqController@run')->middleware(YiMqLogMiddleware::class);
});
