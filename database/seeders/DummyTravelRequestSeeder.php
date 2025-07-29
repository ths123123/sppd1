<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Carbon\Carbon;

class DummyTravelRequestSeeder extends Seeder
{
    public function run(): void
    {
        $kasubbag = User::where('email', 'kasubbag1@kpu.go.id')->first();
        $staffs = User::where('role', 'staff')->pluck('id')->toArray();
        
        if (!$kasubbag || count($staffs) < 3) {
            if ($this->command) {
                $this->command->error('Kasubbag atau staff tidak ditemukan. Pastikan UserRoleSeeder sudah dijalankan.');
            }
            return;
        }

        $tujuanList = [
            'KPU Provinsi Jawa Barat, Bandung',
            'KPU RI, Jakarta',
            'KPU Provinsi Jawa Tengah, Semarang',
            'KPU Provinsi Jawa Timur, Surabaya',
            'KPU Provinsi Banten, Serang',
            'KPU Provinsi Sumatera Utara, Medan',
            'KPU Provinsi Bali, Denpasar',
            'KPU Provinsi Kalimantan Timur, Samarinda',
            'KPU Provinsi Sulawesi Selatan, Makassar',
            'KPU Provinsi Papua, Jayapura',
        ];
        
        $keperluanList = [
            'Rapat Koordinasi Persiapan Pemilu',
            'Bimbingan Teknis Pengelolaan Data Pemilih',
            'Sosialisasi Peraturan KPU Terbaru',
            'Rapat Evaluasi Tahapan Pilkada',
            'Workshop Penguatan SDM KPU',
            'Rapat Penyusunan Anggaran',
            'Monitoring dan Evaluasi Logistik',
            'Pelatihan Sistem Informasi KPU',
            'Rapat Konsolidasi Nasional',
            'Sosialisasi Pemilu Serentak',
        ];
        
        $transportasiList = [
            'Kereta Api Eksekutif',
            'Kereta Api Ekonomi',
            'Pesawat',
            'Bus',
            'Mobil Dinas',
        ];
        
        $penginapanList = [
            'Hotel Santika',
            'Hotel Ibis',
            'Hotel Aston',
            'Hotel Amaris',
            'Hotel Harris',
            'Hotel Grand Mercure',
            'Hotel Swiss-Belhotel',
            'Hotel Horison',
            'Hotel Aryaduta',
            'Hotel Novotel',
        ];

        $tempatBerangkatList = [
            'KPU Kabupaten Cirebon',
            'Kantor KPU Kabupaten Cirebon',
            'KPU Kab. Cirebon',
        ];

        $sumberDanaList = [
            'APBN',
            'APBD',
            'Dana Dekonsentrasi',
        ];

        $service = app(TravelRequestService::class);
        $now = Carbon::now();

        for ($i = 0; $i < 20; $i++) {
            $kodeSppd = $service->generateKodeSppd();
            $tujuan = $tujuanList[$i % count($tujuanList)];
            $keperluan = $keperluanList[$i % count($keperluanList)];
            $transportasi = $transportasiList[array_rand($transportasiList)];
            $penginapan = $penginapanList[array_rand($penginapanList)];
            $tempatBerangkat = $tempatBerangkatList[array_rand($tempatBerangkatList)];
            $sumberDana = $sumberDanaList[array_rand($sumberDanaList)];
            
            $tanggal_berangkat = $now->copy()->addDays($i * 2);
            $lama_perjalanan = rand(2, 5);
            $tanggal_kembali = $tanggal_berangkat->copy()->addDays($lama_perjalanan - 1);
            
            $biaya_transport = rand(300000, 1500000);
            $biaya_penginapan = rand(400000, 2000000);
            $uang_harian = rand(200000, 600000);
            $biaya_lainnya = rand(50000, 300000);
            $total_biaya = $biaya_transport + $biaya_penginapan + $uang_harian + $biaya_lainnya;

            $travelRequest = TravelRequest::create([
                'kode_sppd' => $kodeSppd,
                'user_id' => $kasubbag->id,
                'tujuan' => $tujuan,
                'keperluan' => $keperluan,
                'tempat_berangkat' => $tempatBerangkat,
                'tanggal_berangkat' => $tanggal_berangkat->format('Y-m-d'),
                'tanggal_kembali' => $tanggal_kembali->format('Y-m-d'),
                'lama_perjalanan' => $lama_perjalanan,
                'transportasi' => $transportasi,
                'tempat_menginap' => $penginapan,
                'biaya_transport' => $biaya_transport,
                'biaya_penginapan' => $biaya_penginapan,
                'uang_harian' => $uang_harian,
                'biaya_lainnya' => $biaya_lainnya,
                'total_biaya' => $total_biaya,
                'sumber_dana' => $sumberDana,
                'status' => 'in_review',
                'current_approval_level' => 1,
                'catatan_pemohon' => 'Mohon persetujuan untuk perjalanan dinas ini.',
                'catatan_approval' => null,
                'is_urgent' => rand(0, 1) == 1,
                'nomor_surat_tugas' => null,
                'tanggal_surat_tugas' => null,
                'submitted_at' => now(),
                'approved_at' => null,
            ]);

            // Tambahkan 3 peserta random
            shuffle($staffs);
            $participants = array_slice($staffs, 0, 3);
            $travelRequest->participants()->sync($participants);
            
            if ($this->command) {
                $this->command->info("SPPD {$kodeSppd} berhasil dibuat dengan {$lama_perjalanan} peserta.");
            }
        }
        
        if ($this->command) {
            $this->command->info('Berhasil membuat 20 data dummy SPPD.');
        }
    }

