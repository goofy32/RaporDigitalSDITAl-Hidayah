@extends('layouts.wali_kelas.app')

@section('title', 'Data Absensi')

@section('content')
<div>
    <div class="p-4 mt-14">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Data Absensi</h2>
        </div>

        <div class="flex justify-start mb-4">
            <a href="{{ route('wali_kelas.absence.create') }}" class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                </svg>
                Tambah Data 
            </a>
        </div>

        <!-- Search Bar -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <form action="{{ route('wali_kelas.absence.index') }}" method="GET" class="w-full">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2"
                        placeholder="Cari siswa...">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">NIS</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Semester</th>
                        <th class="px-6 py-3">Sakit</th>
                        <th class="px-6 py-3">Izin</th>
                        <th class="px-6 py-3">Tanpa Keterangan</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                  @forelse($absensis as $index => $absensi)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{ $absensi->siswa->nis }}</td>
                        <td class="px-6 py-4">{{ $absensi->siswa->nama }}</td>
                        <td class="px-6 py-4">Semester {{ $absensi->semester }}</td>
                        <td class="px-6 py-4">{{ $absensi->sakit }}</td>
                        <td class="px-6 py-4">{{ $absensi->izin }}</td>
                        <td class="px-6 py-4">{{ $absensi->tanpa_keterangan }}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                            <a href="{{ route('wali_kelas.absence.edit', $absensi->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Ubah Data">
                                <img src="{{ asset('images/icons/edit.png') }}" alt="Edit Icon" class="w-5 h-5">
                            </a>
                                <form action="{{ route('wali_kelas.absence.destroy', $absensi->id) }}" method="POST" class="inline" title="Hapus Data">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        <img src="{{ asset('images/icons/delete.png') }}" alt="Delete Icon" class="w-5 h-5">
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center">Tidak ada data absensi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $absensis->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection