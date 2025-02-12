@extends('layouts.app')

@section('title', 'Manajemen Template Rapor')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Template Rapor {{ $type }}</h1>
        <button @click="openPlaceholderGuide" 
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
            Panduan Placeholder
        </button>
    </div>

    <div x-data="reportManager"
         x-init="initData('{{ $type }}')"
         class="space-y-6">
        <!-- Alert Feedback -->
        <div x-show="feedback.message" 
             x-transition
             :class="{'bg-green-100 border-green-400': feedback.type === 'success', 
                     'bg-red-100 border-red-400': feedback.type === 'error'}"
             class="p-4 rounded-lg border">
            <div class="flex justify-between">
                <p :class="{'text-green-700': feedback.type === 'success', 
                           'text-red-700': feedback.type === 'error'}" 
                   x-text="feedback.message"></p>
                <button @click="feedback.message = ''" class="text-gray-500 hover:text-gray-700">×</button>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-medium mb-4">Upload Template Baru</h2>
            <div class="mb-4">
                <input type="file" 
                       @change="handleFileUpload"
                       accept=".docx"
                       :disabled="loading"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-sm text-gray-500">Format yang diterima: .docx</p>
            </div>
            <div x-show="loading" class="mt-2">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
            </div>
        </div>

        <!-- Active Template Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-medium mb-4">Template Aktif</h2>
            <template x-if="currentTemplate">
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-medium" x-text="currentTemplate.filename"></h3>
                            <p class="text-sm text-gray-500" x-text="formatDate(currentTemplate.created_at)"></p>
                        </div>
                        <span x-show="currentTemplate.is_active" 
                              class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                            Aktif
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="previewTemplate"
                                :disabled="loading"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Preview
                        </button>
                        <template x-if="!currentTemplate.is_active">
                            <button @click="activateTemplate"
                                    :disabled="loading"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                Aktifkan
                            </button>
                        </template>
                        <button @click="deleteTemplate"
                                :disabled="loading"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Hapus
                        </button>
                    </div>
                </div>
            </template>
            <template x-if="!currentTemplate">
                <p class="text-gray-500">Belum ada template yang diupload</p>
            </template>
        </div>

        <!-- Modal Panduan Placeholder -->
        <div x-show="showPlaceholderGuide" 
             x-transition.opacity
             class="fixed inset-0 z-50 overflow-y-auto"
             @click.away="showPlaceholderGuide = false"
             x-cloak>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black opacity-50"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full mx-auto">
                    @include('admin.report.placeholder_guide')
                </div>
                <div class="fixed inset-0 bg-black opacity-50"></div>
                <div class="relative bg-white rounded-lg max-w-4xl w-full mx-auto">
                    @include('admin.report.placeholder_guide')
                </div>
            </div>
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