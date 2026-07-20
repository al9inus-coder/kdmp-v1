<?php

use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\TechnicalSpecification;
use App\Models\TechnicalSpecificationItem;
use App\Models\ProcurementRequest;
use App\Models\PriceReference;
use App\Models\ProcurementProcess;
use App\Models\ProcurementPayment;
use App\Models\ProcurementAddendum;

$package = Package::where('nama_paket', 'Belanja Tagihan Listrik 11999')->first();

if (!$package) {
    echo "Paket tidak ditemukan.\n";
    exit;
}

$procurementPackage = $package->procurementPackage;

if ($procurementPackage) {
    echo "Ditemukan ProcurementPackage ID: {$procurementPackage->id}\n";
    
    // Delete child relations
    $techSpec = TechnicalSpecification::where('procurement_package_id', $procurementPackage->id)->first();
    if ($techSpec) {
        TechnicalSpecificationItem::where('technical_specification_id', $techSpec->id)->delete();
        $techSpec->delete();
        echo "TechnicalSpecification dihapus.\n";
    }
    
    ProcurementRequest::where('procurement_package_id', $procurementPackage->id)->delete();
    PriceReference::where('procurement_package_id', $procurementPackage->id)->delete();
    ProcurementProcess::where('procurement_package_id', $procurementPackage->id)->delete();
    ProcurementPayment::where('procurement_package_id', $procurementPackage->id)->delete();
    ProcurementAddendum::where('procurement_package_id', $procurementPackage->id)->delete();
    
    $procurementPackage->delete();
    echo "ProcurementPackage dihapus.\n";
}

$package->delete();
echo "Package dihapus.\n";
