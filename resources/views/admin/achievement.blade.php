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
                    @foreach ($prestasis as $index => $prestasi)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                {{ $prestasi->kelas ? 'Kelas ' . $prestasi->kelas->nomor_kelas . ' - ' . $prestasi->kelas->nama_kelas : '-' }}
                            </td>                            
                            <td class="px-6 py-4">{{ $prestasi->siswa->nama ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $prestasi->jenis_prestasi }}</td>
                            <td class="px-6 py-4">{{ $prestasi->keterangan }}</td>
                            <td class="px-6 py-4 text-center flex justify-around">
                                <a href="{{ route('achievement.edit', $prestasi->id) }}" class="text-green-600 hover:underline">
                                    ✏
                                </a>
                                <form action="{{ route('achievement.destroy', $prestasi->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">🗑</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
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