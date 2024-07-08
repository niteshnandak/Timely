<?php

use App\Http\Controllers\MailController;
use App\Http\Controllers\UserController;
use App\Mail\SetPasswordMail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;


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

// Here are the Routes of the register
// After the register page is shown the form takes deatails to the UserController and create an User
Route::get('/',[UserController::class,'index'] )->name("index");
// Route::view('/home','register.register' )->name("home");
Route::post('/register',[UserController::class,'register'])->name('register');


// Here are the routes of the password setting
// After the registering the email is sent and the Message page is showed to check the email
// In the email the link is set up to set the password
Route::get('/set-password/{id}',[UserController::class,'password_page'] )->name("set-password");
Route::post('/save-password/{id}',[UserController::class,'save_password'])->name('save-password');
