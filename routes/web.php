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

use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SkpdController;
use App\Http\Controllers\SubActivityController;
use App\Http\Controllers\TechnicalSpecificationController;
use App\Http\Controllers\ControlCardController;
use App\Http\Controllers\MonevController;
use App\Http\Controllers\BukuRegisterController;
use Illuminate\Support\Facades\Route;

// Public Route
Route::get('/', function () {
    return view('welcome');
});
Route::view('/panduan', 'panduan')->name('panduan');
Route::view('/disclaimer', 'disclaimer')->name('disclaimer');

// Route dengan Middleware Auth
Route::middleware('auth')->group(function () {

    // Pencarian cepat paket (header)
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');

    // Profil user (info akun & ganti password sendiri)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard (Redirector)
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Role-specific Dashboards
    Route::get('/admin/dashboard', [\App\Http\Controllers\DashboardController::class, 'admin'])
        ->middleware('role:Admin|Super Admin')
        ->name('dashboard.admin');
        
    Route::get('/kabid/dashboard', [\App\Http\Controllers\DashboardController::class, 'kabid'])
        ->middleware('role:Kabid')
        ->name('dashboard.kabid');
        
    Route::get('/staf/dashboard', [\App\Http\Controllers\DashboardController::class, 'staf'])
        ->middleware('role:Staff')
        ->name('dashboard.staf');
        
    $sharedProcurementRoutes = function () {
        Route::post('packages/{package}/procurement-packages', [\App\Http\Controllers\ProcurementPackageController::class, 'store'])
            ->name('packages.procurement-packages.store');
    
        // Procurement Requests Routes
        Route::get('procurement-packages/{package}/procurement-request/create', [\App\Http\Controllers\ProcurementRequestController::class, 'create'])
            ->name('procurement-packages.procurement-request.create');
        Route::post('procurement-packages/{package}/procurement-request', [\App\Http\Controllers\ProcurementRequestController::class, 'store'])
            ->name('procurement-packages.procurement-request.store');
        Route::get('procurement-packages/{package}/procurement-request', [\App\Http\Controllers\ProcurementRequestController::class, 'show'])
            ->name('procurement-packages.procurement-request.show');
        Route::get('procurement-packages/{package}/procurement-request/edit', [\App\Http\Controllers\ProcurementRequestController::class, 'edit'])
            ->name('procurement-packages.procurement-request.edit');
        Route::put('procurement-packages/{package}/procurement-request', [\App\Http\Controllers\ProcurementRequestController::class, 'update'])
            ->name('procurement-packages.procurement-request.update');
        Route::get('procurement-packages/{package}/procurement-request/print', [\App\Http\Controllers\ProcurementRequestController::class, 'print'])
            ->name('procurement-packages.procurement-request.print');
    
        // Price References Routes
        Route::get('procurement-packages/{package}/price-references/print', [\App\Http\Controllers\PriceReferenceController::class, 'print'])
            ->name('procurement-packages.price-references.print');
        Route::get('procurement-packages/{package}/price-references', [\App\Http\Controllers\PriceReferenceController::class, 'index'])
            ->name('procurement-packages.price-references.index');
        Route::get('procurement-packages/{package}/price-references/create', [\App\Http\Controllers\PriceReferenceController::class, 'create'])
            ->name('procurement-packages.price-references.create');
        Route::post('procurement-packages/{package}/price-references', [\App\Http\Controllers\PriceReferenceController::class, 'store'])
            ->name('procurement-packages.price-references.store');
        Route::get('procurement-packages/{package}/price-references/{priceReference}/edit', [\App\Http\Controllers\PriceReferenceController::class, 'edit'])
            ->name('procurement-packages.price-references.edit');
        Route::put('procurement-packages/{package}/price-references/{priceReference}', [\App\Http\Controllers\PriceReferenceController::class, 'update'])
            ->name('procurement-packages.price-references.update');
        Route::delete('procurement-packages/{package}/price-references/{priceReference}', [\App\Http\Controllers\PriceReferenceController::class, 'destroy'])
            ->name('procurement-packages.price-references.destroy');
    
        // Packages resources actions
        Route::post('packages/{package}/submit', [\App\Http\Controllers\PackageController::class, 'submit'])->name('packages.submit');
        Route::post('packages/{package}/approve', [\App\Http\Controllers\PackageController::class, 'approve'])->name('packages.approve');
        Route::post('packages/{package}/return', [\App\Http\Controllers\PackageController::class, 'returnToDraft'])->name('packages.return');
        Route::get('control-cards/{activity}/print', [\App\Http\Controllers\ControlCardController::class, 'print'])->name('control-cards.print');
        
        // Procurement Package Meta & Items Routes
        Route::patch('/procurement-packages/{procurementPackage}/meta', [\App\Http\Controllers\ProcurementPackageController::class, 'updateMeta'])
            ->name('procurement-packages.meta.update');
        Route::patch('/procurement-packages/{procurementPackage}/dikecualikan', [\App\Http\Controllers\ProcurementPackageController::class, 'updateDikecualikan'])
            ->name('procurement-packages.dikecualikan.update');
        Route::post('/procurement-packages/{procurementPackage}/complete', [\App\Http\Controllers\ProcurementPackageController::class, 'complete'])
            ->name('procurement-packages.complete');
        Route::post('/procurement-packages/{procurementPackage}/external-records', [\App\Http\Controllers\ProcurementExternalRecordController::class, 'store'])
            ->name('procurement-external-records.store');
        Route::delete('/procurement-packages/{procurementPackage}/external-records/{externalRecord}', [\App\Http\Controllers\ProcurementExternalRecordController::class, 'destroy'])
            ->name('procurement-external-records.destroy');
        Route::get('/procurement-packages/{procurementPackage}/external-records/{externalRecord}/print', [\App\Http\Controllers\ProcurementExternalRecordController::class, 'print'])
            ->name('procurement-external-records.print');
    
        Route::get('/technical-specifications/{technicalSpecification}/edit', [\App\Http\Controllers\TechnicalSpecificationController::class, 'editByTechnicalSpecification'])
            ->name('technical-specifications.edit');
        Route::put('/technical-specifications/{technicalSpecification}', [\App\Http\Controllers\TechnicalSpecificationController::class, 'updateByTechnicalSpecification'])
            ->name('technical-specifications.update');
        Route::get('/technical-specifications/{technicalSpecification}/print', [\App\Http\Controllers\TechnicalSpecificationController::class, 'print'])
            ->name('technical-specifications.print');
        Route::get('/procurement-packages/{package}/technical-specifications', [\App\Http\Controllers\TechnicalSpecificationController::class, 'show'])->name('procurement-packages.technical-specifications.show');

        // AI Generate Draft Route
        Route::post('/procurement-packages/{procurementPackage}/generate-draft', [\App\Http\Controllers\ProcurementPackageController::class, 'generateDraft'])
            ->name('procurement-packages.generate-draft');
        Route::post('/procurement-packages/{procurementPackage}/update-prompt', [\App\Http\Controllers\ProcurementPackageController::class, 'updatePrompt'])
            ->name('procurement-packages.update-prompt');
    
        // Tahap 2: Proses Pengadaan (Surat Pesanan, dll)
        Route::post('/procurement-packages/{package}/complete-preparation', [\App\Http\Controllers\ProcurementProcessController::class, 'completePreparation'])->name('procurement-packages.complete-preparation');
        Route::get('/procurement-packages/{package}/procurement-process', [\App\Http\Controllers\ProcurementProcessController::class, 'show'])->name('procurement-packages.procurement-process.show');
        Route::put('/procurement-packages/{package}/procurement-process', [\App\Http\Controllers\ProcurementProcessController::class, 'update'])->name('procurement-packages.procurement-process.update');
        Route::get('/procurement-packages/{package}/procurement-process/preview', [\App\Http\Controllers\ProcurementProcessController::class, 'previewDocument'])->name('procurement-packages.procurement-process.preview-document');
        Route::get('/procurement-packages/{package}/procurement-process/print', [\App\Http\Controllers\ProcurementProcessController::class, 'printDocument'])->name('procurement-packages.procurement-process.print-document');
        
        // Tahap 3: Pelaksanaan Kontrak
        Route::get('/procurement-packages/{package}/execution', [\App\Http\Controllers\ProcurementProcessController::class, 'execution'])->name('procurement-packages.execution.show');
        Route::post('/procurement-packages/{package}/execution/start', [\App\Http\Controllers\ProcurementProcessController::class, 'startExecution'])->name('procurement-packages.execution.start');
    
        // Tahap 4: Pembayaran & Addendum
        Route::post('/procurement-packages/{package}/adendum', [\App\Http\Controllers\ProcurementPaymentController::class, 'storeAddendum'])->name('procurement-packages.adendum.store');
        Route::post('/procurement-packages/{package}/payment', [\App\Http\Controllers\ProcurementPaymentController::class, 'storePayment'])->name('procurement-packages.payment.store');
        Route::get('/procurement-packages/{package}/payment', [\App\Http\Controllers\ProcurementPaymentController::class, 'show'])->name('procurement-packages.payment.show');
        Route::get('/procurement-packages/{package}/payment/preview', [\App\Http\Controllers\ProcurementPaymentController::class, 'previewDocument'])->name('procurement-packages.payment.preview-document');
        Route::get('/procurement-packages/{package}/payment/print', [\App\Http\Controllers\ProcurementPaymentController::class, 'printDocument'])->name('procurement-packages.payment.print-document');
        Route::post('/procurement-packages/{package}/payment/complete', [\App\Http\Controllers\ProcurementPaymentController::class, 'complete'])->name('procurement-packages.payment.complete');
    
        // Dokumen Perjalanan Dinas
        Route::get('/packages/{package}/travel-orders/{travelOrder}/export-word/{type}', [\App\Http\Controllers\TravelOrderDocumentController::class, 'exportWord'])->name('packages.travel-orders.export-word');
        Route::get('/packages/{package}/travel-orders/{travelOrder}/print-html/{type}', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printHtml'])->name('packages.travel-orders.print-html');
        Route::put('/packages/{package}/travel-orders/{travelOrder}/personnels/{personnel}/update-biaya', [\App\Http\Controllers\TravelOrderController::class, 'updateBiaya'])->name('packages.travel-orders.personnels.update-biaya');
        Route::get('/packages/{package}/travel-orders/{travelOrder}/personnels/{personnel}/print-kuitansi', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printKuitansi'])->name('packages.travel-orders.personnels.print-kuitansi');
        Route::get('/packages/{package}/travel-orders/{travelOrder}/print-kuitansi', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printKuitansiAll'])->name('packages.travel-orders.print-kuitansi');
        Route::get('/packages/{package}/travel-orders/{travelOrder}/print-pengeluaran-riil', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printPengeluaranRiil'])->name('packages.travel-orders.print-pengeluaran-riil');
        Route::get('/packages/{package}/travel-orders/{travelOrder}/print-laporan', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printLaporan'])->name('packages.travel-orders.print-laporan');
    };

    // Staff Packages Module
    Route::middleware(['role:Staff'])->prefix('staf')->name('staf.')->group(function () {
        Route::get('packages', [\App\Http\Controllers\Staff\PackageController::class, 'index'])->name('packages.index');
        Route::get('packages/import', [\App\Http\Controllers\ImportBatchController::class, 'index'])->name('packages.import');
        Route::post('packages/import', [\App\Http\Controllers\ImportBatchController::class, 'store'])->name('packages.import.store');
        Route::get('packages/import/{batch}', [\App\Http\Controllers\ImportBatchController::class, 'show'])->name('packages.import.show');
        
        Route::get('packages/create', [\App\Http\Controllers\Staff\PackageController::class, 'create'])->name('packages.create');
        Route::get('packages/{package}', [\App\Http\Controllers\Staff\PackageController::class, 'show'])->name('packages.show');
        Route::get('packages/{package}/edit', [\App\Http\Controllers\Staff\PackageController::class, 'edit'])->name('packages.edit');
        Route::post('packages', [\App\Http\Controllers\PackageController::class, 'store'])->name('packages.store');
        Route::put('packages/{package}', [\App\Http\Controllers\PackageController::class, 'update'])->name('packages.update');
        Route::delete('packages/{package}', [\App\Http\Controllers\PackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('packages/{package}/submit', [\App\Http\Controllers\PackageController::class, 'submit'])->name('packages.submit');

        // Kalender Staf
        Route::get('kalender', [\App\Http\Controllers\Staff\CalendarController::class, 'index'])->name('kalender.index');

        // SPPD (Staff) — daftar pengajuan
        Route::get('sppd', [\App\Http\Controllers\Staff\SppdController::class, 'index'])->name('sppd.index');
        Route::get('sppd/create', [\App\Http\Controllers\Staff\SppdController::class, 'create'])->name('sppd.create');

        // Perjalanan Dinas (Staff)
        Route::get('packages/{package}/travel-orders/create', [\App\Http\Controllers\Staff\TravelOrderController::class, 'create'])->name('packages.travel-orders.create');
        Route::post('packages/{package}/travel-orders', [\App\Http\Controllers\Staff\TravelOrderController::class, 'store'])->name('packages.travel-orders.store');
        Route::get('packages/{package}/travel-orders/{travelOrder}/edit', [\App\Http\Controllers\Staff\TravelOrderController::class, 'edit'])->name('packages.travel-orders.edit');
        Route::put('packages/{package}/travel-orders/{travelOrder}', [\App\Http\Controllers\Staff\TravelOrderController::class, 'update'])->name('packages.travel-orders.update');
        Route::post('packages/{package}/travel-orders/{travelOrder}/submit', [\App\Http\Controllers\Staff\TravelOrderController::class, 'submit'])->name('packages.travel-orders.submit');
        Route::post('packages/{package}/travel-orders/{travelOrder}/withdraw', [\App\Http\Controllers\Staff\TravelOrderController::class, 'withdraw'])->name('packages.travel-orders.withdraw');
        Route::post('packages/{package}/travel-orders/{travelOrder}/spj', [\App\Http\Controllers\Staff\TravelSpjController::class, 'store'])->name('packages.travel-orders.spj.store');
        Route::post('packages/{package}/travel-orders/{travelOrder}/spj/submit', [\App\Http\Controllers\Staff\TravelSpjController::class, 'submit'])->name('packages.travel-orders.spj.submit');
        Route::post('packages/{package}/travel-orders/{travelOrder}/spj/withdraw', [\App\Http\Controllers\Staff\TravelSpjController::class, 'withdraw'])->name('packages.travel-orders.spj.withdraw');
        Route::post('packages/{package}/travel-orders/{travelOrder}/laporan', [\App\Http\Controllers\Staff\TravelReportController::class, 'store'])->name('packages.travel-orders.laporan.store');
        Route::post('packages/{package}/travel-orders/{travelOrder}/laporan/generate', [\App\Http\Controllers\Staff\TravelReportController::class, 'generate'])->name('packages.travel-orders.laporan.generate');
        Route::post('packages/{package}/travel-orders/{travelOrder}/laporan/prompt', [\App\Http\Controllers\Staff\TravelReportController::class, 'updatePrompt'])->name('packages.travel-orders.laporan.prompt');
        Route::get('packages/{package}/travel-orders/{travelOrder}', [\App\Http\Controllers\Staff\TravelOrderController::class, 'show'])->name('packages.travel-orders.show');

        // Travel Order Documents (Staff)
        Route::get('packages/{package}/travel-orders/{travelOrder}/export-word/{type}', [\App\Http\Controllers\TravelOrderDocumentController::class, 'exportWord'])->name('packages.travel-orders.export-word');
        Route::get('packages/{package}/travel-orders/{travelOrder}/print-html/{type}', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printHtml'])->name('packages.travel-orders.print-html');
        Route::get('packages/{package}/travel-orders/{travelOrder}/personnels/{personnel}/print-kuitansi', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printKuitansi'])->name('packages.travel-orders.personnels.print-kuitansi');
        Route::get('packages/{package}/travel-orders/{travelOrder}/print-kuitansi', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printKuitansiAll'])->name('packages.travel-orders.print-kuitansi');
        Route::get('packages/{package}/travel-orders/{travelOrder}/print-pengeluaran-riil', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printPengeluaranRiil'])->name('packages.travel-orders.print-pengeluaran-riil');
        Route::get('packages/{package}/travel-orders/{travelOrder}/print-laporan', [\App\Http\Controllers\TravelOrderDocumentController::class, 'printLaporan'])->name('packages.travel-orders.print-laporan');

        // Lembur (Staff Print)
        Route::get('packages/{package}/overtimes/{overtime}/print/{type}', [\App\Http\Controllers\OvertimeController::class, 'print'])->name('packages.overtimes.print');

        // Arsip Dokumen
        Route::get('arsip', [\App\Http\Controllers\ArsipController::class, 'index'])->name('arsip.index');
    });
    // Kabid Packages Module
    Route::middleware(['role:Kabid'])->prefix('kabid')->name('kabid.')->group(function () use ($sharedProcurementRoutes) {
        $sharedProcurementRoutes();
        Route::get('packages', [\App\Http\Controllers\Kabid\PackageController::class, 'index'])->name('packages.index');
        Route::get('packages/{package}', [\App\Http\Controllers\Kabid\PackageController::class, 'show'])->name('packages.show');
        Route::get('monev', [\App\Http\Controllers\Kabid\MonevController::class, 'index'])->name('monev.index');
        Route::get('monev/{subActivity}', [\App\Http\Controllers\Kabid\MonevController::class, 'show'])->name('monev.show');
        Route::get('sppd', [\App\Http\Controllers\Kabid\SppdController::class, 'index'])->name('sppd.index');
        Route::get('swakelola', [\App\Http\Controllers\Kabid\SwakelolaController::class, 'index'])->name('swakelola.index');
        Route::get('dikecualikan', [\App\Http\Controllers\Kabid\DikecualikanController::class, 'index'])->name('dikecualikan.index');
        Route::get('penyedia', [\App\Http\Controllers\Kabid\PenyediaController::class, 'index'])->name('penyedia.index');
        Route::get('buku-register', [BukuRegisterController::class, 'index'])->name('buku-register.index');
        // Lembur (Overtime) untuk Kabid
        Route::get('packages/{package}/overtimes/{month}', [\App\Http\Controllers\Kabid\OvertimeController::class, 'show'])->name('packages.overtimes.show');
        Route::put('packages/{package}/overtimes/{overtime}', [\App\Http\Controllers\Kabid\OvertimeController::class, 'update'])->name('packages.overtimes.update');
        Route::post('packages/{package}/overtimes/{overtime}/ajax', [\App\Http\Controllers\Kabid\OvertimeController::class, 'updateAjax'])->name('packages.overtimes.updateAjax');
        Route::post('packages/{package}/overtimes/{overtime}/autofill', [\App\Http\Controllers\Kabid\OvertimeController::class, 'autoFill'])->name('packages.overtimes.autoFill');
        Route::post('packages/{package}/overtimes/{overtime}/reset', [\App\Http\Controllers\Kabid\OvertimeController::class, 'resetMonth'])->name('packages.overtimes.reset');
        Route::get('packages/{package}/overtimes/{overtime}/print/{type}', [\App\Http\Controllers\Kabid\OvertimeController::class, 'print'])->name('packages.overtimes.print');
        Route::post('packages/{package}/overtimes/{month}/lock', [\App\Http\Controllers\Kabid\OvertimeController::class, 'lock'])->name('packages.overtimes.lock');
        Route::post('packages/{package}/overtimes/{month}/unlock', [\App\Http\Controllers\Kabid\OvertimeController::class, 'unlock'])->name('packages.overtimes.unlock');
        Route::post('packages/{package}/overtimes/{overtime}/details/{detail}/rates', [\App\Http\Controllers\Kabid\OvertimeController::class, 'updateRates'])->name('packages.overtimes.update_rates');
        // Halaman gabungan lama — kini diarahkan ke daftar Penyedia (navigasi kabid per jenis pengadaan).
        Route::get('procurement-packages', fn () => redirect()->route('kabid.penyedia.index'))->name('procurement-packages.index');
        Route::get('procurement-packages/{package}', [\App\Http\Controllers\Kabid\ProcurementPackageController::class, 'show'])->name('procurement-packages.show');
        // Kabid hanya meninjau SPPD — tidak boleh membuat/mengedit perjalanan dinas.
        Route::get('packages/{package}/travel-orders/{travelOrder}', [\App\Http\Controllers\Kabid\TravelOrderController::class, 'show'])->name('packages.travel-orders.show');
        // Review SPPD (Kabid)
        Route::post('packages/{package}/travel-orders/{travelOrder}/approve', [\App\Http\Controllers\Kabid\TravelOrderController::class, 'approve'])->name('packages.travel-orders.approve');
        Route::post('packages/{package}/travel-orders/{travelOrder}/revise', [\App\Http\Controllers\Kabid\TravelOrderController::class, 'revise'])->name('packages.travel-orders.revise');
        Route::post('packages/{package}/travel-orders/{travelOrder}/reject', [\App\Http\Controllers\Kabid\TravelOrderController::class, 'reject'])->name('packages.travel-orders.reject');
        // Review SPJ (Kabid) — setujui / revisi
        Route::post('packages/{package}/travel-orders/{travelOrder}/spj/approve', [\App\Http\Controllers\Kabid\TravelOrderController::class, 'approveSpj'])->name('packages.travel-orders.spj.approve');
        Route::post('packages/{package}/travel-orders/{travelOrder}/spj/revise', [\App\Http\Controllers\Kabid\TravelOrderController::class, 'reviseSpj'])->name('packages.travel-orders.spj.revise');
        // Arsip Dokumen
        Route::get('arsip', [\App\Http\Controllers\ArsipController::class, 'index'])->name('arsip.index');
        // Kalender Kegiatan
        Route::get('kalender', [\App\Http\Controllers\Kabid\CalendarController::class, 'index'])->name('kalender.index');
        Route::put('procurement-packages/{package}/items', [\App\Http\Controllers\Kabid\ProcurementPackageController::class, 'updateItems'])->name('procurement-packages.items.update');
        Route::put('procurement-packages/{package}/contract', [\App\Http\Controllers\Kabid\ProcurementPackageController::class, 'updateContract'])->name('procurement-packages.contract.update');
        Route::put('procurement-packages/{package}/specification', [\App\Http\Controllers\Kabid\ProcurementPackageController::class, 'updateSpecification'])->name('procurement-packages.specification.update');
        Route::post('procurement-packages/{package}/specification/generate', [\App\Http\Controllers\Kabid\ProcurementPackageController::class, 'generateSpecification'])->name('procurement-packages.specification.generate');
        Route::post('procurement-packages/{package}/specification/prompt', [\App\Http\Controllers\Kabid\ProcurementPackageController::class, 'updatePrompt'])->name('procurement-packages.specification.prompt');
        Route::post('procurement-packages/{package}/price-references/fetch', [\App\Http\Controllers\Kabid\PriceReferenceController::class, 'fetchFromCatalog'])->name('procurement-packages.price-references.fetch');
        Route::post('procurement-packages/{package}/price-references', [\App\Http\Controllers\Kabid\PriceReferenceController::class, 'store'])->name('procurement-packages.price-references.store');
        Route::put('procurement-packages/{package}/price-references/{priceReference}', [\App\Http\Controllers\Kabid\PriceReferenceController::class, 'update'])->name('procurement-packages.price-references.update');
        Route::delete('procurement-packages/{package}/price-references/{priceReference}', [\App\Http\Controllers\Kabid\PriceReferenceController::class, 'destroy'])->name('procurement-packages.price-references.destroy');
        Route::put('procurement-packages/{package}/request', [\App\Http\Controllers\Kabid\ProcurementPackageController::class, 'updateRequest'])->name('procurement-packages.request.update');
        Route::post('procurement-packages/{package}/finish-preparation', [\App\Http\Controllers\Kabid\ProcurementPackageController::class, 'completePreparation'])->name('procurement-packages.finish-preparation');
        Route::get('procurement-packages/{package}/procurement-process', fn ($package) => redirect()->route('kabid.procurement-packages.procurement-process.show', $package));
        Route::get('procurement-packages/{package}/process', [\App\Http\Controllers\Kabid\ProcurementProcessController::class, 'show'])->name('procurement-packages.procurement-process.show');
        Route::put('procurement-packages/{package}/process/order', [\App\Http\Controllers\Kabid\ProcurementProcessController::class, 'updateOrder'])->name('procurement-packages.procurement-process.order.update');
        Route::put('procurement-packages/{package}/process/vendor', [\App\Http\Controllers\Kabid\ProcurementProcessController::class, 'updateVendor'])->name('procurement-packages.procurement-process.vendor.update');
        Route::post('procurement-packages/{package}/process/start-execution', [\App\Http\Controllers\Kabid\ProcurementProcessController::class, 'startExecution'])->name('procurement-packages.procurement-process.start-execution');
        Route::get('procurement-packages/{package}/execution', [\App\Http\Controllers\Kabid\ProcurementExecutionController::class, 'show'])->name('procurement-packages.execution.show');
        Route::post('procurement-packages/{package}/execution/addendum', [\App\Http\Controllers\Kabid\ProcurementExecutionController::class, 'storeAddendum'])->name('procurement-packages.execution.addendum');
        Route::post('procurement-packages/{package}/execution/finish', [\App\Http\Controllers\Kabid\ProcurementExecutionController::class, 'finishWork'])->name('procurement-packages.execution.finish');
        Route::get('procurement-packages/{package}/payment', [\App\Http\Controllers\Kabid\ProcurementPaymentController::class, 'show'])->name('procurement-packages.payment.show');
        Route::post('procurement-packages/{package}/payment/complete', [\App\Http\Controllers\Kabid\ProcurementPaymentController::class, 'complete'])->name('procurement-packages.payment.complete');
    });
    // Admin Procurement Packages Module
    Route::middleware(['role:Admin|Super Admin'])->prefix('admin')->name('admin.')->group(function () use ($sharedProcurementRoutes) {
        $sharedProcurementRoutes();
        // Import (admin)
        Route::get('packages/import', [\App\Http\Controllers\ImportBatchController::class, 'index'])->name('packages.import.index');
        Route::post('packages/import', [\App\Http\Controllers\ImportBatchController::class, 'store'])->name('packages.import.store');
        Route::get('packages/import/{batch}', [\App\Http\Controllers\ImportBatchController::class, 'show'])->name('packages.import.show');

        // Packages CRUD (admin)
        Route::get('packages', [\App\Http\Controllers\PackageController::class, 'index'])->name('packages.index');
        Route::get('packages/create', [\App\Http\Controllers\PackageController::class, 'create'])->name('packages.create');
        Route::post('packages', [\App\Http\Controllers\PackageController::class, 'store'])->name('packages.store');
        Route::get('packages/{package}', [\App\Http\Controllers\PackageController::class, 'show'])->name('packages.show');
        Route::get('packages/{package}/edit', [\App\Http\Controllers\PackageController::class, 'edit'])->name('packages.edit');
        Route::put('packages/{package}', [\App\Http\Controllers\PackageController::class, 'update'])->name('packages.update');
        Route::delete('packages/{package}', [\App\Http\Controllers\PackageController::class, 'destroy'])->name('packages.destroy');
        Route::get('packages/{package}/procurement', [\App\Http\Controllers\PackageController::class, 'procurement'])->name('packages.procurement');
        Route::put('packages/{package}/procurement', [\App\Http\Controllers\PackageController::class, 'updateProcurement'])->name('packages.procurement.update');
        Route::get('packages/program/{program}', [\App\Http\Controllers\PackageController::class, 'byProgram'])->name('packages.program');

        // Perjalanan dinas: Admin hanya dapat melihat dan mengoreksi biaya rampung SPJ.
        Route::get('packages/{package}/travel-orders/{travelOrder}', [\App\Http\Controllers\Admin\TravelOrderController::class, 'show'])->name('packages.travel-orders.show');
        Route::get('packages/{package}/travel-orders/{travelOrder}/koreksi-biaya', [\App\Http\Controllers\Admin\TravelOrderController::class, 'editBiaya'])->name('packages.travel-orders.koreksi-biaya.edit');
        Route::put('packages/{package}/travel-orders/{travelOrder}/koreksi-biaya', [\App\Http\Controllers\Admin\TravelOrderController::class, 'updateBiaya'])->name('packages.travel-orders.koreksi-biaya.update');

        Route::get('procurement-packages/{package}', [\App\Http\Controllers\Admin\ProcurementPackageController::class, 'show'])->name('procurement-packages.show');
        Route::post('procurement-packages/{package}/unlock', [\App\Http\Controllers\Admin\ProcurementPackageController::class, 'unlock'])->name('procurement-packages.unlock');
        Route::post('procurement-packages/{package}/unlock-selection', [\App\Http\Controllers\Admin\ProcurementPackageController::class, 'unlockSelection'])->name('procurement-packages.unlock-selection');
        Route::post('procurement-packages/{package}/unlock-execution', [\App\Http\Controllers\Admin\ProcurementPackageController::class, 'unlockExecution'])->name('procurement-packages.unlock-execution');
        Route::post('procurement-packages/{package}/unlock-payment', [\App\Http\Controllers\Admin\ProcurementPackageController::class, 'unlockPayment'])->name('procurement-packages.unlock-payment');
        Route::get('procurement-packages/{package}/payment', [\App\Http\Controllers\Admin\ProcurementPackageController::class, 'payment'])->name('procurement-packages.payment');

        Route::get('procurement-packages/{package}/procurement-process', fn ($package) => redirect()->route('admin.procurement-packages.procurement-process.show', $package));
        Route::get('procurement-packages/{package}/process', [\App\Http\Controllers\ProcurementProcessController::class, 'show'])->name('procurement-packages.procurement-process.show');

        Route::get('procurement-packages', fn () => redirect()->route('admin.penyedia.index'))->name('procurement-packages.index');
        Route::get('penyedia', [\App\Http\Controllers\Admin\PenyediaController::class, 'index'])->name('penyedia.index');
        Route::get('swakelola', [\App\Http\Controllers\Admin\SwakelolaController::class, 'index'])->name('swakelola.index');
        Route::get('dikecualikan', [\App\Http\Controllers\Admin\DikecualikanController::class, 'index'])->name('dikecualikan.index');
        Route::get('schedules', [\App\Http\Controllers\ScheduleController::class, 'index'])->name('schedules.index');

        // Lembur (Admin Print)
        Route::get('packages/{package}/overtimes/{overtime}/print/{type}', [\App\Http\Controllers\OvertimeController::class, 'print'])->name('packages.overtimes.print');

        // Buku Register
        Route::get('buku-register', [BukuRegisterController::class, 'index'])->name('buku-register.index');

        // Arsip Dokumen
        Route::get('arsip', [\App\Http\Controllers\ArsipController::class, 'index'])->name('arsip.index');

        // Manajemen User
        Route::get('users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'editPassword'])->name('users.reset-password');
        Route::put('users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('users.update-password');
        Route::patch('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });

    Route::get('/schedules', [\App\Http\Controllers\ScheduleController::class, 'index'])->name('schedules.index');

    // Resource Routes
    Route::middleware(['role:Admin|Super Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('skpds', SkpdController::class)->except(['show']);
        Route::resource('programs', ProgramController::class)->except(['show', 'destroy']);
        Route::resource('activities', ActivityController::class)->except(['show', 'destroy']);
        Route::resource('sub-activities', SubActivityController::class)->except(['show', 'destroy']);
        Route::resource('accounts', AccountController::class)->except(['show', 'destroy']);
        Route::post('fiscal-years/{fiscalYear}/activate', [FiscalYearController::class, 'activate'])->name('fiscal-years.activate');
        Route::resource('fiscal-years', FiscalYearController::class)->except(['show', 'edit', 'update', 'destroy']);
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->except(['show']);
    });
    

    // Packages & Imports Routes
    Route::get('packages/import', [ImportBatchController::class, 'index'])->name('packages.import.index');
    Route::post('packages/import', [ImportBatchController::class, 'store'])->name('packages.import.store');
    Route::get('/packages/import/{batch}', [ImportBatchController::class, 'show'])->name('packages.import.show');    
    
    Route::get('packages/program/{program}', [PackageController::class, 'byProgram'])->name('packages.program');
    Route::get('packages/program-menu', [PackageController::class, 'programMenu'])->name('packages.program-menu');
    
    Route::get('packages/{package}/procurement', [PackageController::class, 'procurement'])->name('packages.procurement');

    Route::get('control-cards/{activity}/print', [ControlCardController::class, 'print'])->name('control-cards.print');

    // Monev
    Route::middleware(['role:Admin|Super Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('monev', [MonevController::class, 'index'])->name('monev.index');
        Route::get('monev/{subActivity}', [MonevController::class, 'show'])->name('monev.show');
        Route::get('monev/{subActivity}/print', [MonevController::class, 'print'])->name('monev.print');
    });
    
    // SBU Management
    Route::middleware(['role:Admin|Super Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('sbu-transport-rates', App\Http\Controllers\SbuTransportRateController::class)->except(['show']);
        Route::resource('sbu-uang-harians', App\Http\Controllers\SbuUangHarianController::class)->except(['show']);
        Route::resource('sbu-penginapans', App\Http\Controllers\SbuPenginapanController::class)->except(['show']);
        Route::resource('sbu-tiket-pesawats', App\Http\Controllers\SbuTiketPesawatController::class)->except(['show']);
        Route::resource('sbu-lemburs', App\Http\Controllers\SbuLemburController::class)->except(['show', 'create', 'edit']);
    });
    
    // Procurement Packages & sub-resources
    Route::resource('packages', PackageController::class);

});

require __DIR__.'/auth.php';



