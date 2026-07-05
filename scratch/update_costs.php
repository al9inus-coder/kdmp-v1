<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\TravelPersonnel::all() as $p) {
    $est = $p->getEstimatedCosts();
    $p->update([
        'biaya_penginapan' => $est['biaya_penginapan'] ?? 0,
        'biaya_representasi' => $est['biaya_representasi'] ?? 0,
        'uang_harian' => $est['uang_harian'] ?? 0,
        'biaya_transport' => $est['biaya_transport'] ?? 0,
        'biaya_taksi' => $est['biaya_taksi'] ?? 0,
    ]);
    echo "Updated Personnel {$p->id}\n";
}
