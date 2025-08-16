{{-- Komponen Upload Dokumen untuk Detail SPPD --}}
<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <div class="flex items-center mb-4">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-file-upload text-blue-600"></i>
        </div>
        <div>
            <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
            <p class="text-sm text-gray-600">{{ $description }}</p>
        </div>
    </div>

    @if($isEnabled)
        <form method="POST" action="{{ $actionUrl }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition-colors cursor-pointer relative">
                <input type="file" name="{{ $inputName }}[]" id="{{ $inputId }}" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <div class="space-y-2">
                    <i class="fas fa-cloud-upload-alt text-3xl text-blue-500"></i>
                    <p class="text-sm font-medium text-gray-700">Klik untuk memilih file atau seret file ke sini</p>
                    <p class="text-xs text-gray-500">Format: PDF, JPG, PNG, DOC, DOCX (Maks. 5MB per file)</p>
                </div>
            </div>
            <div id="{{ $fileListId }}" class="space-y-2 hidden"></div>
            @if(isset($errors) && $errors->has($inputName))
                <p class="text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $errors->first($inputName) }}</p>
            @endif
            @if(isset($errors) && $errors->has($inputName.'.*'))
                <p class="text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $errors->first($inputName.'.*') }}</p>
            @endif
            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-upload mr-2"></i>Unggah Dokumen
            </button>
        </form>
    @else
        <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-lock mr-2"></i>{{ $disabledMessage }}
            </p>
        </div>
    @endif

    {{-- Daftar Dokumen yang Sudah Diunggah --}}
    @if($documents->count() > 0)
        <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Dokumen yang Sudah Diunggah</h4>
            <div class="space-y-3">
                @foreach($documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $doc->original_filename }}</p>
                            <p class="text-xs text-gray-500">
                                {{ strtoupper($doc->file_type) }} • {{ number_format($doc->file_size/1024,1) }} KB
                                @if($doc->is_verified)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Verified
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ asset('storage/documents/'.$doc->filename) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            @if($doc->uploaded_by == auth()->id() || auth()->user()->role == 'admin')
                                <form method="POST" action="{{ route('documents.destroy', $doc->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
    // Script untuk preview file yang dipilih
    document.addEventListener('DOMContentLoaded', function() {
        const inputElement = document.getElementById('{{ $inputId }}');
        const fileListElement = document.getElementById('{{ $fileListId }}');
        
        if (inputElement && fileListElement) {
            inputElement.addEventListener('change', function() {
                fileListElement.innerHTML = '';
                fileListElement.classList.remove('hidden');
                
                if (this.files.length > 0) {
                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        const fileSize = (file.size / 1024).toFixed(1);
                        const fileType = file.name.split('.').pop().toUpperCase();
                        
                        const fileItem = document.createElement('div');
                        fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                        fileItem.innerHTML = `
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                                <p class="text-xs text-gray-500">${fileType} • ${fileSize} KB</p>
                            </div>
                        `;
                        
                        fileListElement.appendChild(fileItem);
                    }
                } else {
                    fileListElement.classList.add('hidden');
                }
            });
        }
    });
</script>