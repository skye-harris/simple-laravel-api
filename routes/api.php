<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/posts/{postId}/comments', [CommentController::class, 'getPaginated'])->middleware('auth:sanctum');
Route::post('/posts/{postId}/comments', [CommentController::class, 'create'])->middleware('auth:sanctum');
Route::patch('/posts/{postId}/comments/{commentId}', [CommentController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/posts/{postId}/comments/{commentId}', [CommentController::class, 'delete'])->middleware('auth:sanctum');

Route::get('/posts', [PostController::class, 'getPaginated'])->middleware('auth:sanctum');
Route::get('/posts/{id}', [PostController::class, 'getSingular'])->middleware('auth:sanctum');
Route::post('/posts', [PostController::class, 'create'])->middleware('auth:sanctum');
Route::patch('/posts/{id}', [PostController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/posts/{id}', [PostController::class, 'delete'])->middleware('auth:sanctum');

Route::post('/users/register', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);
Route::post('/users/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/users/{id}', [UserController::class, 'getUser'])->middleware('auth:sanctum');
