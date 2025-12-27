<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', fn () => view('landing'));
Route::get('/login', fn () => view('auth.login'));
Route::get('/register', fn () => view('auth.register'));


Route::get('/quests', function () {
    return view('quests.index');
});

Route::get('/quests/{id}', function ($id) {
    return view('quests.show');
});