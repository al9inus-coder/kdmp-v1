<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\TravelPersonnel::all() as $p) {
    $est = $p->getEstimatedCosts();
    $p->update(['biaya_penginapan' => $est['biaya_penginapan'] ?? 0]);
    echo "Updated Personnel {$p->id}\n";
}
