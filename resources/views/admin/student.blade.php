@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header Data Siswa -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Data Siswa</h2>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('student.create') }}" 
                    class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                    </svg>
                    Tambah Data
                </a>
                <button id="uploadButton" data-turbo-permanent  class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    Upload Excel
                </button>
                <a href="{{ route('student.template') }}" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    Download Template
                </a>
            </div>
        </div>


        <!-- Search Bar -->

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <form action="{{ route('student') }}" method="GET" class="w-full">
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
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">NIS</th>
                        <th class="px-6 py-3">NISN</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Jenis Kelamin</th>
                        <th class="px-6 py-3">Aksi</th>
                        </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                        <td class="px-6 py-4">{{ $student->nis }}</td>
                        <td class="px-6 py-4">{{ $student->nisn }}</td>
                        <td class="px-6 py-4">{{ $student->nama }}</td>
                        <td class="px-6 py-4">{{ optional($student->kelas)->full_kelas ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $student->jenis_kelamin }}</td>
                <!-- Aksi dengan icon -->
                <td class="px-1 py-4 text-center flex space-x-2">
                    <a href="{{ route('student.show', $student->id) }}" class="text-blue-600 hover:text-blue-800">
                       <img src="{{ asset('images/icons/detail.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                    </a>
                    <a href="{{ route('student.edit', $student->id) }}" class="text-yellow-600 hover:text-yellow-800">
                        <img src="{{ asset('images/icons/edit.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                    </a>
                    <form action="{{ route('student.destroy', $student->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">
                            <img src="{{ asset('images/icons/delete.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                        </button>
                    </form>
                    </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center">Tidak ada data siswa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $students->withQueryString()->links('vendor.pagination.custom') }}
        </div>

        <div id="uploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Upload Data Siswa</h3>
                    <form action="{{ route('student.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <div class="mt-2">
                            <input type="file" name="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" accept=".xlsx,.xls">
                            <p class="mt-1 text-sm text-gray-500">File Excel (.xlsx, .xls)</p>
                        </div>
                        @error('file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="flex justify-between mt-4">
                            <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Tutup</button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('turbo:load', function () {
        const uploadButton = document.querySelector('#uploadButton');
        const uploadModal = document.querySelector('#uploadModal');
        const closeModal = document.querySelector('#closeModal');

        // Pastikan elemen ditemukan sebelum menambahkan event listener
        if (uploadButton) {
            uploadButton.addEventListener('click', () => {
                if (uploadModal) {
                    uploadModal.classList.remove('hidden');
                }
            });
        }

        if (closeModal) {
            closeModal.addEventListener('click', () => {
                if (uploadModal) {
                    uploadModal.classList.add('hidden');
                }
            });
        }

        if (uploadModal) {
            uploadModal.addEventListener('click', (e) => {
                if (e.target === uploadModal) {
                    uploadModal.classList.add('hidden');
                }
            });
        }
    });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

@endsection