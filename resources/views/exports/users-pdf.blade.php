<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
            color: #1f2937;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 11px;
        }
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #1f2937;
            color: white;
            padding: 10px 5px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        td {
            padding: 8px 5px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status-active {
            color: #059669;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc2626;
            font-weight: bold;
        }
        .role-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .role-kasubbag { background-color: #e9d5ff; color: #7c3aed; }
        .role-sekretaris { background-color: #dbeafe; color: #2563eb; }
        .role-ppk { background-color: #fee2e2; color: #dc2626; }
        .role-staff { background-color: #f3f4f6; color: #374151; }
        .role-admin { background-color: #d1fae5; color: #059669; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border-radius: 5px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
            font-size: 11px;
        }
        .summary-item strong {
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
        <h1>{{ $title }}</h1>
        <p>Komisi Pemilihan Umum Kabupaten Cirebon</p>
        <p>Diekspor pada: {{ $exported_at }} oleh: {{ $exported_by }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 15%;">Nama</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 10%;">NIP</th>
                <th style="width: 12%;">Jabatan</th>
                <th style="width: 8%;">Role</th>
                <th style="width: 12%;">Unit Kerja</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 10%;">No. Telp</th>
                <th style="width: 7%;">Login Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $user)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><strong>{{ $user->name }}</strong></td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->nip ?? '-' }}</td>
                <td>{{ $user->jabatan ?? '-' }}</td>
                <td>
                    <span class="role-badge role-{{ $user->role }}">
                                                    {{ $user->role }}
                    </span>
                </td>
                <td>{{ $user->unit_kerja ?? '-' }}</td>
                <td>
                    <span class="{{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </td>
                <td>{{ $user->phone ?? '-' }}</td>
                <td style="font-size: 10px;">
                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y') : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-item">
            <strong>Total User:</strong> {{ $users->count() }}
        </div>
        <div class="summary-item">
            <strong>User Aktif:</strong> {{ $users->where('is_active', true)->count() }}
        </div>
        <div class="summary-item">
            <strong>User Tidak Aktif:</strong> {{ $users->where('is_active', false)->count() }}
        </div>
        @php
            $roleCount = $users->groupBy('role')->map->count();
        @endphp
        @foreach($roleCount as $role => $count)
        <div class="summary-item">
            <strong>{{ $role }}:</strong> {{ $count }}
        </div>
        @endforeach
    </div>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem SPPD KPU Kabupaten Cirebon</p>
    </div>
</body>
</html>
