<?php
Route::prefix('api/v1/auth')->group(function () {

    Route::post('signIn', 'Igniter\\Api\\Controllers\\Users@signIn');

    Route::post('signUp', 'Igniter\\Api\\Controllers\\Users@signUp');

    Route::post('setLocation', 'Igniter\\Api\\Controllers\\Users@setLocation');

    Route::post('validateToken', 'Igniter\\Api\\Controllers\\Users@validateToken');
});

Route::prefix('api/v1/home')->group(function () {
    Route::post('menu', 'Igniter\\Api\\Controllers\\Infos@menu');
    Route::post('menuDetail', 'Igniter\\Api\\Controllers\\Infos@menuDetail');
});