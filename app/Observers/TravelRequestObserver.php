<?php

namespace App\Observers;

use App\Models\TravelRequest;

class TravelRequestObserver
{
    public function creating(TravelRequest $model)
    {
        // Dihapus: kode_sppd hanya diisi saat approve selesai
    }
}
