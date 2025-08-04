<!DOCTYPE html>
<html>
<head>
    <title>Surat Tugas</title>
    <style>
        @page { 
            size: A4; 
            margin: 1.5cm 2cm 1.5cm 2cm; 
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            margin: 0;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 8px;
        }
        .alamat {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        .judul {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0 15px 0;
            text-decoration: underline;
        }
        .nomor {
            margin-bottom: 15px;
            font-size: 11pt;
            line-height: 1.3;
        }
        .isi {
            margin-bottom: 15px;
            font-size: 11pt;
            line-height: 1.3;
        }
        .ttd {
            margin-top: 20px;
            text-align: right;
            font-size: 11pt;
            line-height: 1.3;
        }
        .logo {
            height: 50px;
            margin-bottom: 8px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table-data td {
            padding: 1px 4px;
            vertical-align: top;
            font-size: 11pt;
            line-height: 1.2;
        }
        .peserta-list {
            margin: 0 0 8px 0;
            padding-left: 16px;
            font-size: 11pt;
        }
        .peserta-list li {
            margin-bottom: 2px;
        }
        .spacing-small {
            margin-bottom: 8px;
        }
        .spacing-medium {
            margin-bottom: 12px;
        }
        hr {
            border: none;
            border-top: 2px solid #000;
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo KPU"><br>
        KOMISI PEMILIHAN UMUM<br>
        KABUPATEN CIREBON
    </div>
    <hr>
    <div class="alamat">
        Jl. R. Dewi Sartika No. 100, Sumber, Cirebon<br>
        Telp. (0231) 324292 Fax. (0231) 324292<br>
        Website: kab-cirebon.kpu.go.id | Email: kpukab.cirebon@kpu.go.id
    </div>
    
    <div class="judul">
        SURAT TUGAS
    </div>
    
    <div class="nomor spacing-medium">
        Nomor: {{ $travelRequest->kode_sppd ?? '182/PY.02-ST/3209/1/2025' }}
    </div>
    
    <div class="isi">
        <div class="spacing-small">
            <table class="table-data">
                <tr><td style="width: 120px;">Menimbang</td><td style="width: 20px;">:</td><td>a. Dalam Rangka Tertibnya Administrasi;</td></tr>
                <tr><td></td><td></td><td>b. Sebagaimana dimaksud dalam huruf a perlu dibuat Surat Tugas.</td></tr>
            </table>
        </div>
        
        <div class="spacing-small">
            <table class="table-data">
                <tr><td style="width: 120px;">Dasar</td><td style="width: 20px;">:</td><td>Surat KPU Provinsi Jawa Barat Nomor 21/PY.02-Und/32/2025 tanggal 7 Maret 2025 Perihal Undangan</td></tr>
            </table>
        </div>
        
        <div class="spacing-small">
            <b>Memberi Tugas</b><br>
            <b>Kepada</b>:
            @if($travelRequest->participants && $travelRequest->participants->count())
                @foreach($travelRequest->participants as $index => $peserta)
                    <div style="margin-left: 20px; margin-bottom: 8px;">
                        {{ $index + 1 }}. Nama/NIP : {{ $peserta->name }} / {{ $peserta->nip ?? '-' }}<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;Pangkat/Gol. : {{ $peserta->pangkat ?? '-' }} / {{ $peserta->golongan ?? '-' }}<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;Jabatan : {{ $peserta->jabatan ?? '-' }}
                    </div>
                @endforeach
            @else
                <div style="margin-left: 20px; margin-bottom: 8px;">
                    1. Nama/NIP : {{ $pegawai->name ?? '-' }} / {{ $pegawai->nip ?? '-' }}<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Pangkat/Gol. : {{ $pegawai->pangkat ?? '-' }} / {{ $pegawai->golongan ?? '-' }}<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;Jabatan : {{ $pegawai->jabatan ?? '-' }}
                </div>
            @endif
        </div>
        
        <div class="spacing-small">
            <b>Untuk</b> : Melaksanakan Perjalanan Dinas dalam rangka {{ $travelRequest->keperluan ?? 'menghadiri undangan kegiatan' }}. Selama {{ $travelRequest->lama_perjalanan ?? '2 (dua)' }} hari pada tanggal {{ $travelRequest->tanggal_berangkat ? \Carbon\Carbon::parse($travelRequest->tanggal_berangkat)->format('d-m-Y') : '11-12 Maret 2025' }}, bertempat di {{ $travelRequest->tujuan ?? 'Grand Sunshine Resort & Convention, Jl. Raya Soreang No.06, Pamekaran, Kec. Soreang, Kab. Bandung' }}.
        </div>
        
        <div class="spacing-small">
            <b>Anggaran</b> : Biaya Sehubungan dengan diterbitkannya Surat Tugas ini dibebankan pada Anggaran Hibah Pilkada 2024 KPU Kabupaten Cirebon.
        </div>
        
        <div class="spacing-medium">
            Demikian Surat Tugas ini dibuat, untuk dapat dilaksanakan dengan penuh tanggung jawab.
        </div>
    </div>
    
    <div class="ttd">
        Cirebon, {{ $approval->created_at ? $approval->created_at->format('d F Y') : now()->format('d F Y') }}<br>
        Sekretaris,<br><br><br>
        <div style="display: inline-block; text-align: center; min-width: 320px;">
            <b style="font-size: 13pt;">{{ $approval && $approval->approver ? strtoupper($approval->approver->name) : 'ANDRARTUA SINAGA' }}</b><br>
            <span style="font-size: 12pt;">NIP. {{ $approval && $approval->approver ? $approval->approver->nip : '197401152000121001' }}</span>
        </div>
    </div>
</body>
</html>