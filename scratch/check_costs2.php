<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$o = \App\Models\TravelOrder::find(9); // Package ID 9 as mentioned earlier, wait maybe it's ID 9
foreach(\App\Models\TravelOrder::all() as $to) {
    echo "ID: {$to->id}, Tipe: {$to->tipe_perjalanan}\n";
    foreach ($to->personnels as $p) {
        $est = $p->getEstimatedCosts();
        echo "  - {$p->employee->nama} - " . json_encode($est) . " Nights: " . max(0, $p->getDays() - 1) . "\n";
    }
}
