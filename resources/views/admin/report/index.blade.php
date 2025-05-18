@extends('layouts.app')

@section('title', 'Manajemen Template Rapor')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Template Rapor</h2>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-2">
                <button onclick="openUploadModal()" 
                    class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2.5 transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
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

        <!-- Filter Controls -->
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div class="flex gap-2">
                <select id="filter-type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 p-2.5">
                    <option value="">Semua Jenis</option>
                    <option value="UTS">UTS</option>
                    <option value="UAS">UAS</option>
                </select>
            </div>
            
            <!-- Search Box -->
            <div class="relative w-full md:w-auto mt-2 md:mt-0">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" 
                       id="search-input"
                       class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-green-500 focus:border-green-500" 
                       placeholder="Cari template...">
            </div>
        </div>

        <!-- Templates List Table -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Jenis</th>
                        <th class="px-6 py-3">Nama File</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Tahun Ajaran</th>
                        <th class="px-6 py-3">Semester</th>
                        <th class="px-6 py-3">Tanggal Upload</th>
                        <th class="px-6 py-3 text-center" style="width: 100px;">Status</th>
                        <th class="px-6 py-3 text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $index => $template)
                    <tr class="bg-white border-b hover:bg-gray-50" 
                        data-type="{{ $template->type }}" 
                        data-kelas="{{ $template->kelas_id ? $template->kelas->full_kelas : 'Template Global' }}"
                        data-search="{{ $template->filename }}">
                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-medium">
                                {{ $template->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4" id="filename-{{ $template->id }}">
                            @php
                                $filename = $template->filename;
                                // Remove numeric prefixes (pattern like "1746151007_RTS")
                                $cleanName = preg_replace('/^\d+_[A-Z]+_/', '', $filename);
                                // Also handle pattern like "1744894347_Template_UTS"
                                $cleanName = preg_replace('/^\d+_/', '', $cleanName);
                                // Remove file extension
                                $cleanName = preg_replace('/\.docx$/', '', $cleanName);
                            @endphp
                            {{ $cleanName }}
                        </td>
                        <td class="px-6 py-4">
                        @if($template->kelasList && $template->kelasList->count() > 0)
                            @php
                                $kelasCount = $template->kelasList->count();
                                
                                // Tampilan ringkas: kelas pertama +N
                                $firstKelas = $template->kelasList->first();
                                $firstKelasText = $firstKelas->nomor_kelas . $firstKelas->nama_kelas;
                                
                                if ($kelasCount == 1) {
                                    $kelasText = $firstKelasText;
                                } else {
                                    $kelasText = $firstKelasText . " +" . ($kelasCount - 1);
                                }
                                
                                // Daftar lengkap kelas untuk tooltip
                                $allKelasText = $template->kelasList->map(function($kelas) {
                                    return $kelas->nomor_kelas . $kelas->nama_kelas;
                                })->implode(', ');
                            @endphp
                            
                            <span class="text-xs text-blue-600" title="{{ $allKelasText }}">
                                {{ $kelasText }}
                            </span>
                        @elseif($template->kelas_id)
                            <span class="text-xs">
                                {{ $template->kelas->nomor_kelas }}{{ $template->kelas->nama_kelas }}
                            </span>
                        @else
                            <span class="text-gray-500">Global</span>
                        @endif
                        </td>
                        </td>
                        <td class="px-6 py-4">{{ $template->tahun_ajaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $template->semester == 1 ? 'Ganjil' : 'Genap' }}</td>
                        <td class="px-6 py-4">{{ Carbon\Carbon::parse($template->created_at)->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($template->is_active)
                                <span class="inline-block px-3 py-1.5 text-xs font-medium bg-green-100 text-green-800 rounded-lg min-w-[80px]">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-block px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-lg min-w-[80px]">
                                    Tidak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <!-- Preview Button -->
                            <a href="#" onclick="previewDocument('{{ route('report.template.preview', $template->id) }}', '{{ $template->filename }}'); return false;" class="inline-block align-middle mr-2">
                                <img src="{{ asset('images/icons/detail.png') }}" alt="Detail" class="w-5 h-5">
                            </a>
                            
                            <!-- Aktivasi Checkbox dengan margin kanan kecil -->
                            <form action="{{ route('report.template.activate', $template->id) }}" 
                                method="POST" 
                                class="inline-block align-middle mr-2"
                                onsubmit="return handleActivateToggle(event)">
                                @csrf
                                <input 
                                    type="checkbox" 
                                    class="w-5 h-5 align-middle rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    id="active-{{ $template->id }}"
                                    {{ $template->is_active ? 'checked' : '' }}
                                    onclick="handleActivateToggle(event)"
                                >
                            </form>
                            
                            <!-- Delete Button tanpa margin kanan karena elemen terakhir -->
                            <form action="{{ route('report.template.destroy', $template->id) }}" 
                                method="POST" 
                                class="inline-block align-middle"
                                onsubmit="return handleDelete(event)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-block">
                                    <img src="{{ asset('images/icons/delete.png') }}" alt="Delete" class="w-5 h-5">
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center">
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
                <input type="hidden" name="tahun_ajaran_id" value="{{ session('tahun_ajaran_id') }}">
                
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

                <!-- Kelas Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                    
                    <div class="relative">
                        <button 
                            type="button" 
                            id="kelas-dropdown-btn"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 p-2.5 w-full flex justify-between items-center"
                            onclick="toggleKelasDropdown()">
                            <span id="selected-kelas">Pilih Kelas</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div id="kelas-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <div class="p-2">
                                <label class="flex items-center p-2 hover:bg-gray-100 rounded-lg">
                                    <input 
                                        type="checkbox" 
                                        id="select-all-kelas" 
                                        class="h-4 w-4 text-green-600 focus:ring-green-500 mr-2"
                                        onchange="toggleAllKelas(this)">
                                    <span class="text-sm font-medium">Pilih Semua Kelas</span>
                                </label>
                                
                                <div class="border-t my-2"></div>
                                
                                <!-- Kelas list from tahun ajaran will be here -->
                                <div class="space-y-1">
                                    @php
                                        $tahunAjaranId = session('tahun_ajaran_id');
                                        $kelasList = \App\Models\Kelas::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                                            return $query->where('tahun_ajaran_id', $tahunAjaranId);
                                        })->orderBy('nomor_kelas')->get();
                                    @endphp
                                    
                                    @foreach($kelasList as $kelas)
                                    <label class="flex items-center p-2 hover:bg-gray-100 rounded-lg">
                                        <input 
                                            type="checkbox" 
                                            name="kelas_ids[]" 
                                            value="{{ $kelas->id }}" 
                                            class="kelas-checkbox h-4 w-4 text-green-600 focus:ring-green-500 mr-2"
                                            onchange="updateSelectedKelasText()">
                                        <span class="text-sm">{{ $kelas->full_kelas }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="mt-1 text-xs text-gray-500">
                        Pilih kelas untuk template ini (hanya kelas dari tahun ajaran aktif).
                        Centang beberapa kelas jika template akan digunakan untuk banyak kelas.
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

<!-- DOCX Preview Modal -->
<div id="docxPreviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black opacity-50" onclick="closeDocxPreviewModal()"></div>
        
        <div class="relative bg-white rounded-lg w-full max-w-5xl max-h-[90vh] p-4">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-medium" id="previewFileName">Preview Document</h3>
                <button onclick="closeDocxPreviewModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="overflow-y-auto max-h-[calc(90vh-8rem)]">
                <div class="border border-gray-300 rounded-lg p-3 min-h-[70vh] bg-white">
                    <!-- Loading indicator -->
                    <div id="loadingIndicator" class="flex items-center justify-center h-full">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-green-500"></div>
                    </div>
                    
                    <!-- Error message -->
                    <div id="errorMessage" class="hidden flex items-center justify-center h-full">
                        <div class="text-red-500 text-center">
                            <p>Gagal memuat preview dokumen.</p>
                            <p class="text-sm mt-2" id="errorDetail"></p>
                        </div>
                    </div>
                    
                    <!-- DOCX Content -->
                    <div id="docxContent" class="h-full w-full"></div>
                </div>
            </div>
            
            <!-- Fallback options -->
            <div class="mt-3 text-center" id="fallbackOptions">
                <p class="text-sm text-gray-500 mb-2">Jika preview tidak tampil dengan baik, gunakan opsi berikut:</p>
                <button id="officeViewerBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Lihat dengan Office Viewer
                </button>
                <a id="downloadDocxBtn" href="#" download class="ml-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Download
                </a>
            </div>
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

@push('styles')
<style>
    /* Docx viewer styles for consistent rendering */
    .docx-viewer {
        width: 100%;
        height: 100%;
        overflow-y: auto;
    }
    
    .docx-viewer .document-container {
        padding: 20px;
        background-color: #f0f0f0;
    }
    
    .docx-viewer .document-container .page {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        background-color: white;
        margin-bottom: 20px;
        overflow: hidden;
        width: 794px !important; /* A4 width */
        min-height: 1123px; /* A4 height */
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Make modal wider on larger screens */
    @media (min-width: 1280px) {
        .max-w-5xl {
            max-width: 80vw;
        }
    }
</style>
@endpush

@push('scripts')
<!-- CDN fallback for docx-preview -->
<script src="https://unpkg.com/docx-preview@0.1.15/dist/docx-preview.js"></script>
<script>
async function handleActivateToggle(e) {
    // Prevent the default form submit or checkbox behavior
    e.preventDefault();
    
    // Find the checkbox
    const checkbox = e.target.type === 'checkbox' ? e.target : e.target.querySelector('input[type="checkbox"]');
    
    // Find the form
    const form = checkbox.closest('form');
    
    // Determine the activation action based on current state
    const isActive = checkbox.checked;
    const actionWord = isActive ? 'mengaktifkah' : 'menonaktifkan';
    
    // Show confirmation dialog
    if (!confirm(`Apakah Anda yakin ingin ${actionWord} template ini?`)) {
        // Reset checkbox state if canceled - toggle back to original state
        checkbox.checked = !checkbox.checked;
        return false;
    }

    // Disable the checkbox during the request
    checkbox.disabled = true;
    
    try {
        // Submit the form via AJAX
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
            // Reload the page after successful activation
            window.location.reload();
        } else {
            // Show error message
            alert(result.message || `Gagal ${actionWord} template`);
            checkbox.disabled = false;
            checkbox.checked = !checkbox.checked; // Reset checkbox state
        }
    } catch (error) {
        console.error('Error:', error);
        alert(`Terjadi kesalahan saat ${actionWord} template`);
        checkbox.disabled = false;
        checkbox.checked = !checkbox.checked; // Reset checkbox state
    }
    
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Filter berdasarkan jenis (UTS/UAS)
    const filterType = document.getElementById('filter-type');
    filterType.addEventListener('change', applyFilters);
    
    // Filter berdasarkan pencarian
    const searchInput = document.getElementById('search-input');
    searchInput.addEventListener('input', applyFilters);
    
    function applyFilters() {
        const typeFilter = filterType.value;
        const searchFilter = searchInput.value.toLowerCase();
        
        document.querySelectorAll('tbody tr').forEach(row => {
            const rowType = row.getAttribute('data-type');
            const rowSearchText = row.getAttribute('data-search').toLowerCase();
            
            // Cek apakah baris memenuhi semua filter
            const matchesType = !typeFilter || rowType === typeFilter;
            const matchesSearch = !searchFilter || rowSearchText.includes(searchFilter);
            
            // Tampilkan/sembunyikan baris berdasarkan hasil filter
            row.style.display = matchesType && matchesSearch ? '' : 'none';
        });
    }
});

// Fallback jika library tidak di-bundle
if (typeof window.renderAsync === 'undefined' && typeof docx !== 'undefined') {
    window.renderAsync = docx.renderAsync;
}

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
                
                // Ambil semua checkbox kelas yang dicentang
                const selectedKelas = document.querySelectorAll('.kelas-checkbox:checked');
                
                // Hapus kelas_ids[] yang sudah ada di formData (jika ada)
                for (const pair of [...formData.entries()]) {
                    if (pair[0] === 'kelas_ids[]') {
                        formData.delete(pair[0]);
                    }
                }
                
                // Tambahkan kelas_ids yang dicentang ke formData
                selectedKelas.forEach(checkbox => {
                    formData.append('kelas_ids[]', checkbox.value);
                });
                
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
let currentPreviewUrl = '';

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

async function previewDocument(url, filename) {
    // Show the modal
    document.getElementById('docxPreviewModal').classList.remove('hidden');
    
    // Update the filename
    document.getElementById('previewFileName').textContent = 'Preview: ' + filename;
    
    // Show loading indicator
    document.getElementById('loadingIndicator').style.display = 'flex';
    document.getElementById('errorMessage').classList.add('hidden');
    document.getElementById('docxContent').innerHTML = '';
    
    // Set current URL for fallback options
    currentPreviewUrl = url;
    document.getElementById('downloadDocxBtn').href = url;
    
    try {
        // Fetch the DOCX file
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Convert to array buffer
        const arrayBuffer = await response.arrayBuffer();
        
        // Hide loading indicator
        document.getElementById('loadingIndicator').style.display = 'none';
        
        // Check if renderAsync is available
        if (typeof window.renderAsync === 'function') {
            // Render the DOCX with improved settings for more consistent document size
            await window.renderAsync(arrayBuffer, document.getElementById('docxContent'), null, {
                className: 'docx-viewer',
                inWrapper: true,
                ignoreWidth: false,
                ignoreHeight: false,
                breakPages: true,
                renderHeaders: true,
                renderFooters: true,
                useBase64URL: true,
                useMathMLPolyfill: true,
                pageWidth: 794, // A4 width in pixels (approximately)
                pageHeight: 1123, // A4 height in pixels (approximately)
                pageBorderTop: 10,
                pageBorderRight: 10,
                pageBorderBottom: 10,
                pageBorderLeft: 10
            });
        } else {
            throw new Error('DocX Preview library not found. Use the fallback options instead.');
        }
    } catch (error) {
        // Hide loading indicator
        document.getElementById('loadingIndicator').style.display = 'none';
        
        // Show error message
        document.getElementById('errorMessage').classList.remove('hidden');
        document.getElementById('errorDetail').textContent = error.message;
        
        console.error('Error rendering DOCX:', error);
    }
}

function closeDocxPreviewModal() {
    document.getElementById('docxPreviewModal').classList.add('hidden');
    document.getElementById('docxContent').innerHTML = '';
}

function openDocxInOfficeViewer(url) {
    // Ensure we have a full URL for Office Viewer
    var publicUrl = '';
    
    // Check if the URL already starts with http
    if (url.startsWith('http')) {
        publicUrl = url;
    } else {
        // Otherwise, build the full URL
        publicUrl = window.location.origin + (url.startsWith('/') ? '' : '/') + url;
    }
    
    // Create Office Online viewer URL
    var viewerUrl = "https://view.officeapps.live.com/op/embed.aspx?src=" + encodeURIComponent(publicUrl);
    
    // Instead of opening a new window, embed the document in an iframe within the modal
    const docxContent = document.getElementById('docxContent');
    docxContent.innerHTML = '';
    
    // Create a responsive iframe container
    const iframeContainer = document.createElement('div');
    iframeContainer.className = 'w-full h-full min-h-[70vh]';
    
    // Create an iframe for the Office Viewer
    const iframe = document.createElement('iframe');
    iframe.src = viewerUrl;
    iframe.className = 'w-full h-full min-h-[70vh] border-0';
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allowfullscreen', 'true');
    
    // Add the iframe to the container
    iframeContainer.appendChild(iframe);
    docxContent.appendChild(iframeContainer);
    
    // Hide loading indicator if it's still visible
    document.getElementById('loadingIndicator').style.display = 'none';
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

document.getElementById('officeViewerBtn').onclick = function() {
    openDocxInOfficeViewer(currentPreviewUrl);
};

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed')) {
        closeUploadModal();
        closePlaceholderGuide();
        closeDocxPreviewModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
        closePlaceholderGuide();
        closeDocxPreviewModal();
    }
});

