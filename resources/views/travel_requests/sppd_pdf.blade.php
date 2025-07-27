<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SPD - {{ $travelRequest->kode_sppd }}</title>
    <style>
        @page { 
            size: A4 portrait; 
            margin: 1.2cm;
            @bottom-center {
                content: "Halaman " counter(page) " dari " counter(pages);
                font-size: 10pt;
                color: #666;
            }
        }
        body { 
            font-family: Arial, Calibri, sans-serif; 
            font-size: 10.5pt; 
            margin: 0; 
            line-height: 1.3;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
        }
        td, th { 
            border: 1px solid #000; 
            padding: 4px 6px; 
            vertical-align: top; 
        }
        .no-border { 
            border: none !important; 
        }
        .header-kiri { 
            font-weight: bold; 
            font-size: 22pt; 
            font-family: Arial, Calibri, sans-serif; 
            text-align: left; 
        }
        .header-kanan { 
            font-style: italic; 
            font-size: 12pt; 
            font-family: Arial, Calibri, sans-serif; 
            text-align: right; 
        }
        .judul-spd { 
            text-align: center; 
            font-weight: bold; 
            font-size: 18pt; 
            text-decoration: underline; 
            margin-top: 16px; 
            font-family: Arial, Calibri, sans-serif; 
        }
        .nomor-spd { 
            text-align: center; 
            font-size: 14pt; 
            margin-bottom: 12px; 
            font-family: Arial, Calibri, sans-serif; 
        }
        .underline { 
            text-decoration: underline; 
        }
        .bold { 
            font-weight: bold; 
        }
        .right { 
            text-align: right; 
        }
        .center { 
            text-align: center; 
        }
        .mt-2 { 
            margin-top: 12px; 
        }
        .mb-2 { 
            margin-bottom: 12px; 
        }
        .spacer { 
            height: 8px; 
        }
        .page-break {
            page-break-before: always;
        }
        .signature-space {
            height: 60px;
        }
        .small-text {
            font-size: 9pt;
        }
        .medium-text {
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <!-- HALAMAN 1: SPD UTAMA -->
    <!-- HEADER & LAMPIRAN -->
    <table class="no-border" style="width:100%; border:none; margin-bottom:0;">
        <tr>
            <td class="no-border header-kiri" style="width:60%; vertical-align:top;">
                KOMISI PEMILIHAN UMUM<br>KABUPATEN CIREBON
            </td>
            <td class="no-border header-kanan" style="width:40%; vertical-align:top;">
                Lampiran I<br>
                Peraturan Menteri Keuangan Republik Indonesia No. 113/PMK.05/2012<br>
                Tentang Perjalanan Dinas Jabatan Dalam Negeri Bagi<br>
                Pejabat Negara, Pegawai Negeri, dan Pegawai Tidak Tetap
                <br>
                <table class="no-border" style="border:none; font-size:12pt; margin-left:auto;">
                    <tr class="no-border"><td class="no-border">Lembar Ke</td><td class="no-border">:</td><td class="no-border">1</td></tr>
                    <tr class="no-border"><td class="no-border">Kode No.</td><td class="no-border">:</td><td class="no-border">SPD-001</td></tr>
                    <tr class="no-border"><td class="no-border">Nomor</td><td class="no-border">:</td><td class="no-border">{{ $travelRequest->kode_sppd }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="judul-spd">SURAT PERJALANAN DINAS (SPD)</div>
    @if($travelRequest->status === 'completed')
        <div class="nomor-spd">Nomor: {{ $travelRequest->kode_sppd }}</div>
    @endif

    <!-- TABEL UTAMA SPD HALAMAN 1 -->
    <table>
        <tr>
            <td style="width:2.5em; text-align:center; font-weight:bold;">1</td>
            <td style="width:22em;">Pejabat Pembuat Komitmen</td>
            <td style="width:1em; text-align:center;">:</td>
            <td>{{ $travelRequest->ppk_nama ?? 'Hendra Gunawan, S.IP., M.Si' }}<br>NIP. {{ $travelRequest->ppk_nip ?? '198402132009121001' }}</td>
        </tr>
        <tr>
            <td style="text-align:center; font-weight:bold;">2</td>
            <td>Nama/NIP Pegawai yang melaksanakan Perjalanan Dinas</td>
            <td style="text-align:center;">:</td>
            <td>{{ $travelRequest->user->name ?? '-' }}<br>NIP. {{ $travelRequest->user->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td style="text-align:center; font-weight:bold;">3</td>
            <td>
                <div>a. Pangkat dan Golongan</div>
                <div>b. Jabatan/Instansi</div>
                <div>c. Tingkat Biaya Perjalanan Dinas</div>
            </td>
            <td style="text-align:center;">:</td>
            <td>
                <div>a. {{ $travelRequest->user->pangkat ?? '-' }}</div>
                <div>b. {{ $travelRequest->user->jabatan ?? $travelRequest->user->role }}</div>
                <div>c. {{ $travelRequest->tingkat_biaya ?? '-' }}</div>
            </td>
        </tr>
        <tr>
            <td style="text-align:center; font-weight:bold;">4</td>
            <td>Maksud Perjalanan Dinas</td>
            <td style="text-align:center;">:</td>
            <td>{{ $travelRequest->keperluan }}</td>
        </tr>
        <tr>
            <td style="text-align:center; font-weight:bold;">5</td>
            <td>Alat Angkut yang dipergunakan</td>
            <td style="text-align:center;">:</td>
            <td>{{ $travelRequest->transportasi }}</td>
        </tr>
        <tr>
            <td style="text-align:center; font-weight:bold;">6</td>
            <td>
                <div>a. Tempat Berangkat</div>
                <div>b. Tempat Tujuan</div>
            </td>
            <td style="text-align:center;">:</td>
            <td>
                <div>a. {{ $travelRequest->tempat_berangkat ?? 'Cirebon (Sumber)' }}</div>
                <div>b. {{ $travelRequest->tujuan }}</div>
            </td>
        </tr>
        <tr>
            <td style="text-align:center; font-weight:bold;">7</td>
            <td>
                <div>a. Lamanya Perjalanan Dinas</div>
                <div>b. Tanggal Berangkat</div>
                <div>c. Tanggal Harus Kembali/tiba di tempat baru</div>
            </td>
            <td style="text-align:center;">:</td>
            <td>
                <div>a. {{ $travelRequest->lama_perjalanan }} {{ $travelRequest->lama_perjalanan > 1 ? 'hari' : '(satu) hari' }}</div>
                <div>b. {{ \Carbon\Carbon::parse($travelRequest->tanggal_berangkat)->format('d M Y') }}</div>
                <div>c. {{ \Carbon\Carbon::parse($travelRequest->tanggal_kembali)->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- HALAMAN 2: SPD LANJUTAN -->
    <div class="page-break"></div>
    
    <!-- HEADER HALAMAN 2 -->
    <table class="no-border" style="width:100%; border:none; margin-bottom:0;">
        <tr>
            <td class="no-border header-kiri" style="width:60%; vertical-align:top;">
                KOMISI PEMILIHAN UMUM<br>KABUPATEN CIREBON
            </td>
            <td class="no-border header-kanan" style="width:40%; vertical-align:top;">
                Lampiran I<br>
                Peraturan Menteri Keuangan Republik Indonesia No. 113/PMK.05/2012<br>
                Tentang Perjalanan Dinas Jabatan Dalam Negeri Bagi<br>
                Pejabat Negara, Pegawai Negeri, dan Pegawai Tidak Tetap
                <br>
                <table class="no-border" style="border:none; font-size:12pt; margin-left:auto;">
                    <tr class="no-border"><td class="no-border">Lembar Ke</td><td class="no-border">:</td><td class="no-border">2</td></tr>
                    <tr class="no-border"><td class="no-border">Kode No.</td><td class="no-border">:</td><td class="no-border">SPD-001</td></tr>
                    <tr class="no-border"><td class="no-border">Nomor</td><td class="no-border">:</td><td class="no-border">{{ $travelRequest->kode_sppd }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="judul-spd">SURAT PERJALANAN DINAS (SPD)</div>
    @if($travelRequest->status === 'completed')
        <div class="nomor-spd">Nomor: {{ $travelRequest->kode_sppd }}</div>
    @endif

    <!-- TABEL LANJUTAN SPD HALAMAN 2 -->
    <table>
        <tr>
            <td style="width:2.5em; text-align:center; font-weight:bold;">8</td>
            <td>Pengikut<br><span class="small-text">Nama</span></td>
            <td style="width:1em; text-align:center;">:</td>
            <td>
                @if(isset($travelRequest->pengikut) && is_array($travelRequest->pengikut) && count($travelRequest->pengikut) > 0)
                    <table style="width:100%; border-collapse:collapse; font-size:9pt; margin:0;">
                        <tr>
                            <th style="border:1px solid #000;">Nama</th>
                            <th style="border:1px solid #000;">Pangkat/ Gol</th>
                            <th style="border:1px solid #000;">Jabatan</th>
                        </tr>
                        @foreach($travelRequest->pengikut as $pengikut)
                        <tr>
                            <td style="border:1px solid #000;">{{ $pengikut['nama'] }}</td>
                            <td style="border:1px solid #000;">{{ $pengikut['pangkat'] ?? '-' }}</td>
                            <td style="border:1px solid #000;">{{ $pengikut['jabatan'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </table>
                @else
                    <span class="small-text">-</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="text-align:center; font-weight:bold;">9</td>
            <td>
                <div>a. Instansi</div>
                <div>b. Akun</div>
            </td>
            <td style="text-align:center;">:</td>
            <td>
                <div>a. {{ $travelRequest->instansi ?? 'Komisi Pemilihan Umum Kabupaten Cirebon' }}</div>
                <div>b. {{ $travelRequest->akun_anggaran ?? '-' }}</div>
            </td>
        </tr>
        <tr>
            <td style="text-align:center; font-weight:bold;">10</td>
            <td>Keterangan Lain-lain</td>
            <td style="text-align:center;">:</td>
            <td>{{ $travelRequest->keterangan_lain ?? '-' }}</td>
        </tr>
    </table>
    <br>
    
    <!-- FOOTER PENANDATANGAN HALAMAN 2 -->
    <table class="no-border" style="width:100%; border:none; margin-top: 24px;">
        <tr>
            <td class="no-border" style="width:60%; border:none;"></td>
            <td class="no-border" style="border:none;">
                Dikeluarkan di : Cirebon<br>
                Tanggal : {{ $travelRequest->created_at->format('d F Y') }}<br><br>
                Pejabat Pembuat Komitmen<br><br>
                <div class="signature-space"></div>
                <span class="bold underline">{{ $travelRequest->ppk_nama ?? 'Hendra Gunawan, S.IP., M.Si' }}</span><br>
                NIP. {{ $travelRequest->ppk_nip ?? '198402132009121001' }}
            </td>
        </tr>
    </table>

    <!-- CATATAN TAMBAHAN -->
    <div style="margin-top: 20px; font-size: 9pt; color: #666;">
        <strong>Catatan:</strong><br>
        1. SPD ini berlaku selama perjalanan dinas berlangsung<br>
        2. Setelah selesai perjalanan dinas, SPD ini harus dilengkapi dengan bukti-bukti pengeluaran<br>
        3. SPD ini harus disimpan sebagai bukti pertanggungjawaban keuangan
    </div>
</body>
</html> 