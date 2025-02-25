@extends('layouts.app')

@section('title', 'Manajemen Template Rapor')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Template Rapor</h2>
        </div>

        <!-- Action Buttons - Similar to Student Page -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-2">
                <button onclick="openUploadModal()" 
                    class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                    </svg>
                    Upload Template
                </button>
                <button onclick="openPlaceholderGuide()" 
                    class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    Panduan Placeholder
                </button>
                <div class="dropdown">
                    <button id="dropdownSampleButton" 
                        data-dropdown-toggle="sampleDropdown" 
                        class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center">
                        Download Contoh
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="sampleDropdown" class="hidden z-10 bg-white rounded-lg shadow w-44">
                        <ul class="py-2 text-sm text-gray-700">
                            <li>
                                <a href="{{ route('report.template.sample', ['type' => 'UTS']) }}" 
                                   class="block px-4 py-2 hover:bg-gray-100">
                                   Template UTS
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('report.template.sample', ['type' => 'UAS']) }}" 
                                   class="block px-4 py-2 hover:bg-gray-100">
                                   Template UAS
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Single Templates List Table -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Jenis</th>
                        <th class="px-6 py-3">Nama File</th>
                        <th class="px-6 py-3">Tahun Ajaran</th>
                        <th class="px-6 py-3">Semester</th>
                        <th class="px-6 py-3">Tanggal Upload</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $index => $template)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium {{ $template->type === 'UTS' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }} rounded-full">
                                {{ $template->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $template->filename }}</td>
                        <td class="px-6 py-4">{{ $template->tahun_ajaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $template->semester == 1 ? 'Ganjil' : 'Genap' }}</td>
                        <td class="px-6 py-4">{{ Carbon\Carbon::parse($template->created_at)->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4">
                            @if($template->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('report.template.preview', $template->id) }}" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800">
                                    <img src="{{ asset('images/icons/detail.png') }}" alt="Preview Icon" class="w-5 h-5">
                                </a>
                                
                                @if(!$template->is_active)
                                    <form action="{{ route('report.template.activate', $template->id) }}" 
                                        method="POST" 
                                        onsubmit="return handleActivate(event)"
                                        class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800">
                                            <img src="{{ asset('images/icons/activate.png') }}" alt="Activate Icon" class="w-5 h-5">
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('report.template.destroy', $template->id) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return handleDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <img src="{{ asset('images/icons/delete.png') }}" alt="Delete Icon" class="w-5 h-5">
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center justify-center py-6">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 mb-2">Belum ada template Rapor yang diupload</p>
                                <button onclick="openUploadModal()" 
                                        class="px-4 py-2 text-sm bg-green-700 text-white rounded-lg hover:bg-green-800">
                                    Upload Template
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <div class="relative bg-white rounded-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Upload Template Rapor</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="uploadForm" action="{{ route('report.template.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Type Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Rapor</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="type" value="UTS" class="h-4 w-4 text-green-600 focus:ring-green-500" checked>
                            <span class="ml-2 text-sm text-gray-700">UTS (Tengah Semester)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="UAS" class="h-4 w-4 text-green-600 focus:ring-green-500">
                            <span class="ml-2 text-sm text-gray-700">UAS (Akhir Semester)</span>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Pastikan template yang diupload sesuai dengan jenis rapor yang dipilih
                    </p>
                </div>
                
                @if(!$schoolProfile)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                    <p>Tahun ajaran dan semester akan diambil dari Profil Sekolah, namun profil sekolah belum diisi.</p>
                    <a href="{{ route('profile.edit') }}" class="text-blue-600 hover:underline">Isi Profil Sekolah</a>
                </div>
                @else
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    <p>Tahun Ajaran: <strong>{{ $schoolProfile->tahun_pelajaran }}</strong></p>
                    <p>Semester: <strong>{{ $schoolProfile->semester == 1 ? 'Ganjil' : 'Genap' }}</strong></p>
                    <p class="text-xs mt-2">Data diambil dari Profil Sekolah</p>
                </div>
                <!-- Add hidden fields to pass the values from school profile -->
                <input type="hidden" name="tahun_ajaran" value="{{ $schoolProfile->tahun_pelajaran }}">
                <input type="hidden" name="semester" value="{{ $schoolProfile->semester }}">
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Template</label>
                    <input type="file" 
                           name="template"
                           required
                           accept=".docx"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500">Format yang diterima: .docx</p>
                    <div class="mt-2 flex flex-col gap-1">
                        <p class="text-sm text-gray-500">Pastikan template memiliki placeholder yang sesuai:</p>
                        <div class="flex gap-2">
                            <a href="javascript:void(0)" 
                               onclick="closeUploadModal(); openPlaceholderGuide();"
                               class="text-sm text-blue-600 hover:underline">
                               Lihat panduan placeholder
                            </a>
                            <span class="text-gray-500">•</span>
                            <a href="{{ route('report.template.sample', ['type' => 'UTS']) }}" 
                               id="download-sample-link"
                               class="text-sm text-blue-600 hover:underline">
                               Download contoh template
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button"
                            onclick="closeUploadModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            id="upload-button"
                            {{ !$schoolProfile ? 'disabled' : '' }}
                            class="{{ !$schoolProfile ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700' }} px-4 py-2 text-white rounded-lg transition">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Placeholder Guide -->
<div id="placeholderGuide" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative bg-white rounded-lg max-w-4xl w-full mx-auto">
            @include('admin.report.placeholder_guide')
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flowbite dropdowns
    if (typeof Dropdown !== 'undefined') {
        const targetEl = document.getElementById('sampleDropdown');
        const triggerEl = document.getElementById('dropdownSampleButton');
        
        if (targetEl && triggerEl) {
            const dropdown = new Dropdown(targetEl, triggerEl);
        }
    }

    // Update download sample link based on selected template type
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const downloadSampleLink = document.getElementById('download-sample-link');
    
    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (downloadSampleLink) {
                downloadSampleLink.href = "{{ route('report.template.sample') }}?type=" + this.value;
            }
        });
    });
    
    // Handle form submit with AJAX
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const button = document.getElementById('upload-button');
                
                // Show loading state
                button.disabled = true;
                button.textContent = 'Uploading...';
                
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                
                // Remove loading state
                button.disabled = false;
                button.textContent = 'Upload';
                
                if (result.success) {
                    window.location.reload();
                } else {
                    // Show error
                    alert(result.message || 'Gagal mengupload template. Pastikan semua placeholder wajib tersedia dalam template.');
                }
            } catch (error) {
                // Remove loading state
                document.getElementById('upload-button').disabled = false;
                document.getElementById('upload-button').textContent = 'Upload';
                
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupload template.');
            }
        });
    }
});

