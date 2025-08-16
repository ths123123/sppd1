<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['users', 'selected' => []]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['users', 'selected' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

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
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-b peserta-row" data-nama="<?php echo e(strtolower($user['name'])); ?>">
                                <td class="p-2">
                                    <img src="<?php echo e($user['avatar_url']); ?>" alt="avatar" class="w-9 h-9 rounded-full object-cover border">
                                </td>
                                <td class="p-2 font-medium"><?php echo e($user['name']); ?></td>
                                <td class="p-2 text-gray-600"><?php echo e(ucfirst($user['role'])); ?></td>
                                <td class="p-2 text-center">
                                    <input type="checkbox" class="peserta-checkbox" value="<?php echo e($user['id']); ?>" <?php echo e(in_array($user['id'], $selected) ? 'checked' : ''); ?>>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
        // Check if it's comma-separated (old format) or array format
        if (pesertaHidden.value.includes(',')) {
            selectedPeserta = pesertaHidden.value.split(',').filter(id => id.trim() !== '');
        } else {
            selectedPeserta = [pesertaHidden.value];
        }
    }
    
    // Also check for existing participants[] inputs
    const existingParticipants = document.querySelectorAll('input[name="participants[]"]');
    if (existingParticipants.length > 0) {
        selectedPeserta = Array.from(existingParticipants).map(input => input.value);
    }
    
    // Check window.selectedPeserta if available (for edit mode)
    if (window.selectedPeserta && window.selectedPeserta.length > 0) {
        selectedPeserta = window.selectedPeserta;
        console.log('Debug - Using window.selectedPeserta:', selectedPeserta);
    }
    
    // Fungsi untuk render tabel peserta terpilih
    function renderPesertaTable() {
        if (!pesertaTable) return;
        
        // Clear table
        pesertaTable.innerHTML = '';
        
        if (selectedPeserta.length === 0) {
            pesertaTable.innerHTML = '<div class="text-center py-4"><span class="text-blue-500 font-medium">Tidak ada peserta dipilih - Anda sendiri yang akan melakukan perjalanan dinas</span></div>';
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
             item.className = 'flex items-center justify-between px-4 py-3 hover:bg-gray-50';
             item.innerHTML = `
                 <div class="flex items-center flex-grow">
                     <div class="flex-shrink-0">
                         <img src="${user.avatar_url}" alt="${user.name}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                     </div>
                     <div class="ml-3">
                         <p class="text-sm font-medium text-gray-900">${user.name}</p>
                         <p class="text-xs text-gray-500">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</p>
                     </div>
                 </div>
                 <button type="button" class="text-red-500 hover:text-red-700 remove-peserta-btn" data-participant-id="${user.id}" title="Hapus peserta">
                     <i class="fas fa-times"></i>
                 </button>
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
            console.log('Debug - Pilih Peserta button clicked');
            console.log('Debug - Current selectedPeserta:', selectedPeserta);
            
            if (modal) {
                modal.style.display = 'flex';
                console.log('Debug - Modal opened');
            }
            
            // Update checkbox state based on selectedPeserta
            document.querySelectorAll('.peserta-checkbox').forEach(cb => {
                const isChecked = selectedPeserta.includes(cb.value);
                cb.checked = isChecked;
                console.log(`Debug - Checkbox ${cb.value} (${isChecked ? 'checked' : 'unchecked'})`);
            });
        });
    }
    
    // Listen for refresh event
    document.addEventListener('refreshPesertaTable', function(e) {
        console.log('Debug - Refreshing peserta table with:', e.detail.participants);
        selectedPeserta = e.detail.participants;
        renderPesertaTable();
    });
    
    // Event handler untuk tombol OK
    if (btnOk) {
        btnOk.addEventListener('click', function() {
            console.log('Debug - OK button clicked');
            
            // Get selected participants from modal checkboxes
            const checked = Array.from(document.querySelectorAll('.peserta-checkbox:checked')).map(cb => cb.value);
            console.log('Debug - Checked participants from modal:', checked);
            
            // Get existing participants from parent page
            const existingParticipants = window.selectedPeserta || [];
            console.log('Debug - Existing participants from parent:', existingParticipants);
            
            // Merge existing and new participants (preserve existing data)
            const mergedParticipants = [...new Set([...existingParticipants, ...checked])];
            console.log('Debug - Merged participants:', mergedParticipants);
            
            selectedPeserta = mergedParticipants;
            console.log('Debug - Updated selectedPeserta:', selectedPeserta);
            
            // Update hidden input
            if (pesertaHidden && pesertaHidden.parentNode) {
                console.log('Debug - pesertaHidden found:', pesertaHidden);
                console.log('Debug - pesertaHidden.parentNode:', pesertaHidden.parentNode);
                
                // Clear existing hidden inputs
                const existingInputs = document.querySelectorAll('input[name="participants[]"]');
                existingInputs.forEach(input => input.remove());
                
                // Create new hidden inputs for each participant
                selectedPeserta.forEach(id => {
                    if (id && id.toString().trim() !== '') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'participants[]';
                        input.value = id.toString().trim();
                        
                        // Use pesertaHidden.parentNode if available, otherwise use document.body as fallback
                        const targetParent = pesertaHidden.parentNode || document.body;
                        targetParent.appendChild(input);
                        console.log(`Debug - Created hidden input for participant ${id}`);
                    }
                });
                
                // Also update the hidden value for backward compatibility
                pesertaHidden.value = selectedPeserta.join(',');
                console.log('Debug - Updated hidden input value:', pesertaHidden.value);
            } else {
                console.log('Debug - pesertaHidden or parentNode not found, creating fallback hidden inputs');
                
                // Fallback: create hidden inputs in document body
                selectedPeserta.forEach(id => {
                    if (id && id.toString().trim() !== '') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'participants[]';
                        input.value = id.toString().trim();
                        document.body.appendChild(input);
                        console.log(`Debug - Created fallback hidden input for participant ${id}`);
                    }
                });
            }
            
            // Update table
            renderPesertaTable();
            console.log('Debug - Table re-rendered');
            
            // Trigger custom event to notify parent page
            const event = new CustomEvent('participantsUpdated', {
                detail: { participants: selectedPeserta || [] }
            });
            document.dispatchEvent(event);
            console.log('Debug - participantsUpdated event dispatched with participants:', selectedPeserta || []);
            
            // Close modal
            if (modal) {
                modal.style.display = 'none';
                console.log('Debug - Modal closed');
            }
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
    
                   // Event handler untuk tombol hapus peserta
      document.addEventListener('click', function(e) {
          if (e.target.closest('.remove-peserta-btn')) {
              e.preventDefault();
              e.stopPropagation();
              
              const button = e.target.closest('.remove-peserta-btn');
              const participantId = button.getAttribute('data-participant-id');
              
              console.log('Debug - Modal: Remove button clicked for participant ID:', participantId);
              console.log('Debug - Modal: Before removal, selectedPeserta:', selectedPeserta);
              
              // Remove from selectedPeserta array
              selectedPeserta = selectedPeserta.filter(id => id != participantId);
              
              console.log('Debug - Modal: After removal, selectedPeserta:', selectedPeserta);
              
              // Update hidden input
              if (pesertaHidden && pesertaHidden.parentNode) {
                  console.log('Debug - Modal: pesertaHidden found:', pesertaHidden);
                  
                  // Clear existing hidden inputs except the main one
                  const existingInputs = document.querySelectorAll('input[name="participants[]"]');
                  existingInputs.forEach(input => {
                      if (input !== pesertaHidden) {
                          input.remove();
                      }
                  });
                  
                  // Create new hidden inputs for each participant
                  selectedPeserta.forEach(id => {
                      if (id && id.toString().trim() !== '') {
                          const input = document.createElement('input');
                          input.type = 'hidden';
                          input.name = 'participants[]';
                          input.value = id.toString().trim();
                          
                          // Use pesertaHidden.parentNode if available, otherwise use document.body as fallback
                          const targetParent = pesertaHidden.parentNode || document.body;
                          targetParent.appendChild(input);
                      }
                  });
                  
                  // Also update the hidden value for backward compatibility
                  pesertaHidden.value = selectedPeserta.join(',');
                  
                  console.log('Debug - Modal: Updated hidden inputs:', selectedPeserta);
              } else {
                  console.log('Debug - Modal: pesertaHidden or parentNode not found, creating fallback hidden inputs');
                  
                  // Fallback: create hidden inputs in document body
                  selectedPeserta.forEach(id => {
                      if (id && id.toString().trim() !== '') {
                          const input = document.createElement('input');
                          input.type = 'hidden';
                          input.name = 'participants[]';
                          input.value = id.toString().trim();
                          document.body.appendChild(input);
                          console.log(`Debug - Modal: Created fallback hidden input for participant ${id}`);
                      }
                  });
              }
              
              // Re-render table
              renderPesertaTable();
              
              console.log('Debug - Modal: Participant removed successfully');
          }
      });
     
     // Initialize
     renderPesertaTable();
 });
</script> <?php /**PATH D:\pkl\SPPD-KP1\SPPD-KPUKP1\resources\views/travel_requests/partials/peserta-modal.blade.php ENDPATH**/ ?>