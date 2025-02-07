@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Data Mata Pelajaran</h2>
        </div>

        <div class="flex justify-start mb-4">
            <a href="{{ route('subject.create') }}"" class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                Tambah Data 
            </a>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <form action="{{ route('subject.index') }}" method="GET" class="w-full" data-turbo="false">
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
  
        <!-- Table -->
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Mata Pelajaran</th>
                        <th scope="col" class="px-6 py-3">Kelas</th>
                        <th scope="col" class="px-6 py-3">Semester</th>
                        <th scope="col" class="px-6 py-3">Guru Pengampu</th>
                        <th scope="col" class="px-6 py-3">Lingkup Materi</th>
                        <th scope="col" class="px-6 py-3 text center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $index => $subject)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">{{ $subject->nama_pelajaran }}</td>
                        <td class="px-6 py-4">{{ $subject->kelas->nomor_kelas }}-{{ $subject->kelas->nama_kelas }}</td>
                        <td class="px-6 py-4">Semester {{ $subject->semester }}</td>
                        <td class="px-6 py-4">{{ $subject->guru->nama }}</td>
                        <td class="px-6 py-4">
                            @if($subject->lingkupMateris->isNotEmpty())
                                <ul class="list-disc list-inside">
                                    @foreach($subject->lingkupMateris as $lm)
                                        <li>{{ $lm->judul_lingkup_materi }}</li>
                                    @endforeach
                                </ul>
                            @else
                                Tidak ada Lingkup Materi
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                        <div class="flex space-x-2">
                            <!-- Lihat TP -->
                            <a href="{{ route('tujuan_pembelajaran.create', $subject->id) }}" class="text-blue-600 hover:underline">
                                <!-- Ikon Lihat TP -->
                                <img src="{{ asset('images/icons/edittp.png') }}" alt="Extracurricular Icon" class="w-8 h-5">

                            </a>
                        
                            <!-- Edit Data Mata Pelajaran -->
                            <a href="{{ route('subject.edit', $subject->id) }}" class="text-green-600 hover:underline">
                                <!-- Ikon Edit -->
                                <img src="{{ asset('images/icons/edit.png') }}" alt="Extracurricular Icon" class="w-5 h-5">

                            </a>
                        
                            <!-- Hapus Mata Pelajaran dan Lingkup Materi terkait -->
                            <form action="{{ route('subject.destroy', $subject->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    <!-- Ikon Hapus -->
                                    <img src="{{ asset('images/icons/delete.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                                </button>
                            </form>
                        </td>
                        </div>
                    </tr>
                    @empty
                    <tr class="bg-white border-b">
                        <td colspan="7" class="px-6 py-4 text-center">Tidak ada data mata pelajaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $subjects->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<!-- Flowbite JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
@endsection