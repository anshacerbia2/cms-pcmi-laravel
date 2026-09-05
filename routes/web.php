<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PdfTemplateController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BoqController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\MenuController;
use App\Http\Middlewares\AuthMiddleware;
use App\Http\Middlewares\PermissionMiddleware;
use App\Http\Controllers\BankController;
use App\Http\Controllers\PcmiBankController;
use App\Http\Controllers\BillingOptionController;
use App\Http\Controllers\ReceiveVoucherController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\EmployeeController;

// Route::get('deals-dashboard', [CustomAuthController::class, 'deals-dashboard']); 
// Route::get('index', [CustomAuthController::class, 'index'])->name('index');
// Route::post('custom-login', [CustomAuthController::class, 'customLogin'])->name('login.custom'); 
// Route::get('register', [CustomAuthController::class, 'register'])->name('register-user');
// Route::post('custom-registration', [CustomAuthController::class, 'customRegistration'])->name('register.custom'); 
// Route::get('signout', [CustomAuthController::class, 'signOut'])->name('signout');

Route::get('/signin', [AuthController::class, 'getCsrf']);

Route::middleware(['guest'])->group(function () {
    Route::post('/signin', [AuthController::class, 'signin'])->name('auth.signin');
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('project-dashboard');
    }
    return view('index');
})->name('index');

Route::get('/project-dashboard', function () {
        return view('project-dashboard');
    })->name('project-dashboard'); 


