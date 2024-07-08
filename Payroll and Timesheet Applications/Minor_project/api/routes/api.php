<?php

use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\TimesheetDetailController;
use App\Http\Controllers\ReportController;
use App\Models\TimesheetDetail;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\EmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Registration
Route::post('/register',[UserController::class,'register'])->name('register');// User Registrartion
Route::post('/set-password',[UserController::class,'password_page'] )->name("set-password");// Validating the registered User
Route::post('/save-password',[UserController::class,'save_password'])->name('save-password');// Validating the registered User
// Route::group(['middleware' => 'prevent-back-history'],function(){

// Login
Route::post('/login', [LoginController::class, 'authenticate']);
// Logout
Route::middleware('auth:api')->post('/logout', [LoginController::class, 'logout']);
// Forgot
Route::post('/forgot-password', [ResetPasswordController::class, "forgotPassword"])->name("forgot-password");
Route::post('/forgot-validate-user', [ResetPasswordController::class, "validateForgotPasswordUser"])->name("forgot-validate-user");
Route::post('/forgot-set-password', [ResetPasswordController::class, "forgotSetPassword"])->name("forgot-set-password");

Route::middleware("auth:api")->group(function () {
    Route::get("/dashboard", [TimesheetController::class, 'showHome'])->name('dashboard')->middleware('web');
    Route::post('/dashboard/upload-csv', [TimesheetController::class, 'uploadCsv'])->name('timesheet.create');
    Route::get('/dashboard/upload-csv/{file_id}/{timesheet_id}/{no_of_rows}', [TimesheetController::class, 'checkCsv'])->name('timesheet.check');
    Route::post('/dashboard/{timesheet_id}/store', [TimesheetController::class, 'storeCsv'])->name('timesheetcsv.store');
    Route::get('/dashboard/{timesheet_id}', [TimesheetDetailController::class, 'show'])->name('timesheetdetail.index');
    Route::post('/timesheets', [TimesheetController::class, 'store'])->name('timesheets.store');
    Route::post('/dashboard/{timesheet_id}', [TimesheetDetailController::class, 'addRow'])->name('addRow');
    Route::get('/dashboard/{timesheet_id}/edittimesheet/{id}', [TimesheetDetailController::class, 'edit'])->name('editTimesheet');
    Route::put('/dashboard/{timesheet_id}/edittimesheet/{id}/update', [TimesheetDetailController::class, 'update'])->name('updateTimesheet');
    Route::get('/dashboard/{timesheet_id}/draftstatus', [TimesheetDetailController::class, 'updateDraft'])->name('draftUpdateTimesheet');
    Route::post('/dashboard/{timesheet_id}/{id}/destroy', [TimesheetDetailController::class, 'destroy'])->name('deleteTimesheet');

    // Invoices
    Route::get('/invoice', [InvoicesController::class, 'index'])->name('invoice.index');
    Route::post('/invoices/create',[InvoicesController::class, 'store'])->name('invoice.store');
    Route::get('/invoice/delete',[InvoicesController::class, 'deleteInvoice'])->name('invoice.delete');
    Route::get('/invoice/edit',[InvoicesController::class, 'editInvoice'])->name('invoice.edit');

    // Pdfs
    Route::get('/invoice/generate-pdf', [PdfController::class, 'generatePdf'])->name('pdf.generate')->middleware('web');
    Route::get('/invoice/view-pdf', [PdfController::class, 'viewPdf'])->name('pdf.view');
    Route::get('/invoice/download-pdf', [PdfController::class, 'downloadPdf'])->name('pdf.download');
    Route::get('/invoice/delete-pdf', [PdfController::class, 'deletePdf'])->name('pdf.delete');

    //Reports
    Route::post('/show-reports', [ReportController::class, 'showReports'])->name('show.reports');
    Route::post('/generate-reports', [ReportController::class, 'generateReports'])->name('generate.reports');

    // Email
    Route::get('/email-invoice', [EmailController::class, 'mailInvoice'])->name('mail.invoice');
});

// });

