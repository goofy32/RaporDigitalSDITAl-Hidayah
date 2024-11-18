@extends('layouts.app')

@section('title', 'Tambah Data Kelas')

@section('content')
<div class="p-6 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Kelas</h2>
        <div>
            <a href="{{ route('kelas.index') }}" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit" form="createClassForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form Tambah Data Kelas -->
    <form id="createClassForm" action="{{ route('kelas.store') }}" method="post" class="space-y-6">
        @csrf

        <!-- Nomor Kelas -->
        <div>
            <label for="nomor_kelas" class="block mb-2 text-sm font-medium text-gray-900">Nomor Kelas</label>
            <input type="number" id="nomor_kelas" name="nomor_kelas" value="{{ old('nomor_kelas') }}" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('nomor_kelas')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Kelas -->
        <div>
            <label for="nama_kelas" class="block mb-2 text-sm font-medium text-gray-900">Nama Kelas</label>
            <input type="text" id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('nama_kelas')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Wali Kelas -->
        <div>
            <label for="wali_kelas" class="block mb-2 text-sm font-medium text-gray-900">Wali Kelas</label>
            <input type="text" id="wali_kelas" name="wali_kelas" value="{{ old('wali_kelas') }}" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('wali_kelas')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>
@endsection
