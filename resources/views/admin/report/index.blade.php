@extends('layouts.app')

@section('title', 'Manajemen Template Rapor')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Template Rapor {{ $type }}</h2>
        </div>

        <!-- Buttons Section -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <!-- Upload Button -->
            <div class="flex gap-2">
                <button onclick="openUploadModal()" 
                        class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                    </svg>
                    Upload Template
                </button>
                <button onclick="openPlaceholderGuide()" 
                        class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    Panduan Placeholder
                </button>
            </div>
        </div>

        <!-- Templates List Section -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
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
                        <td class="px-6 py-4">{{ $template->filename }}</td>
                        <td class="px-6 py-4">{{ $template->tahun_ajaran ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $template->semester ?? '-' }}</td>
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
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
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
                        <td colspan="7" class="px-6 py-4 text-center">Belum ada template yang diupload</td>
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
                <h3 class="text-lg font-medium">Upload Template Baru</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="uploadForm" action="{{ route('report.template.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran</label>
                        <select name="tahun_ajaran" required 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Tahun Ajaran</option>
                            @php
                                $currentYear = date('Y');
                                for($i = $currentYear - 1; $i <= $currentYear + 1; $i++) {
                                    $tahunAjaran = $i . '/' . ($i + 1);
                                    echo "<option value='{$tahunAjaran}'>{$tahunAjaran}</option>";
                                }
                            @endphp
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <select name="semester" required 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Semester</option>
                            <option value="1">Ganjil</option>
                            <option value="2">Genap</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">File Template</label>
                        <input type="file" 
                               name="template"
                               required
                               accept=".docx"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">Format yang diterima: .docx</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button"
                            onclick="closeUploadModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Panduan -->
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
// Modal functions
function openUploadModal() {
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
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengaktifkan template');
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

// Handle form submit
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal mengupload template');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupload template');
            }
        });
    }
});

async function handleDelete(e) {
    e.preventDefault();
    
    if (!confirm('Apakah Anda yakin ingin menghapus template ini?')) {
        return false;
    }

    const form = e.target;
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
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus template');
    }
    
    return false;
}
document.addEventListener('alpine:init', () => {
    Alpine.data('reportTemplateManager', () => ({
        currentTemplate: null,
        feedback: {
            type: '',
            message: ''
        },
        loading: false,
        showPlaceholderGuide: false,

        init() {
            this.fetchCurrentTemplate();
        },

        async fetchCurrentTemplate() {
            try {
                const response = await fetch(`/admin/report-template/current?type={{ $type }}`);
                const data = await response.json();
                this.currentTemplate = data.template;
            } catch (error) {
                console.error('Error fetching template:', error);
            }
        },

        openPlaceholderGuide() {
            this.showPlaceholderGuide = true;
        },

        async handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.name.endsWith('.docx')) {
                this.showFeedback('error', 'File harus berformat .docx');
                return;
            }

            const formData = new FormData();
            formData.append('template', file);
            formData.append('type', '{{ $type }}');

            try {
                this.loading = true;
                const response = await fetch('/admin/report-template/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    this.currentTemplate = result.template;
                    this.showFeedback('success', 'Template berhasil diupload');
                    event.target.value = '';
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                this.showFeedback('error', error.message || 'Gagal mengupload template');
            } finally {
                this.loading = false;
            }
        },

        async activateTemplate() {
            if (!this.currentTemplate) return;

            try {
                this.loading = true;
                const response = await fetch(`/admin/report-template/${this.currentTemplate.id}/activate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    this.currentTemplate.is_active = true;
                    this.showFeedback('success', 'Template berhasil diaktifkan');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                this.showFeedback('error', error.message || 'Gagal mengaktifkan template');
            } finally {
                this.loading = false;
            }
        },

        previewTemplate() {
            if (!this.currentTemplate) return;
            window.open(`/admin/report-template/${this.currentTemplate.id}/preview`, '_blank');
        },

        async deleteTemplate() {
            if (!this.currentTemplate || !confirm('Anda yakin ingin menghapus template ini?')) return;

            try {
                this.loading = true;
                const response = await fetch(`/admin/report-template/${this.currentTemplate.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    this.currentTemplate = null;
                    this.showFeedback('success', 'Template berhasil dihapus');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                this.showFeedback('error', error.message || 'Gagal menghapus template');
            } finally {
                this.loading = false;
            }
        },

        showFeedback(type, message) {
            this.feedback = { type, message };
            setTimeout(() => {
                this.feedback.message = '';
            }, 3000);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }
    }));
});
</script>
@endpush
@endsection