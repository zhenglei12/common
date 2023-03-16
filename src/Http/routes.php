<?php
use Zl\Common\Http\YiMqLogMiddleware;

Route::prefix(config('tm.route.prefix'))->name(config('tm.route.name'))->group(function (){
    Route::post('tm', 'Zl\Common\Http\Controllers\YiMqController@run')->middleware(YiMqLogMiddleware::class);
});