// Modal functions
let selectedType = 'UTS';

function openUploadModal(type = 'UTS') {
    selectedType = type;
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        if (radio.value === type) {
            radio.checked = true;
        }
    });
    
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

function openPlaceholderGuide() {
    document.getElementById('placeholderGuide').classList.remove('hidden');
}

function closePlaceholderGuide() {
    document.getElementById('placeholderGuide').classList.add('hidden');
}

// Handle aktivasi template
async function handleActivate(e) {
    e.preventDefault();
    
    if (!confirm('Apakah Anda yakin ingin mengaktifkan template ini?')) {
        return false;
    }

    const form = e.target;
    const button = form.querySelector('button');
    button.disabled = true;
    
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();
        
        if (result.success) {
            // Reload halaman setelah berhasil aktivasi
            window.location.reload();
        } else {
            alert(result.message || 'Gagal mengaktifkan template');
            button.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengaktifkan template');
        button.disabled = false;
    }
    
    return false;
}

// Handle delete template
async function handleDelete(e) {
    e.preventDefault();
    
    if (!confirm('Apakah Anda yakin ingin menghapus template ini?')) {
        return false;
    }

    const form = e.target;
    const button = form.querySelector('button');
    button.disabled = true;
    
    try {
        const response = await fetch(form.action, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();
        
        if (result.success) {
            // Reload halaman setelah berhasil hapus
            window.location.reload();
        } else {
            alert(result.message || 'Gagal menghapus template');
            button.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus template');
        button.disabled = false;
    }
    
    return false;
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed')) {
        closeUploadModal();
        closePlaceholderGuide();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
        closePlaceholderGuide();
    }
});
</script>
@endpush
@endsection