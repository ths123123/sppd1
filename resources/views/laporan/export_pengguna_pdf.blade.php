<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengguna/Peserta SPPD - KPU Kabupaten Cirebon</title>
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
        .success-rate-high {
            color: #28a745;
            font-weight: bold;
        }
        .success-rate-medium {
            color: #ffc107;
            font-weight: bold;
        }
        .success-rate-low {
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
        <h1>LAPORAN PENGGUNA/PESERTA SPPD</h1>
        <h2>KPU Kabupaten Cirebon</h2>
        <div class="date">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Pengguna</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <strong>{{ $totalUsers }}</strong>
                    <span>Total Pengguna</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $activeUsers }}</strong>
                    <span>Pengguna Aktif</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $usersWithSPPD }}</strong>
                    <span>Pengguna dengan SPPD</span>
                </div>
                <div class="stats-cell">
                    <strong>{{ $totalUsers > 0 ? number_format(($usersWithSPPD / $totalUsers) * 100, 1) : 0 }}%</strong>
                    <span>Persentase Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Statistik Pengguna per SPPD</div>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Total SPPD</th>
                    <th>SPPD Disetujui</th>
                    <th>SPPD Ditolak</th>
                    <th>SPPD Review</th>
                    <th>SPPD Revisi</th>
                    <th>Total Anggaran</th>
                    <th>Success Rate (%)</th>
                    <th>Rata-rata per SPPD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userStats as $index => $user)
                @php
                    $successRate = $user->total_sppd > 0 ? ($user->approved_count / $user->total_sppd) * 100 : 0;
                    $avgPerSPPD = $user->approved_count > 0 ? $user->total_budget / $user->approved_count : 0;
                    $successRateClass = $successRate >= 80 ? 'success-rate-high' : ($successRate >= 50 ? 'success-rate-medium' : 'success-rate-low');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>{{ $user->total_sppd }}</td>
                    <td>{{ $user->approved_count }}</td>
                    <td>{{ $user->rejected_count }}</td>
                    <td>{{ $user->review_count }}</td>
                    <td>{{ $user->revision_count }}</td>
                    <td class="amount">Rp {{ number_format($user->total_budget, 0, ',', '.') }}</td>
                    <td class="{{ $successRateClass }}">{{ number_format($successRate, 1) }}%</td>
                    <td class="amount">Rp {{ number_format($avgPerSPPD, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Statistik per Role</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Jumlah Pengguna</th>
                    <th>Total SPPD</th>
                    <th>SPPD Disetujui</th>
                    <th>SPPD Ditolak</th>
                    <th>Total Anggaran</th>
                    <th>Rata-rata Success Rate</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $roleStats = $userStats->groupBy('role')->map(function($users, $role) {
                        $userCount = $users->count();
                        $totalSPPD = $users->sum('total_sppd');
                        $approvedSPPD = $users->sum('approved_count');
                        $rejectedSPPD = $users->sum('rejected_count');
                        $totalBudget = $users->sum('total_budget');
                        $avgSuccessRate = $users->map(function($user) {
                            return $user->total_sppd > 0 ? ($user->approved_count / $user->total_sppd) * 100 : 0;
                        })->avg();
                        
                        return [
                            'user_count' => $userCount,
                            'total_sppd' => $totalSPPD,
                            'approved_sppd' => $approvedSPPD,
                            'rejected_sppd' => $rejectedSPPD,
                            'total_budget' => $totalBudget,
                            'avg_success_rate' => $avgSuccessRate
                        ];
                    });
                @endphp
                
                @foreach($roleStats as $role => $stats)
                <tr>
                    <td>{{ ucfirst($role) }}</td>
                    <td>{{ $stats['user_count'] }}</td>
                    <td>{{ $stats['total_sppd'] }}</td>
                    <td>{{ $stats['approved_sppd'] }}</td>
                    <td>{{ $stats['rejected_sppd'] }}</td>
                    <td class="amount">Rp {{ number_format($stats['total_budget'], 0, ',', '.') }}</td>
                    <td>{{ number_format($stats['avg_success_rate'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Top 10 Pengguna Berdasarkan Jumlah SPPD</div>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pengguna</th>
                    <th>Role</th>
                    <th>Total SPPD</th>
                    <th>SPPD Disetujui</th>
                    <th>Success Rate (%)</th>
                    <th>Total Anggaran</th>
                    <th>Rata-rata per SPPD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userStats->take(10) as $index => $user)
                @php
                    $successRate = $user->total_sppd > 0 ? ($user->approved_count / $user->total_sppd) * 100 : 0;
                    $avgPerSPPD = $user->approved_count > 0 ? $user->total_budget / $user->approved_count : 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>{{ $user->total_sppd }}</td>
                    <td>{{ $user->approved_count }}</td>
                    <td>{{ number_format($successRate, 1) }}%</td>
                    <td class="amount">Rp {{ number_format($user->total_budget, 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($avgPerSPPD, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Top 10 Pengguna Berdasarkan Anggaran</div>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pengguna</th>
                    <th>Role</th>
                    <th>Total Anggaran</th>
                    <th>SPPD Disetujui</th>
                    <th>Rata-rata per SPPD</th>
                    <th>Success Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userStats->where('total_budget', '>', 0)->sortByDesc('total_budget')->take(10) as $index => $user)
                @php
                    $successRate = $user->total_sppd > 0 ? ($user->approved_count / $user->total_sppd) * 100 : 0;
                    $avgPerSPPD = $user->approved_count > 0 ? $user->total_budget / $user->approved_count : 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td class="amount">Rp {{ number_format($user->total_budget, 0, ',', '.') }}</td>
                    <td>{{ $user->approved_count }}</td>
                    <td class="amount">Rp {{ number_format($avgPerSPPD, 0, ',', '.') }}</td>
                    <td>{{ number_format($successRate, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li>Laporan ini mencakup semua pengguna dalam sistem dan aktivitas SPPD mereka</li>
            <li>Success Rate dihitung berdasarkan persentase SPPD yang disetujui dari total SPPD yang diajukan</li>
            <li>Anggaran hanya dihitung untuk SPPD yang telah disetujui</li>
            <li>Pengguna aktif adalah pengguna yang memiliki status aktif dalam sistem</li>
            <li>Data diurutkan berdasarkan jumlah SPPD terbanyak</li>
        </ul>
    </div>
</body>
</html> 