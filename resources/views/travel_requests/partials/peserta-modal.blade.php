@props(['users', 'selected' => []])

<div id="peserta-modal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">Pilih Peserta Perjalanan Dinas</h2>
                <button type="button" class="text-gray-400 hover:text-gray-600" id="close-peserta-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-3">
                <input type="text" id="search-peserta" class="form-input w-full" placeholder="Cari nama peserta...">
            </div>
            <div class="overflow-x-auto max-h-80">
                <table class="min-w-full text-sm border rounded-lg bg-white">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Avatar</th>
                            <th class="p-2 text-left">Nama</th>
                            <th class="p-2 text-left">Role</th>
                            <th class="p-2 text-center">Pilih</th>
                        </tr>
                    </thead>
                    <tbody id="peserta-table-body">
                        @foreach($users as $user)
                            <tr class="border-b peserta-row" data-nama="{{ strtolower($user['name']) }}">
                                <td class="p-2">
                                    <img src="{{ $user['avatar_url'] }}" alt="avatar" class="w-9 h-9 rounded-full object-cover border">
                                </td>
                                <td class="p-2 font-medium">{{ $user['name'] }}</td>
                                <td class="p-2 text-gray-600">{{ ucfirst($user['role']) }}</td>
                                <td class="p-2 text-center">
                                    <input type="checkbox" class="peserta-checkbox" value="{{ $user['id'] }}" {{ in_array($user['id'], $selected) ? 'checked' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" class="btn btn-secondary" id="cancel-peserta-modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-ok-peserta">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('peserta-modal');
    const btnClose = document.getElementById('close-peserta-modal');
    const btnCancel = document.getElementById('cancel-peserta-modal');
    const btnPilih = document.getElementById('btn-pilih-peserta');
    const btnOk = document.getElementById('btn-ok-peserta');
    const pesertaHidden = document.getElementById('participants-hidden');
    const pesertaTable = document.getElementById('peserta-terpilih-table');
    
    // Data users dari window.users (diinject dari blade)
    const users = window.users || [];
    let selectedPeserta = [];
    
    // Inisialisasi selectedPeserta dari hidden input jika ada
    if (pesertaHidden && pesertaHidden.value) {
        selectedPeserta = pesertaHidden.value.split(',').filter(id => id.trim() !== '');
    }
    
    // Fungsi untuk render tabel peserta terpilih
    function renderPesertaTable() {
        if (!pesertaTable) return;
        
        // Clear table
        pesertaTable.innerHTML = '';
        
        if (selectedPeserta.length === 0) {
            pesertaTable.innerHTML = '<div class="text-center py-4"><span class="text-red-500 font-medium">Belum ada peserta dipilih</span></div>';
            return;
        }
        
        // Create container
        const container = document.createElement('div');
        container.className = 'bg-white rounded-lg border border-gray-200 overflow-hidden';
        
        // Create header
        const header = document.createElement('div');
        header.className = 'bg-gray-50 px-4 py-3 border-b border-gray-200';
        header.innerHTML = `
            <h3 class="text-base font-semibold text-gray-800">Daftar Peserta Terpilih</h3>
        `;
        container.appendChild(header);
        
        // Create participant list
        const participantList = document.createElement('div');
        participantList.className = 'divide-y divide-gray-200';
        
        selectedPeserta.forEach(id => {
            const user = users.find(u => u.id == id);
            if (!user) return;
            
            const item = document.createElement('div');
            item.className = 'flex items-center px-4 py-3 hover:bg-gray-50';
            item.innerHTML = `
                <div class="flex-shrink-0">
                    <img src="${user.avatar_url}" alt="${user.name}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                </div>
                <div class="ml-3 flex-grow">
                    <p class="text-sm font-medium text-gray-900">${user.name}</p>
                    <p class="text-xs text-gray-500">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</p>
                </div>
            `;
            participantList.appendChild(item);
        });
        
        container.appendChild(participantList);
        pesertaTable.appendChild(container);
    }
    
    // Event handler untuk tombol pilih peserta
    if (btnPilih) {
        btnPilih.addEventListener('click', function(e) {
            e.preventDefault();
            if (modal) modal.style.display = 'flex';
            
            // Update checkbox state based on selectedPeserta
            document.querySelectorAll('.peserta-checkbox').forEach(cb => {
                cb.checked = selectedPeserta.includes(cb.value);
            });
        });
    }
    
    // Event handler untuk tombol OK
    if (btnOk) {
        btnOk.addEventListener('click', function() {
            // Get selected participants
            const checked = Array.from(document.querySelectorAll('.peserta-checkbox:checked')).map(cb => cb.value);
            selectedPeserta = checked;
            
            // Update hidden input
            if (pesertaHidden) {
                pesertaHidden.value = selectedPeserta.join(',');
            }
            
            // Update table
            renderPesertaTable();
            
            // Close modal
            if (modal) modal.style.display = 'none';
        });
    }
    
    // Event handler untuk tombol close dan cancel
    if (btnClose) {
        btnClose.addEventListener('click', function() {
            if (modal) modal.style.display = 'none';
        });
    }
    
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            if (modal) modal.style.display = 'none';
        });
    }
    
    // Close when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Search peserta
    const searchInput = document.getElementById('search-peserta');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.peserta-row').forEach(row => {
                row.style.display = row.dataset.nama.includes(val) ? '' : 'none';
            });
        });
    }
    
    // Initialize
    renderPesertaTable();
});
</script> 