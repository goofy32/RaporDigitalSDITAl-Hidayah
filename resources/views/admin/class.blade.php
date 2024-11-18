@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<div >
    <div class="p-4 bg-white mt-14">
        <!-- Header Data Kelas -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Data Kelas</h2>
        </div>

        <!-- Tombol Tambah Kelas -->
        <div class="flex justify-start mb-4">
            <a href="{{ route('kelas.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg">
                Tambah Kelas
            </a>
        </div>

        <!-- Search Bar -->
        <div class="w-full md:w-1/2 mb-4 md:mb-0">
            <form action="{{ route('kelas.index') }}" method="GET" class="flex items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2"
                    placeholder="Search">
                <button type="submit" class="ml-2 px-4 py-2 bg-green-600 text-white rounded-lg">Cari</button>
            </form>
        </div>

        <!-- Tabel Data Kelas -->
        <div class="overflow-x-auto mt-4">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Nomor</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Wali Kelas</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kelasList as $index => $kelas)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $index + $kelasList->firstItem() }}</td>
                        <td class="px-6 py-4">Kelas {{ $kelas->nomor_kelas }} - {{ $kelas->nama_kelas }}</td>
                        <td class="px-6 py-4">{{ $kelas->wali_kelas }}</td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <form action="{{ route('kelas.destroy', $kelas->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Hapus</button>
                            </form>
                            <a href="{{ route('kelas.edit', $kelas->id) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center">Tidak ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        <div>
            {{ $kelasList->withQueryString()->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection
