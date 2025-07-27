<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TravelRequest;

class CheckSppd extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'check:sppd {id=38}';

    /**
     * The console command description.
     */
    protected $description = 'Check SPPD data by ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');

        $sppd = TravelRequest::find($id);

        if (!$sppd) {
            $this->error("SPPD ID {$id} tidak ditemukan!");
            return 1;
        }

        $this->info("=== DATA SPPD ID {$id} ===");
        $this->line("Kode SPPD: " . $sppd->kode_sppd);
        $this->line("Tujuan: " . $sppd->tujuan);
        $this->line("User: " . ($sppd->user->name ?? 'N/A'));
        $this->line("Status: " . $sppd->status);

        $this->info("=== RINCIAN BIAYA ===");
        $this->line("Biaya Transport: " . $sppd->biaya_transport . " (Type: " . gettype($sppd->biaya_transport) . ")");
        $this->line("Biaya Penginapan: " . $sppd->biaya_penginapan . " (Type: " . gettype($sppd->biaya_penginapan) . ")");
        $this->line("Uang Harian: " . $sppd->uang_harian . " (Type: " . gettype($sppd->uang_harian) . ")");
        $this->line("Biaya Lainnya: " . $sppd->biaya_lainnya . " (Type: " . gettype($sppd->biaya_lainnya) . ")");
        $this->line("Total Biaya: " . $sppd->total_biaya . " (Type: " . gettype($sppd->total_biaya) . ")");

        $this->info("=== FORMATTED ===");
        $this->line("Transport: Rp " . number_format($sppd->biaya_transport ?? 0, 0, ',', '.'));
        $this->line("Penginapan: Rp " . number_format($sppd->biaya_penginapan ?? 0, 0, ',', '.'));
        $this->line("Uang Harian: Rp " . number_format($sppd->uang_harian ?? 0, 0, ',', '.'));
        $this->line("Lainnya: Rp " . number_format($sppd->biaya_lainnya ?? 0, 0, ',', '.'));
        $this->line("TOTAL: Rp " . number_format($sppd->total_biaya ?? 0, 0, ',', '.'));

        return 0;
    }
}
