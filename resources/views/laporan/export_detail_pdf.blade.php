<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Detail SPPD - KPU Kabupaten Cirebon</title>
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
        .status-completed {
            color: #28a745;
            font-weight: bold;
        }
        .status-in_review {
            color: #ffc107;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
        .status-revision {
            color: #fd7e14;
            font-weight: bold;
        }
        .amount {
            text-align: right;
            font-family: monospace;
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
        <h1>LAPORAN DETAIL SPPD</h1>
        <h2>KPU Kabupaten Cirebon</h2>
        <div class="date">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Status SPPD</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <strong>{{ $totalSPPD }}</strong>
                    <span>Total SPPD</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $completedSPPD }}</strong>
                    <span>Disetujui</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $inReviewSPPD }}</strong>
                    <span>Dalam Review</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $rejectedSPPD }}</strong>
                    <span>Ditolak</span>
                </div>
            </div>
            <div class="stats-row">
                <div class="stats-cell">
                    <strong>{{ $revisionSPPD }}</strong>
                    <span>Revisi</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $completedSPPD > 0 ? number_format(($completedSPPD / $totalSPPD) * 100, 1) : 0 }}%</strong>
                    <span>Success Rate</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $sppdList->where('status', 'completed')->sum('total_biaya') > 0 ? 'Rp ' . number_format($sppdList->where('status', 'completed')->sum('total_biaya'), 0, ',', '.') : 'Rp 0' }}</strong>
                    <span>Total Anggaran Disetujui</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $sppdList->where('status', 'completed')->avg('total_biaya') > 0 ? 'Rp ' . number_format($sppdList->where('status', 'completed')->avg('total_biaya'), 0, ',', '.') : 'Rp 0' }}</strong>
                    <span>Rata-rata per SPPD</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Daftar Lengkap SPPD</div>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode SPPD</th>
                    <th>Nama Pengaju</th>
                    <th>Role</th>
                    <th>Tujuan</th>
                    <th>Keperluan</th>
                    <th>Tanggal Berangkat</th>
                    <th>Tanggal Kembali</th>
                    <th>Durasi</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th>Transportasi</th>
                    <th>Tempat Menginap</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sppdList as $index => $sppd)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sppd->kode_sppd }}</td>
                    <td>{{ $sppd->user->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($sppd->user->role ?? 'N/A') }}</td>
                    <td>{{ $sppd->tujuan }}</td>
                    <td>{{ $sppd->keperluan }}</td>
                    <td>{{ $sppd->tanggal_berangkat ? \Carbon\Carbon::parse($sppd->tanggal_berangkat)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $sppd->tanggal_kembali ? \Carbon\Carbon::parse($sppd->tanggal_kembali)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $sppd->lama_perjalanan }} hari</td>
                    <td class="amount">Rp {{ number_format($sppd->total_biaya, 0, ',', '.') }}</td>
                    <td class="status-{{ $sppd->status }}">
                        @switch($sppd->status)
                            @case('completed')
                                Disetujui
                                @break
                            @case('in_review')
                                Dalam Review
                                @break
                            @case('rejected')
                                Ditolak
                                @break
                            @case('revision')
                                Revisi
                                @break
                            @default
                                {{ ucfirst($sppd->status) }}
                        @endswitch
                    </td>
                    <td>{{ $sppd->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sppd->transportasi ?? '-' }}</td>
                    <td>{{ $sppd->tempat_menginap ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Statistik per Status</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Jumlah SPPD</th>
                    <th>Persentase</th>
                    <th>Total Anggaran</th>
                    <th>Rata-rata Anggaran</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusStats = $sppdList->groupBy('status')->map(function($sppds, $status) {
                        $count = $sppds->count();
                        $percentage = $sppdList->count() > 0 ? ($count / $sppdList->count()) * 100 : 0;
                        $totalBudget = $sppds->sum('total_biaya');
                        $avgBudget = $count > 0 ? $totalBudget / $count : 0;
                        
                        return [
                            'count' => $count,
                            'percentage' => $percentage,
                            'total_budget' => $totalBudget,
                            'avg_budget' => $avgBudget
                        ];
                    });
                @endphp
                
                @foreach($statusStats as $status => $stats)
                <tr>
                    <td>
                        @switch($status)
                            @case('completed')
                                Disetujui
                                @break
                            @case('in_review')
                                Dalam Review
                                @break
                            @case('rejected')
                                Ditolak
                                @break
                            @case('revision')
                                Revisi
                                @break
                            @default
                                {{ ucfirst($status) }}
                        @endswitch
                    </td>
                    <td>{{ $stats['count'] }}</td>
                    <td>{{ number_format($stats['percentage'], 1) }}%</td>
                    <td class="amount">Rp {{ number_format($stats['total_budget'], 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($stats['avg_budget'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Top 10 Tujuan SPPD</div>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tujuan</th>
                    <th>Jumlah SPPD</th>
                    <th>Total Anggaran</th>
                    <th>Rata-rata per SPPD</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $topDestinations = $sppdList->groupBy('tujuan')->map(function($sppds, $tujuan) {
                        $count = $sppds->count();
                        $totalBudget = $sppds->sum('total_biaya');
                        $avgBudget = $count > 0 ? $totalBudget / $count : 0;
                        
                        return [
                            'count' => $count,
                            'total_budget' => $totalBudget,
                            'avg_budget' => $avgBudget
                        ];
                    })->sortByDesc('count')->take(10);
                @endphp
                
                @foreach($topDestinations as $index => $destination)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $destination->keys()->first() }}</td>
                    <td>{{ $destination->values()->first()['count'] }}</td>
                    <td class="amount">Rp {{ number_format($destination->values()->first()['total_budget'], 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($destination->values()->first()['avg_budget'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li>Laporan ini mencakup semua SPPD dalam sistem dengan detail lengkap</li>
            <li>Status SPPD: Disetujui, Dalam Review, Ditolak, Revisi</li>
            <li>Anggaran hanya dihitung untuk SPPD yang telah disetujui</li>
            <li>Data diurutkan berdasarkan tanggal pembuatan terbaru</li>
        </ul>
    </div>
</body>
</html> 