<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $me = \Telegram\Bot\Laravel\Facades\Telegram::bot('mybot')->getMe();
    var_dump($me->firstName);

    var_dump(storage_path('app/public/big_8de42a26c42c8592eb71a42c9776e6f9.jpg'));
});

