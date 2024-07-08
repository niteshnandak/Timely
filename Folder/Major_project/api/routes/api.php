<?php

use App\Http\Controllers\AdminAfaUserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\People;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\AdminOrganisationsController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PeoplePayrollController;
use App\Mail\PayslipMail;
use App\Models\Expense;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/payroll-test', [PayrollController::class, 'payroll_test']);

// Login
Route::post('login', [AuthController::class, 'login'])->name('login');

// Register
Route::post('register', [UserController::class, 'registration']);

// Password set
Route::post('verify-user-token', [UserController::class, 'verifyUser']);
Route::post('save-password', [UserController::class, 'savePassword']);

// Forgot Pasword
Route::post('forgot-password', [ResetPasswordController::class, 'forgotPassword']);
Route::post('forgot-validate-user', [ResetPasswordController::class, 'validateForgotPasswordUser']);
Route::post('save-reset-password', [ResetPasswordController::class, 'saveResetPassword']);

//Route to view Logos
Route::get('/org-logo/{filename}', [OrganisationController::class, 'showLogo'])->name('org.logo');
Route::get('/email-track/{inv_id}/{message_id}', [EmailTrackingController::class, 'track'])->name('email.track');
Route::get('link-track/{inv_id}/{message_id}', [EmailTrackingController::class, 'trackLink'])->name('link.track');


