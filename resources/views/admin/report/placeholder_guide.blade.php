<div class="p-6">
@if(isset($placeholders) && is_array($placeholders))
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Panduan Placeholder Rapor</h2>
        <button @click="showPlaceholderGuide = false" 
                class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    @if(count($placeholders) === 0)
        <div class="text-red-500">
            Variabel placeholders kosong
        </div>
    @else
        <!-- Search -->
        <div class="mb-4">
            <input type="text" 
                   x-model="placeholderSearch" 
                   placeholder="Cari placeholder..."
                   class="w-full px-4 py-2 border rounded-lg">
        </div>
    @endif

    <!-- Category Tabs -->
    <div x-data="{ activeCategory: 'siswa' }">
        <div class="flex space-x-2 mb-4 overflow-x-auto pb-2">
            @foreach(array_keys($placeholders) as $category)
                <button @click="activeCategory = '{{ $category }}'"
                        :class="{'bg-blue-100': activeCategory === '{{ $category }}'}"
                        class="px-3 py-1 rounded whitespace-nowrap">
                    {{ ucwords(str_replace('_', ' ', $category)) }}
                </button>
            @endforeach
        </div>

        @foreach($placeholders as $category => $items)
        <div x-show="activeCategory === '{{ $category }}'" class="space-y-2">
            <h3 class="font-medium mb-2">{{ ucwords(str_replace('_', ' ', $category)) }}</h3>
            
            @if($category === 'nilai')
            <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                <p class="text-sm text-yellow-800">
                    Untuk nilai mata pelajaran, gunakan format berikut:
                    <br>- nilai_[mata_pelajaran]_tp[nomor] untuk nilai per TP
                    <br>- nilai_[mata_pelajaran]_akhir untuk nilai akhir
                    <br>- predikat_[mata_pelajaran] untuk predikat (A/B/C/D)
                    <br>- capaian_[mata_pelajaran] untuk deskripsi capaian
                </p>
            </div>
            @endif

            @if($category === 'ekskul')
            <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                <p class="text-sm text-yellow-800">
                    Untuk ekstrakurikuler, gunakan format berikut:
                    <br>- ekskul[nomor]_nama untuk nama ekstrakurikuler
                    <br>- ekskul[nomor]_nilai untuk nilai
                    <br>- ekskul[nomor]_deskripsi untuk deskripsi
                </p>
            </div>
            @endif

            <div class="grid gap-2">
                @foreach($items as $placeholder)
                <div x-show="!placeholderSearch || '{{ $placeholder['key'] }}'.toLowerCase().includes(placeholderSearch.toLowerCase()) || '{{ $placeholder['description'] }}'.toLowerCase().includes(placeholderSearch.toLowerCase())"
                     class="p-2 border rounded hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <code class="text-sm bg-gray-100 px-1 rounded">${{ $placeholder['key'] }}</code>
                            <p class="text-sm text-gray-600">{{ $placeholder['description'] }}</p>
                        </div>
                        <button @click="copyPlaceholder('${' + '{{ $placeholder['key'] }}' + '}')"
                                class="text-blue-600 hover:text-blue-700">
                            Salin
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="text-red-500">
        Data placeholder tidak tersedia.
    </div>
@endif

    <!-- Download Template -->
    <div class="mt-8 pt-4 border-t">
        <h3 class="font-medium mb-2">Template Contoh</h3>
        <button @click="downloadSampleTemplate" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Download Template Contoh
        </button>
    </div>
</div>

@push('scripts')
<script>

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        showPlaceholderGuide = false;
    }
});
document.addEventListener('alpine:init', () => {
    Alpine.data('placeholderGuide', () => ({
        placeholderSearch: '',
        
        copyPlaceholder(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.$dispatch('show-feedback', {
                    type: 'success',
                    message: 'Placeholder berhasil disalin'
                });
            }).catch(() => {
                this.$dispatch('show-feedback', {
                    type: 'error',
                    message: 'Gagal menyalin placeholder'
                });
            });
        },

        async downloadSampleTemplate() {
            try {
                const response = await fetch('/admin/report-template/sample');
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'template_rapor_sample.docx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } catch (error) {
                this.$dispatch('show-feedback', {
                    type: 'error',
                    message: 'Gagal mengunduh template contoh'
                });
            }
        }
    }));
});
</script>
@endpush