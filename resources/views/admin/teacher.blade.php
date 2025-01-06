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

        <!-- Search Bar -->
        <div class="flex w-full mb-4">
            <form action="{{ route('teacher') }}" method="GET" class="flex w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-l-lg focus:ring-green-500 focus:border-green-500 block p-2"
                    placeholder="Search">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-r-lg hover:bg-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
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
                        <th class="px-6 py-3 text-center">Aksi</th>
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
                        <td class="px-6 py-4 text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('teacher.show', $teacher->id) }}" class="text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('teacher.edit', $teacher->id) }}" class="text-yellow-600 hover:text-yellow-800">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            </a>
                            <form action="{{ route('teacher.destroy', $teacher->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
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
