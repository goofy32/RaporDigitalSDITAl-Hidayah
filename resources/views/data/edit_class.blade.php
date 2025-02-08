@extends('layouts.app')

@section('title', 'Edit Data Kelas')

@section('content')
<div class="p-6 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Edit Data Kelas</h2>
        <div>
            <a href="{{ route('kelas.index') }}" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit" form="editClassForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form Edit Data Kelas -->
    <form id="editClassForm" action="{{ route('kelas.update', $kelas->id) }}" method="post" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Nomor Kelas -->
        <div>
            <label for="nomor_kelas" class="block mb-2 text-sm font-medium text-gray-900">Nomor Kelas</label>
            <input type="number" 
                   id="nomor_kelas" 
                   name="nomor_kelas" 
                   value="{{ old('nomor_kelas', $kelas->nomor_kelas) }}"
                   min="1" 
                   max="99"
                   required
                   oninput="this.value = this.value.replace(/^0+/, '')"
                   class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            <small class="text-gray-500">Masukkan angka antara 1-99</small>
            @error('nomor_kelas')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Kelas -->
        <div>
            <label for="nama_kelas" class="block mb-2 text-sm font-medium text-gray-900">Nama Kelas</label>
            <input type="text" 
                   id="nama_kelas" 
                   name="nama_kelas" 
                   value="{{ old('nama_kelas', $kelas->nama_kelas) }}" 
                   required
                   class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('nama_kelas')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Wali Kelas -->
        <div>
            <label for="wali_kelas" class="block mb-2 text-sm font-medium text-gray-900">Wali Kelas</label>
            <input type="text" 
                   id="wali_kelas" 
                   name="wali_kelas" 
                   value="{{ old('wali_kelas', $kelas->wali_kelas) }}" 
                   required
                   class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('wali_kelas')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>

@section('scripts')
<script>
document.getElementById('nomor_kelas').addEventListener('input', function(e) {
    // Hapus angka 0 di depan
    if(this.value.length > 0) {
        this.value = parseInt(this.value.replace(/^0+/, '')) || '';
    }
    
    // Jika nilai 0, kosongkan input
    if(this.value === '0') {
        this.value = '';
    }
    
    // Batasi input maksimal 99
    if(parseInt(this.value) > 99) {
        this.value = '99';
    }
});
</script>
@endsection

@endsection