@extends('layouts.app')

@section('title', 'Manajemen Template Rapor')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Template Rapor {{ $type }}</h1>
        <button onclick="openPlaceholderGuide()" 
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
            Panduan Placeholder
        </button>
    </div>

    <div class="space-y-6">
        <!-- Upload Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-medium mb-4">Upload Template Baru</h2>
            <form action="{{ route('report.template.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="mb-4">
                    <input type="file" 
                           name="template"
                           accept=".docx"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500">Format yang diterima: .docx</p>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Upload Template
                </button>
            </form>
        </div>

        <!-- Templates List -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium mb-4">Daftar Template</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3">No</th>
                            <th class="px-6 py-3">Nama File</th>
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
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($template->created_at)->format('d M Y H:i') }}</td>
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
                                <div class="flex gap-2">
                                    <a href="{{ route('report.template.preview', $template->id) }}" 
                                       target="_blank"
                                       class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                        Preview
                                    </a>
                                    
                                    @if(!$template->is_active)
                                        <form action="{{ route('report.template.activate', $template->id) }}" 
                                              method="POST" 
                                              class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form action="{{ route('report.template.destroy', $template->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center">Belum ada template yang diupload</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Panduan Placeholder -->
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