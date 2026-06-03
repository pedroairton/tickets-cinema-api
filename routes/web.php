<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return dd('welcome');
});
Route::get('/teste-arquivo', function () {
    return file_exists(public_path('storage/teste.txt'))
        ? 'existe'
        : 'nao existe';
});