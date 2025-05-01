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
            <!-- Form Pencarian: Ubah dari data-turbo="false" menjadi data-turbo-search -->
            <form action="{{ route('teacher') }}" method="GET" class="w-full" data-turbo-search>
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2"
                        placeholder="Cari pengajar berdasarkan NUPTK, Nama, Username, Email">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Debug Info (only for development) -->
        @if(app()->environment('local', 'development'))
        <div class="mb-4 p-2 bg-gray-100 text-xs rounded">
            <p>Debugging:</p>
            <p>Current Search Term: "{{ request('search') }}"</p>
            <p>Total Results: {{ $teachers->total() }}</p>
            <p>Current Page: {{ $teachers->currentPage() }}</p>
        </div>
        @endif

        <!-- Tabel Data Pengajar -->
        <div class="overflow-x-auto mt-4">
            <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">NUPTK</th>
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
                @forelse ($teachers as $teacher)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}</td>
                    <td class="px-6 py-4">{{ $teacher->nuptk }}</td>
                    <td class="px-6 py-4">{{ $teacher->nama }}</td>
                    <td class="px-6 py-4">{{ $teacher->username }}</td>
                    <td class="px-6 py-4">{{ $teacher->jenis_kelamin }}</td>
                    <td class="px-6 py-4">{{ $teacher->email }}</td>
                    <td class="px-6 py-4">{{ $teacher->no_handphone }}</td>
                    <td class="px-6 py-4">{{ $teacher->alamat }}</td>
                    <td class="px-6 py-4">
                        @if($teacher->jabatan == 'guru_wali')
                            Guru dan Wali Kelas
                        @else
                            Guru
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @php
                            // Ambil semua kelas terkait guru (baik sebagai pengajar maupun wali kelas)
                            $kelasAll = $teacher->kelas->map(function($kelas) {
                                $roleInfo = $kelas->pivot->is_wali_kelas ? ' (Wali Kelas)' : '';
                                return "Kelas {$kelas->nomor_kelas} {$kelas->nama_kelas}{$roleInfo}";
                            })->join(', ');
                        @endphp
                        {{ $kelasAll ?: '-' }}
                    </td>
                    <td class="px-1 py-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('teacher.show', $teacher->id) }}" class="text-blue-600 hover:text-blue-800">
                                <img src="{{ asset('images/icons/detail.png') }}" alt="Detail Icon" class="w-5 h-5">
                            </a>
                            <a href="{{ route('teacher.edit', $teacher->id) }}" class="text-yellow-600 hover:text-yellow-800">
                                <img src="{{ asset('images/icons/edit.png') }}" alt="Edit Icon" class="w-5 h-5">
                            </a>
                            <form action="{{ route('teacher.destroy', $teacher->id) }}" method="POST" 
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" 
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <img src="{{ asset('images/icons/delete.png') }}" alt="Delete Icon" class="w-5 h-5">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhance search form with real-time feedback
    const searchForm = document.querySelector('form[action="{{ route('teacher') }}"]');
    const searchInput = searchForm.querySelector('input[name="search"]');
    
    // Submit formulir saat tekan Enter
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            searchForm.submit();
        }
    });
    
    searchForm.addEventListener('submit', function(e) {
        // Show loading indicator
        const submitButton = this.querySelector('button[type="submit"]');
        const originalContent = submitButton.innerHTML;
        
        submitButton.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
        
        // Setelah form terkirim, kembalikan tombol ke kondisi normal
        setTimeout(function() {
            submitButton.innerHTML = originalContent;
        }, 2000);
    });
    
    // Auto focus search field if it has a value
    if (searchInput.value) {
        searchInput.focus();
        searchInput.select();
    }
});
</script>
@endpush
@endsection