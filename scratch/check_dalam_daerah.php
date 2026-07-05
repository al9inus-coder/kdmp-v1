<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\TravelOrder::all() as $to) {
    if (strtolower($to->tipe_perjalanan) == 'dalam daerah' || strtolower($to->tipe_perjalanan) == 'dalam_daerah') {
        echo "ID: {$to->id}, Tanggal: {$to->tanggal_berangkat} sd {$to->tanggal_kembali}\n";
        foreach ($to->personnels as $p) {
            $est = $p->getEstimatedCosts();
            echo "  - {$p->employee->nama} - " . json_encode($est) . "\n";
        }
    }
}