    public static function runStatic(): void
    {
        (new static())->run();
    }

    public static function createRevisionTestData(): void
    {
        $kasubbag = User::where('email', 'kasubbag1@kpu.go.id')->first();
        $staffs = User::where('role', 'staff')->pluck('id')->toArray();
        
        if (!$kasubbag || count($staffs) < 3) {
            return;
        }

        $service = app(TravelRequestService::class);
        
        // Buat 5 SPPD yang siap untuk revisi oleh sekretaris
        for ($i = 0; $i < 5; $i++) {
            $kodeSppd = $service->generateKodeSppd();
            
            $travelRequest = TravelRequest::create([
                'kode_sppd' => $kodeSppd,
                'user_id' => $kasubbag->id,
                'tujuan' => 'KPU Provinsi Jawa Barat, Bandung',
                'keperluan' => 'Rapat Koordinasi Persiapan Pemilu',
                'tempat_berangkat' => 'KPU Kabupaten Cirebon',
                'tanggal_berangkat' => now()->addDays($i + 1)->format('Y-m-d'),
                'tanggal_kembali' => now()->addDays($i + 3)->format('Y-m-d'),
                'lama_perjalanan' => 3,
                'transportasi' => 'Kereta Api Eksekutif',
                'tempat_menginap' => 'Hotel Santika',
                'biaya_transport' => 500000,
                'biaya_penginapan' => 800000,
                'uang_harian' => 300000,
                'biaya_lainnya' => 100000,
                'total_biaya' => 1700000,
                'sumber_dana' => 'APBN',
                'status' => 'in_review',
                'current_approval_level' => 1, // Siap untuk approval sekretaris
                'catatan_pemohon' => 'Mohon persetujuan untuk perjalanan dinas ini.',
                'catatan_approval' => null,
                'is_urgent' => false,
                'nomor_surat_tugas' => null,
                'tanggal_surat_tugas' => null,
                'submitted_at' => now(),
                'approved_at' => null,
            ]);

            // Tambahkan 3 peserta
            shuffle($staffs);
            $participants = array_slice($staffs, 0, 3);
            $travelRequest->participants()->sync($participants);
        }
    }
} 