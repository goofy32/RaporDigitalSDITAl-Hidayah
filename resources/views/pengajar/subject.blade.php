@extends('layouts.pengajar.app')

@section('title', 'Data Mata Pelajaran')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Data Mata Pelajaran</h2>
        </div>

        <div class="flex justify-start mb-4">
            <a href="{{ route('pengajar.subject.create') }}" class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                Tambah Data
            </a>
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
                        <th scope="col" class="px-6 py-3">Aksi</th>
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
                        <td class="px-6 py-4 text-center flex justify-around">
                            <!-- View TP Button -->
                            <a href="{{ route('pengajar.tujuan_pembelajaran.view', $subject->id) }}" class="text-blue-600 hover:underline" title="Lihat Tujuan Pembelajaran">
                                <img src="{{ asset('images/icons/detail.png') }}" alt="View Icon" class="w-5 h-5">
                            </a>

                            <!-- Edit TP Button -->
                            <a href="{{ route('pengajar.tujuan_pembelajaran.create', $subject->id) }}" class="text-blue-600 hover:underline" title="Edit Tujuan Pembelajaran">
                                <img src="{{ asset('images/icons/edittp.png') }}" alt="Edit TP Icon" class="w-8 h-5">
                            </a>
                        
                            <!-- Edit Subject Button -->
                            <a href="{{ route('pengajar.subject.edit', $subject->id) }}" class="text-yellow-600 hover:underline" title="Edit Mata Pelajaran">
                                <img src="{{ asset('images/icons/edit.png') }}" alt="Edit Icon" class="w-5 h-5">
                            </a>
                        
                            <!-- Delete Button -->
                            <form action="{{ route('pengajar.subject.destroy', $subject->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                        title="Hapus Mata Pelajaran">
                                    <img src="{{ asset('images/icons/delete.png') }}" alt="Delete Icon" class="w-5 h-5">
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr class="bg-white border-b">
                        <td colspan="7" class="px-6 py-4 text-center">Tidak ada data mata pelajaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $subjects->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection