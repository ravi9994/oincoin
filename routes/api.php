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

Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');

Route::post('addGoal','API\GoalController@addGoal');
Route::post('getGoal','API\GoalController@getGoal');
Route::post('updateGoalIcon','API\GoalController@updateGoalIcon');

Route::post('addTask', 'API\TaskController@addTask');
Route::post('getTask', 'API\TaskController@getTask');

Route::post('assignGoalAndTaskToChildren', 'API\ChildrenController@assignGoalAndTaskToChildren');
Route::post('getDashboardData', 'API\ChildrenController@getDashboardData');
Route::post('getTaskForApprove', 'API\ChildrenController@getTaskForApprove');
Route::post('approveTask', 'API\ChildrenController@approveTask');

