<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\SbuPenginapanDalamDaerah;

$destinations = DB::table('sbu_transport_rates')->where('kategori', 'dalam_daerah')->select('tempat_tujuan')->distinct()->pluck('tempat_tujuan');
foreach ($destinations as $dest) {
    $rate = strtolower($dest) === 'bengkayang' ? 0 : 1;
    SbuPenginapanDalamDaerah::create([
        'tempat_tujuan' => $dest,
        'eselon_ii' => $rate * 500000,
        'eselon_iii' => $rate * 450000,
        'eselon_iv' => $rate * 400000,
        'golongan_i_ii' => $rate * 350000
    ]);
}
echo "Seeded successfully.\n";
