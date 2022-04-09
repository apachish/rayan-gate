<?php
/**
 * User: shahriar
 * Date: 08/04/22
 * Time: 11:28 AM
 */

Route::middleware('web')
    ->prefix('panel/users')
    ->namespace('ArmanTadbir\AuthPassport\App\Http\Controllers')
    ->group(function () {
        Route::middleware(['auth'])
            ->group(function () {
                Route::get('/customer/list', 'UsersController@index')->name("users-list");
            });
    });

