@extends('layouts.app')

@section('title', 'Data Prestasi')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Data Prestasi</h2>
        </div>

        <!-- Tombol Tambah Data -->
        <div class="flex justify-start mb-4">
            <a href="{{ route('achievement.create') }}" class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                Tambah Data
            </a>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <form action="{{ route('achievement.index') }}" method="GET" class="w-full" data-turbo="false">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2"
                        placeholder="Cari (contoh: kelas 1, nama siswa, NIS, atau NISN)">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Data Prestasi -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">NO</th>
                        <th scope="col" class="px-6 py-3">Kelas</th>
                        <th scope="col" class="px-6 py-3">Nama Siswa</th>
                        <th scope="col" class="px-6 py-3">Jenis Prestasi</th>
                        <th scope="col" class="px-6 py-3">Keterangan</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if($prestasis->isEmpty())
                        <tr class="bg-white border-b">
                            <td colspan="6" class="px-6 py-4 text-center">Tidak ada data prestasi.</td>
                        </tr>
                    @else
                        @foreach ($prestasis as $index => $prestasi)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                {{ $prestasi->kelas ? 'Kelas ' . $prestasi->kelas->nomor_kelas . ' - ' . $prestasi->kelas->nama_kelas : '-' }}
                            </td>                            
                            <td class="px-6 py-4">{{ $prestasi->siswa->nama ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $prestasi->jenis_prestasi }}</td>
                            <td class="px-6 py-4">{{ $prestasi->keterangan }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex space-x-2">
                                    <a href="{{ route('achievement.edit', $prestasi->id) }}" class="text-green-600 hover:text-green-800 transition-colors duration-200">
                                        <img src="{{ asset('images/icons/edit.png') }}" alt="Extracurricular Icon" class="w-5 h-5">

                                    </a>
                                    <form action="{{ route('achievement.destroy', $prestasi->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors duration-200">
                                            <img src="{{ asset('images/icons/delete.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div>
            {{ $prestasis->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

@endsection