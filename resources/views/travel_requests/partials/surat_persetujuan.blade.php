<!DOCTYPE html>
<html>
<head>
    <title>Surat Persetujuan Perjalanan Dinas</title>
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
        .kewajiban-list {
            margin: 8px 0 8px 0;
            padding-left: 16px;
            font-size: 11pt;
        }
        .kewajiban-list li {
            margin-bottom: 3px;
            line-height: 1.2;
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
        SURAT PERSETUJUAN PERJALANAN DINAS
    </div>
    
    <div class="nomor spacing-medium">
        Nomor : {{ $travelRequest->nomor_persetujuan ?? $travelRequest->kode_sppd ?? '-' }}<br>
        Lampiran : 1 (satu) berkas<br>
        Perihal : Persetujuan Perjalanan Dinas
    </div>
    
    <div class="isi">
        <div class="spacing-small">
            Kepada Yth.<br>
            <b>{{ $pegawai->name ?? '-' }}</b><br>
            {{ $pegawai->jabatan ?? '-' }} KPU Kabupaten Cirebon<br>
            di Tempat
        </div>
        
        <div class="spacing-medium">
            Dengan hormat,<br>
            Berdasarkan surat pengajuan SPPD nomor {{ $travelRequest->nomor_surat_tugas ?? '-' }} tanggal {{ $travelRequest->tanggal_surat_tugas ? \Carbon\Carbon::parse($travelRequest->tanggal_surat_tugas)->format('d F Y') : '-' }}, maka dengan ini kami memberikan <b>PERSETUJUAN</b> kepada:
        </div>
        
        <table class="table-data">
            <tr><td style="width: 160px;">Nama</td><td>: {{ $pegawai->name ?? '-' }}</td></tr>
            <tr><td>NIP</td><td>: {{ $pegawai->nip ?? '-' }}</td></tr>
            <tr><td>Pangkat/Golongan</td><td>: {{ $pegawai->pangkat ?? '-' }}{{ $pegawai->golongan ? ' / '.$pegawai->golongan : '' }}</td></tr>
            <tr><td>Jabatan</td><td>: {{ $pegawai->jabatan ?? '-' }}</td></tr>
            <tr><td>Unit Kerja</td><td>: {{ $pegawai->unit_kerja ?? 'KPU Kabupaten Cirebon' }}</td></tr>
        </table>
        
        <div style="margin-bottom: 8px;">
            <b>Peserta Perjalanan Dinas:</b>
            @if($travelRequest->participants && $travelRequest->participants->count())
                <ol class="peserta-list">
                    @foreach($travelRequest->participants as $peserta)
                        <li>{{ $peserta->name }} ({{ $peserta->nip ?? '-' }}) - {{ $peserta->jabatan ?? '-' }}</li>
                    @endforeach
                </ol>
            @else
                <span> -</span>
            @endif
        </div>
        
        <table class="table-data">
            <tr><td style="width: 160px;">Tujuan Kegiatan</td><td>: {{ $travelRequest->keperluan ?? '-' }}</td></tr>
            <tr><td>Tempat Pelaksanaan</td><td>: {{ $travelRequest->tujuan ?? '-' }}</td></tr>
            <tr><td>Waktu Pelaksanaan</td><td>: {{ $travelRequest->tanggal_berangkat ? \Carbon\Carbon::parse($travelRequest->tanggal_berangkat)->format('d F Y') : '-' }} s.d {{ $travelRequest->tanggal_kembali ? \Carbon\Carbon::parse($travelRequest->tanggal_kembali)->format('d F Y') : '-' }} ({{ $travelRequest->lama_perjalanan ?? '-' }} hari)</td></tr>
            <tr><td>Transportasi</td><td>: {{ $travelRequest->transportasi ?? '-' }}</td></tr>
            <tr><td>Biaya</td><td>: Dibebankan pada anggaran KPU Kabupaten Cirebon TA. {{ $travelRequest->tahun_anggaran ?? (now()->year) }}</td></tr>
        </table>
        
        <div class="spacing-small">
            Surat persetujuan ini berlaku sejak tanggal ditetapkan dan akan berakhir setelah pelaksanaan kegiatan selesai. Demikian surat persetujuan ini dibuat untuk dapat dilaksanakan sebagaimana mestinya.
        </div>
    </div>
    
    <div class="ttd">
        Ditetapkan di : Cirebon<br>
        Pada tanggal : {{ $approval->created_at ? $approval->created_at->format('d F Y') : now()->format('d F Y') }}<br><br>
        <div style="display: inline-block; text-align: center; min-width: 320px;">
            KOMISI PEMILIHAN UMUM<br>
            KABUPATEN CIREBON<br>
            SEKRETARIS<br><br><br>
            <b style="font-size: 13pt;">{{ $approval && $approval->approver ? strtoupper($approval->approver->name) : 'Drs. Ahmad Supriyadi, M.Si' }}</b><br>
            <span style="font-size: 12pt;">NIP. {{ $approval && $approval->approver ? $approval->approver->nip : '196505151990031001' }}</span>
        </div>
    </div>
</body>
</html>