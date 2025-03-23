@extends('layouts.app')

@section('title', 'Salin Tahun Ajaran')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Salin Tahun Ajaran</h2>
        <a href="{{ route('tahun.ajaran.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Terjadi kesalahan:</p>
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Current Tahun Ajaran Info -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Tahun Ajaran Sumber</h3>
        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Tahun Ajaran</p>
                    <p class="text-lg font-medium">{{ $sourceTahunAjaran->tahun_ajaran }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Semester</p>
                    <p class="text-lg font-medium">{{ $sourceTahunAjaran->semester }} ({{ $sourceTahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }})</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="text-lg font-medium">
                        @if($sourceTahunAjaran->is_active)
                        <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Tidak Aktif</span>
                        @endif
                    </p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Periode</p>
                    <p class="text-lg font-medium">{{ $sourceTahunAjaran->tanggal_mulai->format('d M Y') }} - {{ $sourceTahunAjaran->tanggal_selesai->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Copy Form -->
    <form action="{{ route('tahun.ajaran.process-copy', $sourceTahunAjaran->id) }}" method="POST" class="space-y-6" data-needs-protection>
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran Baru <span class="text-red-500">*</span></label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran" value="{{ old('tahun_ajaran', $newTahunAjaran) }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    placeholder="contoh: 2024/2025">
                <p class="mt-1 text-sm text-gray-500">Format: YYYY/YYYY (contoh: 2024/2025)</p>
            </div>

            <div>
                <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                <select name="semester" id="semester" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih Semester</option>
                    <option value="1" {{ old('semester', 1) == 1 ? 'selected' : '' }}>1 (Ganjil)</option>
                    <option value="2" {{ old('semester', 1) == 2 ? 'selected' : '' }}>2 (Genap)</option>
                </select>
            </div>

            <div>
                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                    value="{{ old('tanggal_mulai', date('Y-m-d', strtotime('+1 year', strtotime($sourceTahunAjaran->tanggal_mulai)))) }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>

            <div>
                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                    value="{{ old('tanggal_selesai', date('Y-m-d', strtotime('+1 year', strtotime($sourceTahunAjaran->tanggal_selesai)))) }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="md:col-span-2">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    placeholder="Berikan deskripsi singkat tentang tahun ajaran ini (opsional)">{{ old('deskripsi', 'Tahun Ajaran ' . $newTahunAjaran) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <h4 class="text-md font-semibold text-gray-800 mb-3">Data yang Akan Disalin</h4>
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div class="flex items-center">
                        <input type="checkbox" name="copy_kelas" id="copy_kelas" value="1" {{ old('copy_kelas', '1') ? 'checked' : '' }}
                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="copy_kelas" class="ml-2 block text-sm text-gray-700">
                            Salin struktur kelas (tanpa siswa)
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="copy_mata_pelajaran" id="copy_mata_pelajaran" value="1" {{ old('copy_mata_pelajaran', '1') ? 'checked' : '' }}
                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="copy_mata_pelajaran" class="ml-2 block text-sm text-gray-700">
                            Salin mata pelajaran, lingkup materi, dan tujuan pembelajaran
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="copy_templates" id="copy_templates" value="1" {{ old('copy_templates', '1') ? 'checked' : '' }}
                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="copy_templates" class="ml-2 block text-sm text-gray-700">
                            Salin template rapor
                        </label>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}
                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        Aktifkan tahun ajaran baru ini sekarang
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Jika diaktifkan, tahun ajaran lain akan dinonaktifkan secara otomatis.</p>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-8">
            <a href="{{ route('tahun.ajaran.index') }}"
                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Batal
            </a>
            <button type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Proses Penyalinan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validasi format tahun ajaran
        const tahunAjaranInput = document.getElementById('tahun_ajaran');
        tahunAjaranInput.addEventListener('blur', function() {
            const value = this.value.trim();
            const pattern = /^\d{4}\/\d{4}$/;
            
            if (value && !pattern.test(value)) {
                const errorMsg = document.createElement('p');
                errorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'tahun-ajaran-error');
                errorMsg.textContent = 'Format tahun ajaran harus YYYY/YYYY, contoh: 2024/2025';
                
                // Remove any existing error message
                const existingError = document.querySelector('.tahun-ajaran-error');
                if (existingError) existingError.remove();
                
                // Add the error message
                this.parentNode.appendChild(errorMsg);
                
                // Add invalid class to input
                this.classList.add('border-red-500');
            } else {
                // Remove error message if format is correct
                const existingError = document.querySelector('.tahun-ajaran-error');
                if (existingError) existingError.remove();
                
                // Remove invalid class
                this.classList.remove('border-red-500');
            }
        });
        
        // Validasi tanggal selesai harus setelah tanggal mulai
        const tanggalMulaiInput = document.getElementById('tanggal_mulai');
        const tanggalSelesaiInput = document.getElementById('tanggal_selesai');
        
        tanggalSelesaiInput.addEventListener('change', function() {
            if (tanggalMulaiInput.value && this.value) {
                const mulai = new Date(tanggalMulaiInput.value);
                const selesai = new Date(this.value);
                
                if (selesai <= mulai) {
                    const errorMsg = document.createElement('p');
                    errorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'tanggal-error');
                    errorMsg.textContent = 'Tanggal selesai harus setelah tanggal mulai';
                    
                    // Remove any existing error message
                    const existingError = document.querySelector('.tanggal-error');
                    if (existingError) existingError.remove();
                    
                    // Add the error message
                    this.parentNode.appendChild(errorMsg);
                    
                    // Add invalid class
                    this.classList.add('border-red-500');
                } else {
                    // Remove error message
                    const existingError = document.querySelector('.tanggal-error');
                    if (existingError) existingError.remove();
                    
                    // Remove invalid class
                    this.classList.remove('border-red-500');
                }
            }
        });
    });
</script>
@endpush
@endsection