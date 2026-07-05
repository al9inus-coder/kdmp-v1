<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$o = \App\Models\TravelOrder::find(6);
foreach($o->personnels as $p) {
    $est = $p->getEstimatedCosts();
    $p->update([
        'uang_harian' => $est['uang_harian'], 
        'biaya_penginapan' => $est['biaya_penginapan'],
        'biaya_transport' => $est['biaya_transport'],
        'biaya_taksi' => $est['biaya_taksi'] ?? 0,
        'biaya_representasi' => $est['biaya_representasi']
    ]);
}
echo "Done.";
