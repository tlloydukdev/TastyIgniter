<?php
Route::prefix('api/v1/auth')->group(function () {

    Route::post('signIn', 'Igniter\\Api\\Controllers\\Users@signIn');

    Route::post('signUp', 'Igniter\\Api\\Controllers\\Users@signUp');

    Route::post('setLocation', 'Igniter\\Api\\Controllers\\Users@setLocation');

    Route::post('validateToken', 'Igniter\\Api\\Controllers\\Users@validateToken');

    Route::post('forgotPassword', 'Igniter\\Api\\Controllers\\Users@forgotPassword');
});

Route::prefix('api/v1/home')->group(function () {
    Route::post('menu', 'Igniter\\Api\\Controllers\\Infos@menu');
    Route::post('menuDetail', 'Igniter\\Api\\Controllers\\Infos@menuDetail');
    Route::post('getCheckOutTime', 'Igniter\\Api\\Controllers\\Infos@getCheckOutTime');
    Route::post('getSavedCard', 'Igniter\\Api\\Controllers\\Infos@getSavedCard');
    Route::post('deleteCard', 'Igniter\\Api\\Controllers\\Infos@deleteCard');
    Route::post('makePaymentIntent', 'Igniter\\Api\\Controllers\\Infos@makePaymentIntent');
    Route::post('verifyPayment', 'Igniter\\Api\\Controllers\\Infos@verifyPayment');
    Route::post('getOrders', 'Igniter\\Api\\Controllers\\Infos@getOrders');
    Route::post('addFavorites', 'Igniter\\Api\\Controllers\\Infos@addFavorites');
    Route::post('getFavorites', 'Igniter\\Api\\Controllers\\Infos@getFavorites');
    Route::get('getPolicy', 'Igniter\\Api\\Controllers\\Infos@getPolicy');
    Route::get('getTerms', 'Igniter\\Api\\Controllers\\Infos@getTerms');
    Route::get('getStripeInfo', 'Igniter\\Api\\Controllers\\Infos@getStripeInfo');
});