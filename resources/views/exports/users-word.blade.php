<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page {
            size: A4 landscape;
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 18pt;
            margin: 0;
            color: #1f2937;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 11pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #000;
        }
        th {
            background-color: #1f2937;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11pt;
            font-weight: bold;
            border: 1px solid #000;
        }
        td {
            padding: 8px;
            border: 1px solid #ccc;
            font-size: 10pt;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-active {
            color: #059669;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc2626;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10pt;
            color: #666;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border: 1px solid #ddd;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
            font-size: 11pt;
        }
        .summary-item strong {
            color: #1f2937;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p><strong>Komisi Pemilihan Umum Kabupaten Cirebon</strong></p>
        <p>Diekspor pada: {{ $exported_at }} oleh: {{ $exported_by }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>NIP</th>
                <th>Jabatan</th>
                <th>Role</th>
                <th>Unit Kerja</th>
                <th>Pangkat</th>
                <th>Golongan</th>
                <th>Status</th>
                <th>No. Telepon</th>
                <th>Login Terakhir</th>
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
                <td style="text-align: center;">
                                            <strong>{{ $user->role }}</strong>
                </td>
                <td>{{ $user->unit_kerja ?? '-' }}</td>
                <td>{{ $user->pangkat ?? '-' }}</td>
                <td>{{ $user->golongan ?? '-' }}</td>
                <td style="text-align: center;">
                    <span class="{{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </td>
                <td>{{ $user->phone ?? '-' }}</td>
                <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Belum pernah login' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Ringkasan Data User</h3>
        <div class="summary-item">
            <strong>Total User:</strong> {{ $users->count() }}
        </div>
        <div class="summary-item">
            <strong>User Aktif:</strong> {{ $users->where('is_active', true)->count() }}
        </div>
        <div class="summary-item">
            <strong>User Tidak Aktif:</strong> {{ $users->where('is_active', false)->count() }}
        </div>
        <br>
        <h4>Distribusi per Role:</h4>
        @php
            $roleCount = $users->groupBy('role')->map->count();
        @endphp
        @foreach($roleCount as $role => $count)
        <div class="summary-item">
                                    <strong>{{ $role }}:</strong> {{ $count }} user
        </div>
        @endforeach
    </div>

    <div class="footer">
        <p><em>Dokumen ini digenerate secara otomatis oleh Sistem SPPD KPU Kabupaten Cirebon</em></p>
        <p><em>{{ config('app.name') }} &copy; {{ date('Y') }}</em></p>
    </div>
</body>
</html>
