php artisan serve --host=0.0.0.0 --port=8000

untuk Halaman Approval
Semua aksi approval (Setujui, Tolak, Revisi) harus AJAX penuh:
Ketika user melakukan aksi (setujui, tolak, revisi), proses dikirim ke server tanpa reload halaman.
Setelah aksi, tabel approval otomatis ter-update (baris hilang/berubah) tanpa refresh manual.
Notifikasi dinamis:
Setelah aksi (setujui, tolak, revisi), notifikasi sukses/gagal langsung muncul di halaman secara otomatis.
Notifikasi harus jelas, mudah dibaca, dan tidak mengganggu UX.
Filter dan paginasi juga AJAX:
Filter pencarian dan urgensi, serta paginasi, harus berjalan tanpa reload halaman.
Tabel approval langsung update sesuai filter/paginasi yang dipilih.
Tidak ada error JavaScript:
Tidak ada error JS di console.
Semua event listener tetap aktif setelah reload tabel.
Tidak ada duplikasi filter:
Hanya ada satu filter form AJAX di halaman approval.
UX konsisten dan profesional:
Pengalaman pengguna di halaman approval harus sama baiknya dengan halaman Daftar SPPD.
Semua aksi dan interaksi terasa modern, cepat, dan responsif.
Aksesibilitas dan struktur HTML baik:
Semua input/select filter punya id dan name yang unik.
Label terhubung dengan input (label for/id).
Inti:
Anda ingin halaman approval yang sepenuhnya AJAX, tanpa reload, dengan notifikasi dinamis, filter/paginasi responsif, dan UX profesional seperti halaman Daftar SPPD.
Jika ada detail tambahan atau permintaan khusus lain, silakan tambahkan!
