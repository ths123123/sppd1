<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analitik SPPD - KPU Kabupaten Cirebon</title>
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
        .two-column {
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
            vertical-align: top;
        }
        .budget-item {
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .budget-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 13px;
        }
        .quarter-box {
            display: inline-block;
            width: 23%;
            margin: 1%;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: top;
        }
        .quarter-box h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #4f46e5;
        }
        .quarter-box .stat {
            margin: 3px 0;
            font-size: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN ANALITIK SPPD</h1>
        <h2>KOMISI PEMILIHAN UMUM KABUPATEN CIREBON</h2>
        <div class="date">
            Dicetak pada: {{ now()->format('d F Y, H:i:s') }} WIB
        </div>
    </div>

    <!-- Catatan Audit & Informasi Legal -->
    @if($catatan || $penanggung_jawab || $jabatan || $tanggal_laporan)
    <div style="border:1px solid #ffe082; background:#fffde7; padding:12px 18px; margin-bottom:18px; border-radius:6px;">
        @if($catatan)
            <div style="margin-bottom:8px;"><strong>Catatan Audit / Summary:</strong><br><span style="white-space:pre-line;">{{ $catatan }}</span></div>
        @endif
        <div style="font-size:11px; color:#444; margin-top:4px;">
            <span style="margin-right:18px;"><strong>Penanggung Jawab:</strong> {{ $penanggung_jawab }}</span>
            <span style="margin-right:18px;"><strong>Jabatan:</strong> {{ $jabatan }}</span>
            <span><strong>Tanggal Laporan:</strong> {{ \Carbon\Carbon::parse($tanggal_laporan)->format('d F Y') }}</span>
        </div>
    </div>
    @endif

    <!-- Rekap Per User -->
    <div class="section">
        <div class="section-title">REKAP PER USER</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Role</th>
                    <th>Total SPPD</th>
                    <th>Disetujui</th>
                    <th>Total Anggaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($userStats as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td style="text-align:right;">{{ number_format($user->total_sppd) }}</td>
                    <td style="text-align:right;">{{ number_format($user->approved_count) }}</td>
                    <td style="text-align:right;">Rp {{ number_format($user->total_budget) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;">Tidak ada data user</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Rekap Per Unit Kerja -->
    <div class="section">
        <div class="section-title">REKAP PER UNIT KERJA</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Unit Kerja (Role)</th>
                    <th>Total SPPD</th>
                    <th>Disetujui</th>
                    <th>Total Anggaran</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $unitStats = collect($userStats)->groupBy('role')->map(function($group) {
                        return [
                            'total_sppd' => $group->sum('total_sppd'),
                            'approved_count' => $group->sum('approved_count'),
                            'total_budget' => $group->sum('total_budget'),
                        ];
                    });
                @endphp
                @forelse($unitStats as $role => $stat)
                <tr>
                    <td>{{ ucfirst($role) }}</td>
                    <td style="text-align:right;">{{ number_format($stat['total_sppd']) }}</td>
                    <td style="text-align:right;">{{ number_format($stat['approved_count']) }}</td>
                    <td style="text-align:right;">Rp {{ number_format($stat['total_budget']) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;">Tidak ada data unit kerja</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Rekap Per Status -->
    <div class="section">
        <div class="section-title">REKAP PER STATUS</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Jumlah SPPD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statusDistribution as $status => $jumlah)
                <tr>
                    <td>{{ ucfirst($status) }}</td>
                    <td style="text-align:right;">{{ number_format($jumlah) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Top Destinasi -->
    <div class="section">
        <div class="section-title">TOP DESTINASI PERJALANAN DINAS</div>
        <table class="table">
            <thead>
                <tr>
                    <th width="10%">Ranking</th>
                    <th width="60%">Destinasi</th>
                    <th width="15%">Jumlah SPPD</th>
                    <th width="15%">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topDestinations as $index => $destination)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $destination->tujuan }}</td>
                    <td style="text-align: center;">{{ $destination->total }}</td>
                    <td style="text-align: center;">
                        {{ $totalSPPD > 0 ? number_format(($destination->total / $totalSPPD) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #666;">Belum ada data destinasi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Quarterly Analysis -->
    <div class="section">
        <div class="section-title">ANALISIS QUARTERLY {{ now()->year }}</div>
        @foreach($quarterlyData as $quarter)
        <div class="quarter-box">
            <h4>{{ $quarter['quarter'] }}</h4>
            <div class="stat">Total: {{ $quarter['total'] }}</div>
            <div class="stat">Disetujui: {{ $quarter['completed'] }}</div>
            <div class="stat">Budget: Rp {{ number_format($quarter['budget'], 0, ',', '.') }}</div>
        </div>
        @endforeach
    </div>

    <div class="page-break"></div>

    <!-- User Performance -->
    <div class="section">
        <div class="section-title">PERFORMA USER (TOP 10)</div>
        <table class="table">
            <thead>
                <tr>
                    <th width="25%">Nama User</th>
                    <th width="15%">Role</th>
                    <th width="10%">Total SPPD</th>
                    <th width="10%">Disetujui</th>
                    <th width="20%">Total Budget</th>
                    <th width="10%">Success Rate</th>
                    <th width="10%">Avg/SPPD</th>
                </tr>
            </thead>
            <tbody>
                @forelse($userStats as $user)
                @php
                    $successRate = $user->total_sppd > 0 ? ($user->approved_count / $user->total_sppd) * 100 : 0;
                    $avgPerSPPD = $user->approved_count > 0 ? $user->total_budget / $user->approved_count : 0;
                @endphp
                <tr>
                    <td>{{ $user->name }}</td>
                                                <td>{{ $user->role }}</td>
                    <td style="text-align: center;">{{ $user->total_sppd }}</td>
                    <td style="text-align: center;">{{ $user->approved_count }}</td>
                    <td style="text-align: right;">Rp {{ number_format($user->total_budget, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format($successRate, 1) }}%</td>
                    <td style="text-align: right;">Rp {{ number_format($avgPerSPPD, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #666;">Belum ada data performa user</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Statistik Tambahan -->
    <div class="section">
        <div class="section-title">STATISTIK SISTEM</div>
        <div class="two-column">
            <div class="column">
                <h4 style="margin-bottom: 10px;">Statistik Dokumen</h4>
                <div class="budget-item">Total Dokumen: {{ number_format($totalDocuments) }}</div>
                <div class="budget-item">Dokumen Terverifikasi: {{ number_format($totalVerifiedDocuments) }}</div>
                <div class="budget-item">Tingkat Verifikasi: {{ $totalDocuments > 0 ? number_format(($totalVerifiedDocuments/$totalDocuments)*100, 1) : 0 }}%</div>
            </div>
            <div class="column">
                <h4 style="margin-bottom: 10px;">Statistik Pengguna</h4>
                <div class="budget-item">Total Pengguna: {{ number_format($totalUsers) }}</div>
                <div class="budget-item">Pengguna Aktif: {{ number_format($activeUsers) }}</div>
                <div class="budget-item">Tingkat Aktivitas: {{ $totalUsers > 0 ? number_format(($activeUsers/$totalUsers)*100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>

    <!-- Tren Bulanan -->
    <div class="section">
        <div class="section-title">TREN BULANAN (12 BULAN TERAKHIR)</div>
        <table class="table">
            <thead>
                <tr>
                    <th width="20%">Bulan</th>
                    <th width="15%">SPPD Diajukan</th>
                    <th width="15%">SPPD Disetujui</th>
                    <th width="20%">Budget Disetujui</th>
                    <th width="15%">Success Rate</th>
                    <th width="15%">Growth</th>
                </tr>
            </thead>
            <tbody>
                @foreach($months as $index => $month)
                @php
                    $approved = $monthlyApproved[$index] ?? 0;
                    $budget = $monthlyBudget[$index] ?? 0;
                    $successRate = $approved > 0 ? ($approved / $approved) * 100 : 0;
                    $prevApproved = $index > 0 ? ($monthlyApproved[$index-1] ?? 0) : 0;
                    $growth = $prevApproved > 0 ? (($approved - $prevApproved) / $prevApproved) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $month }}</td>
                    <td style="text-align: center;">{{ $approved }}</td>
                    <td style="text-align: center;">{{ $approved }}</td>
                    <td style="text-align: right;">Rp {{ number_format($budget, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format($successRate, 1) }}%</td>
                    <td style="text-align: center;">
                        @if($growth > 0)
                            +{{ number_format($growth, 1) }}%
                        @elseif($growth < 0)
                            {{ number_format($growth, 1) }}%
                        @else
                            0%
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <strong>Sistem SPPD KPU Kabupaten Cirebon</strong><br>
        Laporan ini digenerate secara otomatis oleh sistem pada {{ now()->format('d F Y, H:i:s') }} WIB<br>
        Untuk informasi lebih lanjut, hubungi administrator sistem.
    </div>
</body>
</html>
