<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employees = \App\Models\Employee::all();
foreach($employees as $employee) {
    $category = null;
    $jabatan = strtolower($employee->jabatan ?? '');
    $golongan = strtolower($employee->golongan ?? '');
    
    // Auto-detect based on user rules
    if (str_contains($jabatan, 'kepala dinas') || str_contains($jabatan, 'eselon ii')) {
        $category = 'Eselon II';
    } elseif (str_contains($jabatan, 'kepala bidang') || str_contains($jabatan, 'sekretaris') || str_contains($jabatan, 'eselon iii') || str_contains($golongan, 'iv') || str_contains($jabatan, 'jafung madya')) {
        $category = 'Eselon III, Gol. IV dan Jafung Madya';
    } elseif ($jabatan || $golongan) {
        $category = 'Eselon IV, Gol. III kebawah, P3K, Jafung, Non ASN';
    }
    
    if ($category) {
        $employee->update(['kategori_biaya' => $category]);
        echo "Updated {$employee->nama} to $category\n";
    }
}
echo "Done.";
