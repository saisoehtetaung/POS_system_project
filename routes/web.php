<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cool\BrandController;
use App\Http\Controllers\Cool\CategoryController;
use App\Http\Controllers\Cool\CreditController;
use App\Http\Controllers\Cool\CustomerController;
use App\Http\Controllers\Cool\DashboardController;
use App\Http\Controllers\Cool\PosController;
use App\Http\Controllers\Cool\ProductController;
use App\Http\Controllers\Cool\PurchaseController;
use App\Http\Controllers\Cool\SettingsController;
use App\Http\Controllers\Cool\StockController;
use App\Http\Controllers\Cool\SupplierController;
use App\Http\Controllers\Cool\UnitController;
use App\Http\Controllers\Cool\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['admin_auth'])->group(function () {
    Route::redirect('/', '/loginPage');
    Route::get('loginPage', [AuthController::class, 'loginPage'])->name('auth#loginPage');
    Route::get('registerPage', [AuthController::class, 'registerPage'])->name('auth#registerPage');
});

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    Route::middleware(['role:owner,manager,cashier'])->prefix('cool')->name('cool.')->group(function () {
        Route::middleware(['role:manager,cashier'])->group(function () {
            Route::get('pos', [PosController::class, 'index'])->name('pos.index');
            Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
            Route::get('pos/sales/{id}/receipt', [PosController::class, 'receipt'])->name('pos.receipt');
        });

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('stock/low', [StockController::class, 'low'])->name('stock.low');
        Route::get('stock/expired', [StockController::class, 'expired'])->name('stock.expired');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('customers/{id}/repay', [CustomerController::class, 'repay'])->name('customers.repay');

        Route::prefix('credit')->name('credit.')->group(function () {
            Route::get('customers', [CreditController::class, 'customers'])->name('customers.index');
            Route::get('customers/{id}', [CreditController::class, 'showCustomer'])->name('customers.show');

            Route::middleware(['role:owner,manager'])->group(function () {
                Route::get('suppliers', [CreditController::class, 'suppliers'])->name('suppliers.index');
                Route::get('suppliers/{id}', [CreditController::class, 'showSupplier'])->name('suppliers.show');
            });
        });

        Route::middleware(['role:owner,manager'])->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

            Route::get('products/barcode/generate', [ProductController::class, 'generateBarcode'])->name('products.barcode');
            Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
            Route::get('products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('products/{id}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

            Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
            Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
            Route::put('brands/{id}', [BrandController::class, 'update'])->name('brands.update');
            Route::delete('brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');

            Route::get('units', [UnitController::class, 'index'])->name('units.index');
            Route::post('units', [UnitController::class, 'store'])->name('units.store');
            Route::put('units/{id}', [UnitController::class, 'update'])->name('units.update');
            Route::delete('units/{id}', [UnitController::class, 'destroy'])->name('units.destroy');

            Route::post('stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');

            Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
            Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
            Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
            Route::get('purchases/{id}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
            Route::get('purchases/{id}', [PurchaseController::class, 'show'])->name('purchases.show');
            Route::put('purchases/{id}', [PurchaseController::class, 'update'])->name('purchases.update');
            Route::post('purchases/{id}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
            Route::post('purchases/{id}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');

            Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
            Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
            Route::get('suppliers/{id}', [SupplierController::class, 'show'])->name('suppliers.show');
            Route::put('suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
            Route::delete('suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
            Route::post('suppliers/{id}/pay', [SupplierController::class, 'pay'])->name('suppliers.pay');

            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        });
    });
});
