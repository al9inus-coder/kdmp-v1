<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$o = \App\Models\TravelOrder::where('tipe_perjalanan', 'dalam_daerah')->first();
if ($o) {
    echo "Found order: " . $o->id . "\n";
    foreach ($o->personnels as $p) {
        $est = $p->getEstimatedCosts();
        echo "Personnel: {$p->employee->nama}\n";
        echo "Cost: " . json_encode($est) . "\n";
        echo "Nights: " . max(0, $p->getDays() - 1) . "\n";
    }
}