// Authenticated Users/Admins
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logoutAllDevices', [AuthController::class, 'logoutAllDevices']);

    // add admin routes here
    Route::middleware('role:admin')->group(function () {

        //admin-organisation
        Route::post('/admin-organisations', [AdminOrganisationsController::class, 'getOrganisations']);
        Route::post('/admin-create-organisation', [AdminOrganisationsController::class, 'createOrganisation']);
        Route::get('/admin-organisation/{id}', [AdminOrganisationsController::class, 'getEditDetails']);
        Route::post('/admin-organisation/update/{org_id}', [AdminOrganisationsController::class, 'updateOrganisation']);
        Route::delete('/admin-organisation/delete/{org_id}', [AdminOrganisationsController::class, 'deleteOrganisation']);
        Route::get('/admin-organisation/{org_id}/toggle-active-status', [AdminOrganisationsController::class, 'toggleActiveStatus']);
        Route::get('/admin-organisation/statistics/org-stats', [AdminOrganisationsController::class, 'getOrgStats']);
        Route::post('/admin-organisation/search/searchOrg', [AdminOrganisationsController::class, 'getSearchOrgs']);
        Route::get('/admin-organisation/organisation/{org_id}/details', [AdminOrganisationsController::class, 'getOrgDetails']);


        //admin-Afa Users
        Route::post('/admin-organisation/{orgId}/afa-users', [AdminAfaUserController::class, 'getAfaUsers']);
        Route::get('/admin-organisation/{orgId}/afa-users/afauser-stats', [AdminAfaUserController::class, 'getAfaUserStats']);
        Route::get('/admin-organisation/{orgId}/afa-users/{userId}/toggleActiveStatus', [AdminAfaUserController::class, 'toggleActiveStatus']);
        Route::post('/admin-organisation/{orgId}/afa-users/admin-create-afauser', [AdminAfaUserController::class, 'createAfaUser']);
        Route::get('/admin-organisation/{orgId}/afa-users/{userId}', [AdminAfaUserController::class, 'getAfaUserEditDetails']);
        Route::put('/admin-organisation/{orgId}/afa-users/update/{userId}', [AdminAfaUserController::class, 'updateAfaUserEditDetails']);
        Route::delete('/admin-organisation/{orgId}/afa-users/{userId}/deleteAfaUser', [AdminAfaUserController::class, 'deleteAfaUser']);
        // Route::post('/admin-organisation/{orgId}/afa-users/search', [AdminAfaUserController::class, 'searchAfaUser']);

    });

    // add AFA User routes here
    Route::middleware('role:user')->group(function () {

        //organisation
        Route::get('/dashboard', [OrganisationController::class, 'index'])->name('org.dashboard');
        Route::get('/org/settings', [OrganisationController::class, 'orgInitialInfo'])->name('org.settings');
        Route::post('/org-logo/upload', [OrganisationController::class, 'uploadOrgLogo'])->name('upload');
        Route::put('/org-details/edit/{id}', [OrganisationController::class, 'editOrgInfo'])->name('org.edit');

        // companies
        Route::get('/companies', [CompanyController::class, 'show'])->name('company.show');
        // Route::get('/company/{id}', [CompanyController::class, 'getCompany']);
        Route::post('/company/details', [CompanyController::class, 'getCompany']);
        Route::post('/company/{id}', [CompanyController::class, 'updateCompany']);
        Route::post('/company/image-upload/{id}', [CompanyController::class, 'imageUpload']);
        Route::post('/companies', [CompanyController::class, 'addCompany']);
        Route::put('/companies/{id}', [CompanyController::class, 'deleteCompany']);
        Route::post('/companies/searchCompany', [CompanyController::class, 'searchCompany']);
        Route::put('/companies/{id}/status', [CompanyController::class, 'updateStatus']);
        Route::get('/companies/stats', [CompanyController::class, 'getCompanyStats']);

        //INVOICES
        Route::post('/{company_id}/invoices/grid', [InvoiceController::class, 'getInvoices']);
        Route::post('/{company_id}/invoices/create-invoice', [InvoiceController::class, 'createInvoice']);
        Route::post('/{companyId}/invoices/update-invoice/{invId}', [InvoiceController::class, 'updateInvoice']);
        Route::get('/invoice/mail-pdf', [InvoiceController::class, 'mailInvoice']);
        Route::get('/invoice/{inv_id}/download-pdf', [InvoiceController::class, 'downloadInvoice']);
        Route::delete('/invoice/delete/{inv_id}', [InvoiceController::class, 'deleteInvoice']);
        Route::get('/invoice/get-select-assignmnets/{company_id}', [InvoiceController::class, 'getSelectedAssignments']);
        Route::get('/invoices/get-edit-details/{invId}', [InvoiceController::class, 'getEditDetails']);






        //customers
        Route::get('/customers', [CustomerController::class, 'showCustomerDatas']);
        Route::get('/customers/stats', [CustomerController::class, 'getCutomerStats']);
        Route::post('/customers/add-customer', [CustomerController::class, 'addCustomer']);
        Route::post('/customers/searchCustomer', [CustomerController::class, 'searchCustomer']);
        Route::get('/customers/fetch-customer-data', [CustomerController::class, 'fetchEditCustomerData']);
        Route::post('/customers/edit-customer', [CustomerController::class, 'editCustomerData']);
        Route::get('/customers/delete-customer', [CustomerController::class, 'deleteCustomer']);
        Route::put('/people/{id}/status', [PeopleController::class, 'updateStatus']);


        // Route::


        //people
        Route::post('/people', [PeopleController::class, 'peopleGridShow'])->name('people.peopleGridShow');
        Route::post('/people/save-edit/{people_id}', [PeopleController::class, 'peopleEditSave']);
        Route::get('allcompanies', [PeopleController::class, 'getAllCompanies']);
        Route::post('people/add-people', [PeopleController::class, 'peopleCreate']);
        Route::post('people/search-people', [PeopleController::class, 'peopleSearch']);
        Route::post('/people/people-card', [PeopleController::class, 'getPeopleStatusCard']);
        Route::get('/people/{people_id}', [PeopleController::class, 'peopleEdit']);
        Route::get('/people/remove/{id}', [PeopleController::class, 'peopleDelete'])->name('people.delete');
        //people invoices and assignments
        Route::post('/peoples/{peopleId}/assignments/grid', [PeopleController::class, 'getPeopleAssignments']);
        Route::post('/peoples/{peopleId}/invoices/grid', [PeopleController::class, 'getPeopleInvoices']);


        //Assignments
        Route::get('/assignment', [AssignmentController::class, 'showAssignments'])->name('assignment.index');
        Route::post('/assignment/create', [AssignmentController::class, 'createAssignments'])->name('assignment.store');
        Route::get('people/company/{companyId}', [AssignmentController::class, 'getPeopleByCompanyId']);
        Route::get('customers/company/{companyId}', [AssignmentController::class, 'getCustomersByCompanyId']);
        Route::post('/assignment/search/Assignment', [AssignmentController::class, 'searchAssignment']);
        Route::get('/assignment/{assignment_id}', [AssignmentController::class, 'editAssignment'])->name('assignment.edit');
        Route::post('/assignment/save-assignment/{assignment_id}', [AssignmentController::class, 'assignmentEditSave']);
        Route::get('/assignment/delete/{assignment_id}', [AssignmentController::class, 'deleteAssignment'])->name('assignment.delete');
        Route::get('/assignment/fetchAssignmentStats/{company_id}', [AssignmentController::class, 'getAssignmentStats']);

        //Timesheets
        Route::get('/timesheet', [TimesheetController::class, 'showTimesheets'])->name('timesheet.index');
        Route::post('/timesheet/upload', [TimesheetController::class, 'uploadTimesheet'])->name('timesheet.upload');
        Route::get('/timesheet-details', [TimesheetController::class, 'showTimesheetDetails'])->name('timesheet_details.index');
        Route::get('timesheet/info/{timesheet_id}', [TimesheetController::class, 'getTimesheetInfo'])->name('timesheet.info');
        Route::get('/assignments/company/{company_id}', [TimesheetController::class, 'getAssignmentsByCompanyId']);
        Route::get('/assignments/people_name/{assignment_num}', [TimesheetController::class, 'getPeopleNameByAssignmentNum']);
        Route::post('/timesheet/create', [TimesheetController::class, 'addTimesheet'])->name('timesheet.store');
        Route::post('/timesheet-detail/create', [TimesheetController::class, 'addTimesheetDetail'])->name('timesheet_detail.store');
        Route::post('/search', [TimesheetController::class, 'searchTimesheet']);
        Route::post('/timesheet/{timesheet_id}/finalize', [TimesheetController::class, 'finalizeTimesheet']);
        Route::get('/timesheet-detail/delete/{timesheet_detail_id}', [TimesheetController::class, 'deleteTimesheetDetail'])->name('timesheet_detail.delete');
        Route::get('/timesheet/delete/{timesheet_id}', [TimesheetController::class, 'deleteTimesheet'])->name('timesheet.delete');
        Route::post('/timesheet-detail/{timesheet_detail_id}', [TimesheetController::class, 'timesheetEditSave']);
        Route::get('/timesheet/{timesheet_id}/{mapping}', [TimesheetController::class, 'getTimesheetMappingDetails'])->name('timesheet.mapping');
        Route::get('/timesheet-detail/{timesheet_detail_id}/unmap', [TimesheetController::class, 'unmapTimsheetDetail'])->name('timesheet-details.unmap');
        Route::get('/timesheet-detail/{worker_id}/{company_id}/assignments', [TimesheetController::class, 'getAssignmentByWorkerId'])->name('worker.assignments');
        Route::post('/timesheet-detail/{timesheet_detail_id}/map', [TimesheetController::class, 'mapTimesheetDetails'])->name('timesheet.map');
        Route::get('/{timesheet_id}/proceed-to-invoice', [TimesheetController::class, 'proceedToInvoice'])->name('timesheet.proceed');


        //payroll batch
        Route::post('/payroll-batch', [PayrollController::class, 'showPayrollBatches']);
        Route::post('/payroll-batches', [PayrollController::class, 'addPayrollBatch']);
        Route::get('/payroll-selection/fetch-customers', [PayrollController::class, 'getCustomerDatasForPayroll']);
        Route::post('/payroll-selection/fetch-invoices', [PayrollController::class, 'getInvoicesForCustomer']);
        Route::post('/payroll-batches/verified-status-change', [PayrollController::class, 'postStatusChangeEvent']);
        Route::post('/payroll-selection/payroll-batch-details', [PayrollController::class, 'postPayrollBatchDetails']);
        Route::get('/payroll-batches/generate-report/{id}', [PayrollController::class, 'generateReport']);
        Route::post('/payroll-run', [PayrollController::class, 'updatePayrollProcess']);
        Route::post('/payroll-batch/delete', [PayrollController::class, 'deletePayrollBatch']);


        //payroll history
        Route::get('/payroll-history', [PayslipController::class, 'showPayrollHistory']);
        Route::get('/payroll-history/view-pdf/{id}', [PdfController::class, 'viewPdf'])->name('pdf.view');
        Route::post('payroll-history/email-payslip/{id}', [PayslipController::class, 'sendMail']);
        Route::post('payroll-history/{companyId}/search', [PayslipController::class, 'searchPayrollHistory']);

        Route::post('/payroll-rollback', [PayrollController::class, 'rollbackPayrollBatch']);

        // Expenses
        Route::post('/expenses/{company_id}', [ExpenseController::class, 'getExpenses']);
        Route::delete('/expenses/{company_id}/deleteExpense/{expense_id}', [ExpenseController::class, 'deleteExpense']);
        Route::get('/expenses/{company_id}/approveExpense/{expense_id}', [ExpenseController::class, 'approveExpense']);
        Route::post('/expenses/{company_id}/addExpense', [ExpenseController::class, 'addExpense']);
        Route::get('/getExpenseTypes', [ExpenseController::class, 'getExpenseTypes']);
        Route::get('/expenses/{company_id}/getPeopleNames', [ExpenseController::class, 'getPeopleNames']);
        Route::get('/expenses/{expense_id}', [ExpenseController::class, 'getEditExpenseData']);
        Route::post('/editExpense/{expense_id}', [ExpenseController::class, 'updateExpense']);
        // People Hub Expenses
        Route::post('/expenses/people/{people_id}', [ExpenseController::class, 'getPeopleExpenses']);
        Route::get('/getExpensePeopleName/{people_id}', [ExpenseController::class, 'getPeopleName']);
        // People Hub Payrolls
        Route::post('/payrolls/people/{people_id}', [PeoplePayrollController::class, 'getPeoplePayrolls']);
        Route::get('/payrolls/people/getPayrollBatchNames/{people_id}', [PeoplePayrollController::class, 'getPayrollBatchNames']);


        // payroll invoices
        Route::post('/payroll-verify-invoices', [PayrollController::class, 'create_payroll_customer']);
        Route::post('/payroll-selected-invoices', [PayrollController::class, 'payroll_selected_invoices']);
        Route::post('/payroll-unselected-invoices', [PayrollController::class, 'payroll_unselected_invoices']);
    });
});