Route::middleware([AuthMiddleware::class, PermissionMiddleware::class])->group(function () {
    Route::post('/signout', [AuthController::class, 'signout'])->name('auth.signout');

    Route::prefix('users')->name('users.')
    ->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');    
        Route::post('/', 'create')->name('create');   
        Route::get('/all', 'readAll')->name('readAll');  
        Route::get('/{user_id}', 'read')->name('read');  
        Route::patch('/change-password/{user_id}', 'changePassword')->name('changePassword');  
        Route::put('/{user_id}', 'update')->name('update');  
        Route::delete('/{user_id}', 'delete')->name('delete');  
    });

    Route::prefix('boqs')->name('boqs.')
    ->controller(BoqController::class)->group(function () {
        Route::get('/', 'index')->name('index');    
        Route::post('/', 'create')->name('create');   
        Route::get('/all', 'readAll')->name('readAll');  
        Route::get('/{boq_id}', 'read')->name('read');  
        Route::put('/{boq_id}', 'update')->name('update');  
        Route::delete('/', 'bulkDelete')->name('bulkDelete');  
        Route::delete('/{boq_id}', 'delete') ->whereNumber('boq_id')->name('delete');  
        Route::patch('/replicate/{proposal_id?}', 'replicate')->name('replicate'); 
        Route::patch('/unbind-proposal/{boq_id?}', 'unbindProposal')->name('unbindProposal'); 
    });

    Route::prefix('categories')->name('categories.')
    ->controller(ProductCategoryController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{category_id}', 'read')->name('read'); 
        Route::put('/{category_id}', 'update')->name('update'); 
        Route::delete('/{category_id}', 'delete')->name('delete'); 
    });

    Route::prefix('products')->name('products.')
    ->controller(productController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{product_id}', 'read')->name('read'); 
        Route::put('/{product_id}', 'update')->name('update'); 
        Route::delete('/{product_id}', 'delete')->name('delete'); 
    });

    Route::prefix('suppliers')->name('suppliers.')
    ->controller(SupplierController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{supplier_id}', 'read')->name('read'); 
        Route::put('/{supplier_id}', 'update')->name('update'); 
        Route::delete('/{supplier_id}', 'delete')->name('delete'); 
    });

    Route::prefix('roles')->name('roles.')
    ->controller(RoleController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{role_id}', 'read')->name('read'); 
        Route::put('/{role_id}', 'update')->name('update'); 
        Route::delete('/{role_id}', 'delete')->name('delete'); 
    });

    Route::prefix('menus')->name('menus.')
    ->controller(MenuController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{menu_id}', 'read')->name('read'); 
        Route::put('/{menu_id}', 'update')->name('update'); 
        Route::delete('/{menu_id}', 'delete')->name('delete'); 
    });

    Route::prefix('permissions')->name('permissions.')
    ->controller(PermissionController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{permission_id}', 'read')->name('read'); 
        Route::put('/{permission_id}', 'update')->name('update'); 
        Route::delete('/{permission_id}', 'delete')->name('delete'); 
    });

    Route::prefix('customers')->name('customers.')
    ->controller(customerController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{customer_id}', 'read')->name('read'); 
        Route::put('/{customer_id}', 'update')->name('update'); 
        Route::delete('/{customer_id}', 'delete')->name('delete'); 
    });

    Route::prefix('employees')->name('employees.')
    ->controller(EmployeeController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{employee_id}', 'read')->name('read'); 
        Route::put('/{employee_id}', 'update')->name('update'); 
        Route::delete('/{employee_id}', 'delete')->name('delete'); 
    });

    Route::prefix('projects')->name('projects.')
    ->controller(ProjectController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{project_id}', 'read')->name('read'); 
        Route::put('/{project_id}', 'update')->name('update'); 
        Route::delete('/{project_id}', 'delete')->name('delete'); 
    });

    Route::prefix('proposals')->name('proposals.')
    ->controller(ProposalController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{proposal_id}', 'read')->name('read'); 
        Route::get('/{proposal_id}/boqs', 'boqs')->name('boqs'); 
        Route::put('/{proposal_id}', 'update')->name('update'); 
        Route::delete('/{proposal_id}', 'delete')->name('delete'); 
        Route::get('/{proposal_id}/pdf', 'generatePdf')->name('pdf');
        
        // Pricing Model routes
        Route::get('/{proposal_id}/pricing-model', 'getPricingModel')->name('getPricingModel');
        Route::put('/{proposal_id}/pricing-model', 'savePricingModel')->name('savePricingModel');
        Route::get('/{proposal_id}/available-boqs', 'getAvailableBoqs')->name('getAvailableBoqs');
        // Route::get('/{proposal_id}/get-boqs', 'getBoqs')->name('getBoqs');
      });

    Route::prefix('invoices')->name('invoices.')
    ->controller(InvoiceController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/unpaid', 'readUnpaid')->name('unpaid'); 
        Route::get('/{invoice_id}', 'read')->name('read'); 
        Route::put('/{invoice_id}', 'update')->name('update'); 
        Route::delete('/{invoice_id}', 'delete')->name('delete'); 
        Route::get('/{invoice_id}/pdf', 'generatePdf')->name('pdf'); 
    });
    // This single line creates ALL 7 routes for you automatically
    Route::prefix('banks')->name('banks.')
    ->controller(BankController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{bank_id}', 'read')->name('read'); 
        Route::put('/{bank_id}', 'update')->name('update'); 
        Route::delete('/{bank_id}', 'delete')->name('delete'); 
    });

    Route::prefix('pdf-templates')->name('pdf-templates.')
    ->controller(PdfTemplateController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{template_id}', 'read')->name('read'); 
        Route::put('/{template_id}', 'update')->name('update'); 
        Route::delete('/{template_id}', 'delete')->name('delete'); 
        Route::post('/preview', 'preview')->name('preview'); 
    });

    Route::prefix('pcmibanks')->name('pcmibanks.')
    ->controller(PcmiBankController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/all', 'readAll')->name('readAll'); 
        Route::get('/{pcmibank_id}', 'read')->name('read'); 
        Route::put('/{pcmibank_id}', 'update')->name('update'); 
        Route::delete('/{pcmibank_id}', 'delete')->name('delete'); 
    });

    Route::prefix('billing-options')->name('billing-options.')
    ->controller(BillingOptionController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/{billing_option_id}', 'read')->name('read'); 
        Route::put('/{billing_option_id}', 'update')->name('update'); 
        Route::delete('/{billing_option_id}', 'delete')->name('delete'); 
    });

    Route::prefix('rvs')->name('rvs.')
    ->controller(ReceiveVoucherController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/{rv_id}', 'read')->name('read'); 
        Route::put('/{rv_id}', 'update')->name('update'); 
        Route::delete('/{rv_id}', 'delete')->name('delete'); 
    });

    Route::prefix('pvs')->name('pvs.')
    ->controller(PaymentVoucherController::class)->group(function() {
        Route::get('/', 'index')->name('index'); 
        Route::post('/', 'create')->name('create'); 
        Route::get('/{pv_id}', 'read')->name('read'); 
        Route::put('/{pv_id}', 'update')->name('update'); 
        Route::delete('/{pv_id}', 'delete')->name('delete'); 
    });
    
});
