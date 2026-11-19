<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

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

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');

Route::middleware('pims.auth')->group(function (): void {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/doi-mat-khau', [ProfileController::class, 'edit'])->name('profile.password');
    Route::put('/doi-mat-khau', [ProfileController::class, 'update'])->name('profile.password.update');
    Route::get('/don-thuoc/{id}', [PrescriptionController::class, 'show'])->middleware('pims.role:DOCTOR,PHARMACIST')->name('prescriptions.show');
    Route::middleware('pims.role:DOCTOR')->group(function (): void {
        Route::get('/ke-don', [PrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('/ke-don', [PrescriptionController::class, 'store'])->name('prescriptions.store');
        Route::get('/lich-su-don', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    });

    Route::middleware('pims.role:ADMIN')->group(function (): void {
        Route::get('/tai-khoan', [UserController::class, 'index'])->name('users.index');
        Route::get('/tai-khoan/them', [UserController::class, 'create'])->name('users.create');
        Route::post('/tai-khoan', [UserController::class, 'store'])->name('users.store');
        Route::get('/tai-khoan/{id}/sua', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/tai-khoan/{id}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/tai-khoan/{id}/trang-thai', [UserController::class, 'status'])->name('users.status');
        Route::delete('/tai-khoan/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('pims.role:PHARMACIST')->group(function (): void {
        Route::get('/don-cho-duyet', [PrescriptionController::class, 'index'])->defaults('status', 'NEW')->name('prescriptions.pending');
        Route::post('/don-thuoc/{id}/duyet', [PrescriptionController::class, 'approve'])->name('prescriptions.approve');
        Route::post('/don-thuoc/{id}/tu-choi', [PrescriptionController::class, 'reject'])->name('prescriptions.reject');
        Route::get('/thuoc', [MedicineController::class, 'index'])->name('medicines.index');
        Route::get('/thuoc/them', [MedicineController::class, 'create'])->name('medicines.create');
        Route::post('/thuoc', [MedicineController::class, 'store'])->name('medicines.store');
        Route::get('/thuoc/{id}/sua', [MedicineController::class, 'edit'])->name('medicines.edit');
        Route::put('/thuoc/{id}', [MedicineController::class, 'update'])->name('medicines.update');
        Route::delete('/thuoc/{id}', [MedicineController::class, 'destroy'])->name('medicines.destroy');

        Route::get('/nha-cung-cap', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/nha-cung-cap/them', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/nha-cung-cap', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/nha-cung-cap/{id}/sua', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/nha-cung-cap/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/nha-cung-cap/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        Route::get('/nhap-kho', [InventoryController::class, 'receipts'])->name('inventory.receipts');
        Route::get('/nhap-kho/tao', [InventoryController::class, 'create'])->name('inventory.receipts.create');
        Route::post('/nhap-kho', [InventoryController::class, 'store'])->name('inventory.receipts.store');
        Route::get('/nhap-kho/{id}', [InventoryController::class, 'show'])->name('inventory.receipts.show');
        Route::get('/ton-kho', [InventoryController::class, 'batches'])->name('inventory.batches');
        Route::get('/bien-dong-kho', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::get('/xuat-huy', [InventoryController::class, 'disposals'])->name('inventory.disposals');
        Route::get('/xuat-huy/tao', [InventoryController::class, 'createDisposal'])->name('inventory.disposals.create');
        Route::post('/xuat-huy', [InventoryController::class, 'storeDisposal'])->name('inventory.disposals.store');
        Route::get('/xuat-huy/{id}', [InventoryController::class, 'showDisposal'])->name('inventory.disposals.show');
        Route::get('/canh-bao', [InventoryController::class, 'alerts'])->name('inventory.alerts');
        Route::put('/canh-bao/cau-hinh', [InventoryController::class, 'updateAlertSettings'])->name('inventory.alerts.settings');
        Route::get('/bao-cao', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/bao-cao/{format}', [ReportController::class, 'download'])->whereIn('format', ['xlsx', 'pdf'])->name('reports.download');
    });
});
