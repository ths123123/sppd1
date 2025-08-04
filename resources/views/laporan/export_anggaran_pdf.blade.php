<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Anggaran SPPD - KPU Kabupaten Cirebon</title>
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
        <h1>LAPORAN ANGGARAN SPPD</h1>
        <h2>KPU Kabupaten Cirebon</h2>
        <div class="date">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Anggaran {{ $currentYear }}</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <strong>Rp {{ number_format($totalBudget, 0, ',', '.') }}</strong>
                    <span>Total Anggaran</span>
                </div>
                <div class="stats-cell">
                    <strong>Rp {{ number_format($avgBudget, 0, ',', '.') }}</strong>
                    <span>Rata-rata per SPPD</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $budgetData->sum('total_sppd') }}</strong>
                    <span>Total SPPD Disetujui</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $budgetData->count() }}</strong>
                    <span>Bulan Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Anggaran Bulanan {{ $currentYear }}</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Jumlah SPPD</th>
                    <th>Total Anggaran</th>
                    <th>Rata-rata per SPPD</th>
                    <th>Uang Harian</th>
                    <th>Biaya Transportasi</th>
                    <th>Biaya Lainnya</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budgetData as $data)
                <tr>
                    <td>{{ $data['month'] }}</td>
                    <td>{{ $data['total_sppd'] }}</td>
                    <td class="amount">Rp {{ number_format($data['total_budget'], 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($data['avg_budget'], 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($data['total_harian'], 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($data['total_transport'], 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($data['total_lainnya'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Top 10 Kegiatan Berdasarkan Anggaran</div>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kegiatan/Keperluan</th>
                    <th>Jumlah SPPD</th>
                    <th>Total Anggaran</th>
                    <th>Rata-rata per SPPD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topActivities as $index => $activity)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $activity->keperluan }}</td>
                    <td>{{ $activity->total_sppd }}</td>
                    <td class="amount">Rp {{ number_format($activity->total_budget, 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($activity->total_budget / $activity->total_sppd, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Analisis Komponen Biaya</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Komponen Biaya</th>
                    <th>Total (Rp)</th>
                    <th>Persentase dari Total Anggaran</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalHarian = $budgetData->sum('total_harian');
                    $totalTransport = $budgetData->sum('total_transport');
                    $totalLainnya = $budgetData->sum('total_lainnya');
                    $totalAnggaran = $totalBudget;
                    
                    $persenHarian = $totalAnggaran > 0 ? ($totalHarian / $totalAnggaran) * 100 : 0;
                    $persenTransport = $totalAnggaran > 0 ? ($totalTransport / $totalAnggaran) * 100 : 0;
                    $persenLainnya = $totalAnggaran > 0 ? ($totalLainnya / $totalAnggaran) * 100 : 0;
                @endphp
                
                <tr>
                    <td>Uang Harian</td>
                    <td class="amount">Rp {{ number_format($totalHarian, 0, ',', '.') }}</td>
                    <td>{{ number_format($persenHarian, 1) }}%</td>
                </tr>
                <tr>
                    <td>Biaya Transportasi</td>
                    <td class="amount">Rp {{ number_format($totalTransport, 0, ',', '.') }}</td>
                    <td>{{ number_format($persenTransport, 1) }}%</td>
                </tr>
                <tr>
                    <td>Biaya Lainnya</td>
                    <td class="amount">Rp {{ number_format($totalLainnya, 0, ',', '.') }}</td>
                    <td>{{ number_format($persenLainnya, 1) }}%</td>
                </tr>
                <tr style="font-weight: bold; background-color: #e9ecef;">
                    <td>TOTAL</td>
                    <td class="amount">Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</td>
                    <td>100.0%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li>Laporan ini hanya mencakup SPPD yang telah disetujui (status: completed)</li>
            <li>Anggaran dihitung berdasarkan total biaya yang telah disetujui</li>
            <li>Uang harian, biaya transportasi, dan biaya lainnya adalah komponen dari total biaya SPPD</li>
            <li>Data dikelompokkan berdasarkan tahun {{ $currentYear }}</li>
        </ul>
    </div>
</body>
</html> 