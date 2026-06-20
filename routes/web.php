<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\ImportBatchController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TechnicalSpecificationItemController;
use App\Http\Controllers\PriceReferenceController;
use App\Http\Controllers\ProcurementPackageController;
use App\Http\Controllers\ProcurementRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SkpdController;
use App\Http\Controllers\SubActivityController;
use App\Http\Controllers\TechnicalSpecificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ControlCardController;
use App\Http\Controllers\MonevController;
use Illuminate\Support\Facades\Route;

// Public Route
Route::get('/', function () {
    return view('welcome');
});

// Route dengan Middleware Auth
Route::middleware('auth')->group(function () {

    // Dashboard & Schedules
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/schedules', [\App\Http\Controllers\ScheduleController::class, 'index'])->name('schedules.index');

    // Resource Routes
    Route::resource('users', UserController::class);
    Route::resource('skpds', SkpdController::class);
    Route::resource('programs', ProgramController::class)->except('destroy');
    Route::resource('activities', ActivityController::class)->except('destroy');
    Route::resource('sub-activities', SubActivityController::class)->except('destroy');
    Route::resource('accounts', AccountController::class)->except('destroy');
    Route::resource('fiscal-years', FiscalYearController::class);

    // Procurement Packages Routes
    Route::get('procurement-packages', [ProcurementPackageController::class, 'index'])
        ->name('procurement-packages.index');
    
    Route::get('/procurement-packages/{package}', [ProcurementPackageController::class, 'show'])
        ->name('procurement-packages.show');

    //Route::get('procurement-packages/{procurementPackage}', [ProcurementPackageController::class, 'show'])
    //    ->name('procurement-packages.show');
    
    Route::post('packages/{package}/procurement-packages', [ProcurementPackageController::class, 'store'])
        ->name('packages.procurement-packages.store');

        // Procurement Requests Routes
    Route::get('procurement-packages/{package}/procurement-request/create', [ProcurementRequestController::class, 'create'])
        ->name('procurement-packages.procurement-request.create');
    
    Route::post('procurement-packages/{package}/procurement-request', [ProcurementRequestController::class, 'store'])
        ->name('procurement-packages.procurement-request.store');
    
    Route::get('procurement-packages/{package}/procurement-request', [ProcurementRequestController::class, 'show'])
        ->name('procurement-packages.procurement-request.show');
    
    Route::get('procurement-packages/{package}/procurement-request/edit', [ProcurementRequestController::class, 'edit'])
        ->name('procurement-packages.procurement-request.edit');
    
    Route::put('procurement-packages/{package}/procurement-request', [ProcurementRequestController::class, 'update'])
        ->name('procurement-packages.procurement-request.update');

    // Price References Routes
    Route::get('procurement-packages/{package}/price-references/print', [PriceReferenceController::class, 'print'])
        ->name('procurement-packages.price-references.print');
        
    Route::get('procurement-packages/{package}/price-references', [PriceReferenceController::class, 'index'])
        ->name('procurement-packages.price-references.index');
    
    Route::get('procurement-packages/{package}/price-references/create', [PriceReferenceController::class, 'create'])
        ->name('procurement-packages.price-references.create');
    
    Route::post('procurement-packages/{package}/price-references', [PriceReferenceController::class, 'store'])
        ->name('procurement-packages.price-references.store');
    
    Route::get('procurement-packages/{package}/price-references/{priceReference}/edit', [PriceReferenceController::class, 'edit'])
        ->name('procurement-packages.price-references.edit');
    
    Route::put('procurement-packages/{package}/price-references/{priceReference}', [PriceReferenceController::class, 'update'])
        ->name('procurement-packages.price-references.update');
    
    Route::delete('procurement-packages/{package}/price-references/{priceReference}', [PriceReferenceController::class, 'destroy'])
        ->name('procurement-packages.price-references.destroy');

    // Packages & Imports Routes
    Route::get('packages/import', [ImportBatchController::class, 'index'])->name('packages.import.index');
    Route::post('packages/import', [ImportBatchController::class, 'store'])->name('packages.import.store');
    Route::get('/packages/import/{batch}', [ImportBatchController::class, 'show'])->name('packages.import.show');    
    
    Route::get('packages/program/{program}', [PackageController::class, 'byProgram'])->name('packages.program');
    Route::get('packages/program-menu', [PackageController::class, 'programMenu'])->name('packages.program-menu');
    
    Route::get('packages/{package}/procurement', [PackageController::class, 'procurement'])->name('packages.procurement');
    Route::put('packages/{package}/procurement', [PackageController::class, 'updateProcurement'])->name('packages.procurement.update');

    Route::get('control-cards/{activity}/print', [ControlCardController::class, 'print'])->name('control-cards.print');

    // Monev
    Route::get('monev', [MonevController::class, 'index'])->name('monev.index');
    Route::get('monev/{subActivity}', [MonevController::class, 'show'])->name('monev.show');
    Route::get('monev/{subActivity}/print', [MonevController::class, 'print'])->name('monev.print');
    
    Route::resource('packages', PackageController::class);
    
    Route::post('packages/{package}/submit', [PackageController::class, 'submit'])->name('packages.submit');
    Route::post('packages/{package}/approve', [PackageController::class, 'approve'])->name('packages.approve');
    Route::post('packages/{package}/return', [PackageController::class, 'returnToDraft'])->name('packages.return');
    
    Route::post('fiscal-years/{fiscalYear}/activate', [FiscalYearController::class, 'activate'])->name('fiscal-years.activate');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Procurement Package Meta & Items Routes (Dipindahkan ke dalam grup auth agar aman)
    Route::patch('/procurement-packages/{procurementPackage}/meta', [ProcurementPackageController::class, 'updateMeta'])
        ->name('procurement-packages.meta.update');

    Route::get('/technical-specifications/{technicalSpecification}/items/create', [TechnicalSpecificationItemController::class, 'create'])
        ->name('technical-items.create');

    Route::post('/technical-specifications/{technicalSpecification}/items', [TechnicalSpecificationItemController::class, 'store'])
        ->name('technical-items.store');

    Route::get('/technical-specifications/{technicalSpecification}/edit', [TechnicalSpecificationController::class, 'editByTechnicalSpecification'])
        ->name('technical-specifications.edit');

    Route::put('/technical-specifications/{technicalSpecification}', [TechnicalSpecificationController::class, 'updateByTechnicalSpecification'])
        ->name('technical-specifications.update');

    Route::get('/procurement-packages/{package}/technical-specifications', [TechnicalSpecificationController::class, 'show'])->name('procurement-packages.technical-specifications.show');

    Route::get('/technical-items/{item}/edit', [TechnicalSpecificationItemController::class, 'edit'])
        ->name('technical-items.edit');

    Route::put('/technical-items/{item}', [TechnicalSpecificationItemController::class, 'update'])
        ->name('technical-items.update');

    Route::delete('/technical-items/{item}', [TechnicalSpecificationItemController::class, 'destroy'])
        ->name('technical-items.destroy');

    // AI Generate Draft Route
    Route::post('/procurement-packages/{procurementPackage}/generate-draft', [ProcurementPackageController::class, 'generateDraft'])
        ->name('procurement-packages.generate-draft');
        
    Route::post('/procurement-packages/{procurementPackage}/update-prompt', [ProcurementPackageController::class, 'updatePrompt'])
        ->name('procurement-packages.update-prompt');

    Route::get(
        'procurement-packages/{package}/procurement-request/print',
        [ProcurementRequestController::class, 'print']
    )->name(
        'procurement-packages.procurement-request.print'
    );

    Route::get(
    '/technical-specifications/{technicalSpecification}/print',
    [TechnicalSpecificationController::class, 'print']
    )->name('technical-specifications.print');

    // Tahap 2: Proses Pengadaan (Surat Pesanan, dll)
    Route::post('/procurement-packages/{package}/complete-preparation', [App\Http\Controllers\ProcurementProcessController::class, 'completePreparation'])->name('procurement-packages.complete-preparation');
    
    Route::get('/procurement-packages/{package}/procurement-process', [App\Http\Controllers\ProcurementProcessController::class, 'show'])->name('procurement-packages.procurement-process.show');
    Route::put('/procurement-packages/{package}/procurement-process', [App\Http\Controllers\ProcurementProcessController::class, 'update'])->name('procurement-packages.procurement-process.update');
    Route::get('/procurement-packages/{package}/procurement-process/preview', [App\Http\Controllers\ProcurementProcessController::class, 'previewDocument'])->name('procurement-packages.procurement-process.preview-document');
    Route::get('/procurement-packages/{package}/procurement-process/print', [App\Http\Controllers\ProcurementProcessController::class, 'printDocument'])->name('procurement-packages.procurement-process.print-document');
    
    // Tahap 3: Pelaksanaan Kontrak
    Route::get('/procurement-packages/{package}/execution', [App\Http\Controllers\ProcurementProcessController::class, 'execution'])->name('procurement-packages.execution');
    Route::post('/procurement-packages/{package}/execution/start', [App\Http\Controllers\ProcurementProcessController::class, 'startExecution'])->name('procurement-packages.execution.start');

    // Tahap 4: Pembayaran & Addendum
    Route::post('/procurement-packages/{package}/adendum', [App\Http\Controllers\ProcurementPaymentController::class, 'storeAddendum'])->name('procurement-payments.adendum.store');
    Route::post('/procurement-packages/{package}/payment', [App\Http\Controllers\ProcurementPaymentController::class, 'storePayment'])->name('procurement-payments.store');
    Route::get('/procurement-packages/{package}/payment', [App\Http\Controllers\ProcurementPaymentController::class, 'show'])->name('procurement-payments.show');
    Route::get('/procurement-packages/{package}/payment/preview', [App\Http\Controllers\ProcurementPaymentController::class, 'previewDocument'])->name('procurement-payments.preview-document');
    Route::get('/procurement-packages/{package}/payment/print', [App\Http\Controllers\ProcurementPaymentController::class, 'printDocument'])->name('procurement-payments.print-document');
    Route::post('/procurement-packages/{package}/payment/complete', [App\Http\Controllers\ProcurementPaymentController::class, 'complete'])->name('procurement-payments.complete');

});

require __DIR__.'/auth.php';