function showTemplateClasses(classes) {
    let classesList = '';
    classes.forEach(kelas => {
        classesList += `<li class="py-1">${kelas}</li>`;
    });
    
    Swal.fire({
        title: 'Kelas yang Menggunakan Template',
        html: `<ul class="text-left list-disc pl-5">${classesList}</ul>`,
        confirmButtonText: 'Tutup'
    });
}

// Kelas dropdown functionality
function toggleKelasDropdown() {
    const dropdown = document.getElementById('kelas-dropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('kelas-dropdown');
    const button = document.getElementById('kelas-dropdown-btn');
    
    if (dropdown && button) {
        if (!dropdown.contains(event.target) && !button.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    }
});

// Toggle all kelas checkboxes
function toggleAllKelas(checkbox) {
    const isChecked = checkbox.checked;
    document.querySelectorAll('.kelas-checkbox').forEach(cb => {
        cb.checked = isChecked;
    });
    updateSelectedKelasText();
}

// Update the selected kelas text
function updateSelectedKelasText() {
    const checkboxes = document.querySelectorAll('.kelas-checkbox:checked');
    const selectAllCheckbox = document.getElementById('select-all-kelas');
    const selectedKelasElement = document.getElementById('selected-kelas');
    
    if (checkboxes.length === 0) {
        selectedKelasElement.textContent = 'Pilih Kelas';
        selectAllCheckbox.checked = false;
    } else if (checkboxes.length === document.querySelectorAll('.kelas-checkbox').length) {
        selectedKelasElement.textContent = 'Semua Kelas';
        selectAllCheckbox.checked = true;
    } else {
        if (checkboxes.length <= 2) {
            // Show the name of selected classes if only a few are selected
            const kelasNames = Array.from(checkboxes).map(cb => {
                return cb.parentElement.querySelector('span').textContent.trim();
            });
            selectedKelasElement.textContent = kelasNames.join(', ');
        } else {
            // Just show the count if many are selected
            selectedKelasElement.textContent = `${checkboxes.length} kelas dipilih`;
        }
        selectAllCheckbox.checked = false;
    }
}

</script>
@endpush
@endsection