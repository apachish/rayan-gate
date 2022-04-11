<?php
/**
 * User: shahriar
 * Date: 08/04/22
 * Time: 11:28 AM
 */

Route::middleware('web')
    ->prefix('/rayanpay/gateway')
    ->namespace('Rayanpay\RayanGate\App\Http\Controllers')
    ->group(function () {
        Route::middleware(env("USE_AUTH_GATEWAY",false)?['auth']:[])
            ->group(function () {
                Route::get('/verify', 'GatewayController@verification')->name("gateway.verify");
            });
    });

