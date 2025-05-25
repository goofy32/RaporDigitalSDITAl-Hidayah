{{-- resources/views/wali_kelas/catatan/siswa.blade.php --}}
@extends('layouts.wali_kelas.app')

@section('title', 'Catatan Siswa')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-sm mt-14">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-green-700">Catatan Siswa</h2>
            <p class="text-gray-600 mt-1">
                Siswa: <span class="font-semibold">{{ $siswa->nama }}</span> - 
                Kelas: <span class="font-semibold">{{ $siswa->kelas->nomor_kelas }} {{ $siswa->kelas->nama_kelas }}</span>
            </p>
        </div>
        <a href="{{ route('wali_kelas.student.index') }}" 
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            Kembali
        </a>
    </div>

    <!-- Form Catatan -->
    <form action="{{ route('wali_kelas.catatan.siswa.store', $siswa->id) }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 gap-6">
            <!-- Catatan Umum -->
            <div>
                <h3 class="bg-green-700 text-white px-4 py-2 rounded-t">Catatan Umum</h3>
                <div class="border border-gray-300 rounded-b p-4">
                    <label for="catatan_umum" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan umum untuk siswa ini
                    </label>
                    <textarea 
                        id="catatan_umum" 
                        name="catatan_umum" 
                        rows="4"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                        placeholder="Tulis catatan umum untuk siswa ini...">{{ $catatanList['umum']->catatan ?? old('catatan_umum') }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Maksimal 1000 karakter</p>
                </div>
            </div>

            <!-- Catatan UTS -->
            <div>
                <h3 class="bg-blue-600 text-white px-4 py-2 rounded-t">Catatan UTS (Tengah Semester)</h3>
                <div class="border border-gray-300 rounded-b p-4">
                    <label for="catatan_uts" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan khusus untuk rapor UTS
                    </label>
                    <textarea 
                        id="catatan_uts" 
                        name="catatan_uts" 
                        rows="4"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Tulis catatan khusus untuk rapor UTS...">{{ $catatanList['uts']->catatan ?? old('catatan_uts') }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Catatan ini akan muncul di rapor UTS. Maksimal 1000 karakter</p>
                </div>
            </div>

            <!-- Catatan UAS -->
            <div>
                <h3 class="bg-purple-600 text-white px-4 py-2 rounded-t">Catatan UAS (Akhir Semester)</h3>
                <div class="border border-gray-300 rounded-b p-4">
                    <label for="catatan_uas" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan khusus untuk rapor UAS
                    </label>
                    <textarea 
                        id="catatan_uas" 
                        name="catatan_uas" 
                        rows="4"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Tulis catatan khusus untuk rapor UAS...">{{ $catatanList['uas']->catatan ?? old('catatan_uas') }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Catatan ini akan muncul di rapor UAS. Maksimal 1000 karakter</p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end mt-6">
            <button type="submit" 
                    class="bg-green-700 text-white px-6 py-2 rounded hover:bg-green-800 focus:ring-4 focus:ring-green-300">
                Simpan Catatan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
    
    // Character counter
    textareas.forEach(textarea => {
        const maxLength = 1000;
        const counter = document.createElement('div');
        counter.className = 'text-sm text-gray-400 mt-1 text-right';
        textarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const length = textarea.value.length;
            counter.textContent = `${length}/${maxLength} karakter`;
            counter.className = length > maxLength ? 
                'text-sm text-red-500 mt-1 text-right' : 
                'text-sm text-gray-400 mt-1 text-right';
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
});
</script>
@endpush
@endsection