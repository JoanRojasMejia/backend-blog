<?php

// use App\Http\Controllers\ArticleController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([
  'middleware' => 'api',
  'prefix' => 'auth'
], function () {
  Route::post('login', 'App\Http\Controllers\AuthController@login');
  Route::post('logout', 'App\Http\Controllers\AuthController@logout');
  Route::post('register', 'App\Http\Controllers\AuthController@register');
});

Route::group([
  'prefix' => 'user'
], function () {
  Route::get('', 'App\Http\Controllers\UserController@index');
  Route::put('/{id}', 'App\Http\Controllers\UserController@update');
  Route::delete('/{id}', 'App\Http\Controllers\UserController@deleteUser');
});

Route::group([
  'prefix' => 'article'
], function () {
  Route::get('', 'App\Http\Controllers\ArticleController@index');
  Route::post('', 'App\Http\Controllers\ArticleController@save');
  Route::put('/{id}', 'App\Http\Controllers\ArticleController@update')->middleware('articleExist');
  Route::delete('/{id}', 'App\Http\Controllers\ArticleController@delete');
  Route::post('/comment/{id}', 'App\Http\Controllers\ArticleController@saveComment')->middleware('articleExist');
  Route::delete('/comment/{id}', 'App\Http\Controllers\ArticleController@deleteComment')->middleware('articleExist');
  Route::post('/like/{id}', 'App\Http\Controllers\ArticleController@saveLike')->middleware('articleExist');
  Route::delete('/like/{id}', 'App\Http\Controllers\ArticleController@deleteLike')->middleware('articleExist');
  Route::get('/slug/{slug}', 'App\Http\Controllers\ArticleController@findArticleBySlug');
});

Route::group([
  'prefix' => 'category'
], function () {
  Route::get('', 'App\Http\Controllers\CategoryController@index');
  Route::post('', 'App\Http\Controllers\CategoryController@save');
  Route::delete('/{id}', 'App\Http\Controllers\CategoryController@delete');
});
