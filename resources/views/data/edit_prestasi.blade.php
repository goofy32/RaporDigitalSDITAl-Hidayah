@extends('layouts.app')

@section('title', 'Edit Data Prestasi')

@section('content')
<div class="p-6 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Edit Data Prestasi</h2>
        <div>
            <a href="{{ route('achievement.index') }}" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            <button type="submit" form="updatePrestasiForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form Edit Data Prestasi -->
    <form id="updatePrestasiForm" action="{{ route('achievement.update', $prestasi->id) }}" method="post" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Kelas -->
        <div>
            <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
            <select id="kelas" name="kelas_id" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
                <option value="">Pilih Kelas</option>
                @foreach ($kelas as $item)
                    <option value="{{ $item->id }}" {{ $prestasi->kelas_id == $item->id ? 'selected' : '' }}>
                        Kelas {{ $item->nomor_kelas }} - {{ $item->nama_kelas }}
                    </option>
                @endforeach
            </select>
            @error('kelas_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Siswa -->
        <div>
            <label for="nama_siswa" class="block mb-2 text-sm font-medium text-gray-900">Nama Siswa</label>
            <select id="nama_siswa" name="siswa_id" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
                <option value="">Pilih Siswa</option>
                @foreach ($siswa as $item)
                    <option value="{{ $item->id }}" {{ $prestasi->siswa_id == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                @endforeach
            </select>
            @error('siswa_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Prestasi -->
        <div>
            <label for="jenis_prestasi" class="block mb-2 text-sm font-medium text-gray-900">Jenis Prestasi</label>
            <input type="text" id="jenis_prestasi" name="jenis_prestasi" value="{{ $prestasi->jenis_prestasi }}" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">
            @error('jenis_prestasi')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keterangan -->
        <div>
            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="4" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-gray-900">{{ $prestasi->keterangan }}</textarea>
            @error('keterangan')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>
@endsection