<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Kabid\TravelOrderController as KabidTravelOrderController;

/**
 * Modul Perjalanan Dinas untuk Staff.
 * Logika identik dengan Kabid; hanya prefix view & route yang berbeda (staf).
 */
class TravelOrderController extends KabidTravelOrderController
{
    protected string $rolePrefix = 'staf';

    protected bool $submissionFlow = true;
}
