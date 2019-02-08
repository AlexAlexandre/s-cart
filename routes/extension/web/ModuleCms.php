<?php

Route::group(
    [
        'middleware' => ['localization', 'currency'],
        'prefix'     => 'extension/payment',
        'namespace'  => 'Modules',
    ], function () {
        Route::get('/news.html', 'Cms\Cms@news')->name('news');
        Route::get('/news/{name}_{id}.html', 'Cms\Cms@newsDetail')->name('newsDetail');
    });
