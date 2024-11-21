@extends('layouts.app')

@section('title', 'Edit Data Kelas')

@section('content')
<div class="p-6 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Prestasi</h2>
        <div>
            <a href="{{ route('achievement.index') }}" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit" form="createPrestasiForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form Tambah Data Prestasi -->
    <form id="createPrestasiForm" action="{{ route('achievement.store') }}" method="post" class="space-y-6">
        @csrf

        <!-- Kelas -->
        <div>
            <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
            <select id="kelas" name="kelas" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
                <option value="">Pilih Kelas</option>
                @foreach ($kelas as $item)
                    <option value="{{ $item->id }}" {{ old('kelas') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                @endforeach
            </select>
            @error('kelas')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Siswa -->
        <div>
            <label for="nama_siswa" class="block mb-2 text-sm font-medium text-gray-900">Nama Siswa</label>
            <select id="nama_siswa" name="nama_siswa" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
                <option value="">Pilih Siswa</option>
                @foreach ($siswa as $item)
                    <option value="{{ $item->id }}" {{ old('nama_siswa') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                @endforeach
            </select>
            @error('nama_siswa')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Prestasi -->
        <div>
            <label for="jenis_prestasi" class="block mb-2 text-sm font-medium text-gray-900">Jenis Prestasi</label>
            <input type="text" id="jenis_prestasi" name="jenis_prestasi" value="{{ old('jenis_prestasi') }}" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('jenis_prestasi')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keterangan -->
        <div>
            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="4" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">{{ old('keterangan') }}</textarea>
            @error('keterangan')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>
@endsection
