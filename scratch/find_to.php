<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\TravelOrder::all() as $to) {
    echo $to->id . ': ' . $to->tanggal_berangkat . ' sd ' . $to->tanggal_kembali . ' - ' . $to->tipe_perjalanan . "\n";
}
