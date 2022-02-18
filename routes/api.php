<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::name('getAll')->get('variables/types', 'TipoCambioController@getTipoVariables');
Route::name('getByRange')->get('variables/range/{init}/{end}', 'TipoCambioController@getTipoCambio');
Route::name('getHistory')->get('variables/history', 'TipoCambioController@getHistory');