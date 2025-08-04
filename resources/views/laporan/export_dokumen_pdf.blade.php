<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Dokumen SPPD - KPU Kabupaten Cirebon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #333;
        }
        .header h2 {
            font-size: 14px;
            margin: 5px 0;
            color: #666;
        }
        .header .date {
            font-size: 10px;
            color: #888;
            margin-top: 10px;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-weight: bold;
            border-left: 4px solid #4f46e5;
            font-size: 14px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            width: 25%;
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: top;
        }
        .stats-cell strong {
            display: block;
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        .stats-cell span {
            font-size: 10px;
            color: #666;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-verified {
            color: #28a745;
            font-weight: bold;
        }
        .status-unverified {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DOKUMEN SPPD</h1>
        <h2>KPU Kabupaten Cirebon</h2>
        <div class="date">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Dokumen</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <strong>{{ $totalDocuments }}</strong>
                    <span>Total Dokumen</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $verifiedDocuments }}</strong>
                    <span>Dokumen Terverifikasi</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $unverifiedDocuments }}</strong>
                    <span>Dokumen Belum Verifikasi</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ number_format($verificationRate, 1) }}%</strong>
                    <span>Tingkat Verifikasi</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Daftar Dokumen SPPD</div>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dokumen</th>
                    <th>SPPD</th>
                    <th>Pengaju</th>
                    <th>Jenis Dokumen</th>
                    <th>Status Verifikasi</th>
                    <th>Tanggal Upload</th>
                    <th>Ukuran File</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $index => $document)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $document->nama_dokumen }}</td>
                    <td>{{ $document->travelRequest->kode_sppd ?? 'N/A' }}</td>
                    <td>{{ $document->user->name ?? 'N/A' }}</td>
                    <td>{{ $document->jenis_dokumen }}</td>
                    <td class="{{ $document->is_verified ? 'status-verified' : 'status-unverified' }}">
                        {{ $document->is_verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                    </td>
                    <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $document->ukuran_file ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Statistik Verifikasi per Jenis Dokumen</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Jenis Dokumen</th>
                    <th>Total</th>
                    <th>Terverifikasi</th>
                    <th>Belum Verifikasi</th>
                    <th>Persentase Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $jenisStats = $documents->groupBy('jenis_dokumen')->map(function($docs) {
                        $total = $docs->count();
                        $verified = $docs->where('is_verified', true)->count();
                        $unverified = $total - $verified;
                        $percentage = $total > 0 ? ($verified / $total) * 100 : 0;
                        
                        return [
                            'total' => $total,
                            'verified' => $verified,
                            'unverified' => $unverified,
                            'percentage' => $percentage
                        ];
                    });
                @endphp
                
                @foreach($jenisStats as $jenis => $stats)
                <tr>
                    <td>{{ $jenis }}</td>
                    <td>{{ $stats['total'] }}</td>
                    <td>{{ $stats['verified'] }}</td>
                    <td>{{ $stats['unverified'] }}</td>
                    <td>{{ number_format($stats['percentage'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li>Dokumen terverifikasi adalah dokumen yang telah diperiksa dan disetujui oleh admin</li>
            <li>Dokumen belum verifikasi adalah dokumen yang masih dalam proses pemeriksaan</li>
            <li>Laporan ini mencakup semua dokumen SPPD dalam sistem</li>
        </ul>
    </div>
</body>
</html> 