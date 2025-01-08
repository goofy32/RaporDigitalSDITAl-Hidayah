@extends('layouts.app')

@section('title', 'Data Pengajar')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header Data Pengajar -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Data Pengajar</h2>
        </div>

        <!-- Tombol Tambah Data -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <a href="{{ route('teacher.create') }}" 
                class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                Tambah Data
            </a>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <form action="{{ route('teacher') }}" method="GET" class="w-full" data-turbo="false">
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

        <!-- Tabel Data Pengajar -->
        <div class="overflow-x-auto mt-4">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">NIP</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Username</th>
                        <th class="px-6 py-3">Jenis Kelamin</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">No Handphone</th>
                        <th class="px-6 py-3">Alamat</th>
                        <th class="px-6 py-3">Jabatan</th>
                        <th class="px-6 py-3">Kelas Mengajar</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($teachers->sortBy(function($teacher) {
                    return $teacher->kelasPengajar->nomor_kelas ?? PHP_INT_MAX;
                }) as $teacher)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}</td>
                    <td class="px-6 py-4">{{ $teacher->nuptk }}</td>
                    <td class="px-6 py-4">{{ $teacher->nama }}</td>
                    <td class="px-6 py-4">{{ $teacher->username }}</td>
                    <td class="px-6 py-4">{{ $teacher->jenis_kelamin }}</td>
                    <td class="px-6 py-4">{{ $teacher->email }}</td>
                    <td class="px-6 py-4">{{ $teacher->no_handphone }}</td>
                    <td class="px-6 py-4">{{ $teacher->alamat }}</td>
                    <td class="px-6 py-4">{{ $teacher->jabatan }}</td>
                    <td class="px-6 py-4">Kelas
                        {{ $teacher->kelasPengajar->nomor_kelas ?? '-' }} - {{ $teacher->kelasPengajar->nama_kelas ?? 'Belum Diisi' }}
                    </td>
                        <td class="px-1 py-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('teacher.show', $teacher->id) }}" class="text-blue-600 hover:text-blue-800">
                                <img src="{{ asset('images/icons/detail.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                            </a>
                            <a href="{{ route('teacher.edit', $teacher->id) }}" class="text-yellow-600 hover:text-yellow-800">
                                <img src="{{ asset('images/icons/edit.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                            </a>
                            <form action="{{ route('teacher.destroy', $teacher->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <img src="{{ asset('images/icons/delete.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                                </button>
                            </form>
                        </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-4 text-center">Tidak ada data pengajar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <div>
            {{ $teachers->withQueryString()->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

@endsection
