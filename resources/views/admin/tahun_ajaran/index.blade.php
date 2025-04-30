@extends('layouts.app')

@section('title', 'Manajemen Tahun Ajaran')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Manajemen Tahun Ajaran</h2>
        <div class="flex gap-2">
            <a href="{{ route('tahun.ajaran.index', ['showArchived' => !$tampilkanArsip]) }}" 
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-150 ease-in-out"
            id="toggleArchiveBtn">
                <i class="fas fa-archive mr-2"></i> 
                {{ $tampilkanArsip ? 'Sembunyikan Arsip' : 'Tampilkan Arsip' }}
            </a>
            <a href="{{ route('tahun.ajaran.create') }}" 
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                <i class="fas fa-plus mr-2"></i> Tambah Tahun Ajaran
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Tahun Ajaran Aktif -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Tahun Ajaran Aktif</h3>
        @php
            $activeTahunAjaran = $tahunAjarans->where('is_active', true)->first();
        @endphp

        @if($activeTahunAjaran)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center">
                            <h4 class="text-xl font-bold text-green-800">{{ $activeTahunAjaran->tahun_ajaran }}</h4>
                            <span class="ml-3 px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">AKTIF</span>
                        </div>
                        <p class="text-green-600">Semester {{ $activeTahunAjaran->semester }} ({{ $activeTahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }})</p>
                        <p class="text-sm text-gray-600">{{ date('d F Y', strtotime($activeTahunAjaran->tanggal_mulai)) }} - {{ date('d F Y', strtotime($activeTahunAjaran->tanggal_selesai)) }}</p>
                    </div>
                    <div>
                        <a href="{{ route('tahun.ajaran.show', $activeTahunAjaran->id) }}" class="px-3 py-1 bg-green-600 text-white rounded-md mr-2 text-sm hover:bg-green-700 flex items-center transition duration-150 ease-in-out">
                            <img src="{{ asset('images/icons/detail.png') }}" alt="Detail" class="w-4 h-4 mr-1">
                            Detail
                        </a>
                    </div>
                </div>
                @if($activeTahunAjaran->deskripsi)
                <div class="mt-2 p-2 bg-white rounded border border-green-100">
                    <p class="text-sm text-gray-600">{{ $activeTahunAjaran->deskripsi }}</p>
                </div>
                @endif
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-700">Tidak ada tahun ajaran yang aktif saat ini. Silakan aktifkan salah satu tahun ajaran.</p>
            </div>
        @endif
    </div>

    <!-- Daftar Tahun Ajaran -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Daftar Semua Tahun Ajaran</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tahun Ajaran</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Semester</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal Mulai</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal Selesai</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($tahunAjarans as $tahunAjaran)
                    <tr class="hover:bg-gray-50 {{ $tahunAjaran->trashed() ? 'bg-gray-100' : '' }}">
                        <td class="py-4 px-4 border-b">
                            <div class="font-medium text-gray-900">
                                {{ $tahunAjaran->tahun_ajaran }}
                                @if($tahunAjaran->trashed())
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold text-orange-800 bg-orange-100 rounded-full">Diarsipkan</span>
                                @endif
                            </div>
                            @if($tahunAjaran->deskripsi)
                            <div class="text-sm text-gray-500">{{ Str::limit($tahunAjaran->deskripsi, 50) }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-4 border-b">
                            {{ $tahunAjaran->semester }} ({{ $tahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }})
                        </td>
                        <td class="py-4 px-4 border-b">
                            {{ date('d/m/Y', strtotime($tahunAjaran->tanggal_mulai)) }}
                        </td>
                        <td class="py-4 px-4 border-b">
                            {{ date('d/m/Y', strtotime($tahunAjaran->tanggal_selesai)) }}
                        </td>
                        <td class="py-4 px-4 border-b">
                            <div class="flex flex-col gap-1">
                                @if($tahunAjaran->trashed())
                                    <span class="px-2 py-1 text-xs font-semibold text-orange-800 bg-orange-100 rounded-full">Diarsipkan</span>
                                @elseif($tahunAjaran->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Tidak Aktif</span>
                                @endif
                                <span class="px-2 py-1 text-xs font-semibold {{ $tahunAjaran->semester == 1 ? 'text-blue-800 bg-blue-100' : 'text-purple-800 bg-purple-100' }} rounded-full">
                                    Semester {{ $tahunAjaran->semester }} ({{ $tahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }})
                                </span>
                            </div>
                        </td>
                        <td class="py-4 px-4 border-b text-sm">
                            <div class="flex space-x-4">
                                <!-- Tombol Detail -->
                                <a href="{{ route('tahun.ajaran.show', $tahunAjaran->id) }}" title="Detail">
                                    <img src="{{ asset('images/icons/detail.png') }}" alt="Detail" class="w-5 h-5">
                                </a>
                                
                                @if(!$tahunAjaran->trashed())
                                    <!-- Tombol Edit (hanya untuk yang tidak diarsipkan) -->
                                    <a href="{{ route('tahun.ajaran.edit', $tahunAjaran->id) }}" title="Edit">
                                        <img src="{{ asset('images/icons/edit.png') }}" alt="Edit" class="w-5 h-5">
                                    </a>
                                @endif
                                
                                @if($tahunAjaran->is_active)
                                    <!-- Tombol Sedang Aktif (disabled) -->
                                    <div class="tooltip" title="Untuk menonaktifkan, aktifkan tahun ajaran lain terlebih dahulu">
                                        <span class="text-gray-400 cursor-not-allowed">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                @else
                                    <!-- Tombol Aktifkan -->
                                    <form action="{{ route('tahun.ajaran.set-active', $tahunAjaran->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="border-0 bg-transparent p-0" title="Aktifkan"
                                                onclick="return confirm('Apakah Anda yakin ingin mengaktifkan tahun ajaran ini?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 hover:text-green-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>
                                    
                                    <!-- Tombol Salin -->
                                    <a href="{{ route('tahun.ajaran.copy', $tahunAjaran->id) }}" title="Salin">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 hover:text-green-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </a>
                                @endif

                                @if(!$tahunAjaran->is_active)
                                    @if($tahunAjaran->trashed())
                                        <!-- Tombol Restore untuk tahun ajaran yang diarsipkan -->
                                        <form action="{{ route('tahun.ajaran.restore', $tahunAjaran->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="border-0 bg-transparent p-0" title="Pulihkan" 
                                                    onclick="return confirm('Apakah Anda yakin ingin memulihkan tahun ajaran ini?')">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 hover:text-blue-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Tombol Arsip untuk tahun ajaran aktif -->
                                        <form action="{{ route('tahun.ajaran.destroy', $tahunAjaran->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="border-0 bg-transparent p-0" title="Arsipkan" 
                                                    onclick="return confirm('Apakah Anda yakin ingin mengarsipkan tahun ajaran {{ $tahunAjaran->tahun_ajaran }}?\n\nData terkait masih dapat diakses setelah diarsipkan dengan menampilkan tahun ajaran terarsip.')">
                                                <img src="{{ asset('images/icons/delete.png') }}" alt="Arsipkan" class="w-5 h-5">
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                            Tidak ada data tahun ajaran. Silakan tambahkan tahun ajaran baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Tooltip styles - untuk memastikan tooltip bekerja dengan baik */
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip[title]:hover:after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: rgba(55, 65, 81, 0.9);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    white-space: nowrap;
    font-size: 12px;
    pointer-events: none;
    z-index: 10;
}

/* Add transition effects */
.transition {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cek apakah kita sedang menampilkan arsip
        const isShowingArchived = {{ $tampilkanArsip ? 'true' : 'false' }};
        
        // Cek jumlah tahun ajaran yang diarsipkan
        const archivedCount = {{ $tahunAjarans->filter->trashed()->count() }};
        
        // Menangani klik tombol tampilkan/sembunyikan arsip
        const toggleBtn = document.getElementById('toggleArchiveBtn');
        
        // Hanya tampilkan notifikasi jika:
        // 1. Pengguna meminta untuk menampilkan arsip (bukan sembunyikan)
        // 2. Tidak ada arsip yang ditemukan
        if (isShowingArchived && archivedCount === 0) {
            // Tampilkan pesan SweetAlert
            Swal.fire({
                icon: 'info',
                title: 'Tidak Ada Arsip',
                text: 'Tidak ada tahun ajaran yang diarsipkan saat ini.',
                confirmButtonText: 'Mengerti'
            }).then((result) => {
                // Redirect ke halaman tanpa parameter showArchived
                if (result.isConfirmed) {
                    window.location.href = "{{ route('tahun.ajaran.index') }}";
                }
            });
        }
        
        // Hanya tambahkan listener jika:
        // 1. Belum menampilkan arsip
        // 2. Tidak ada arsip yang tersedia
        if (!isShowingArchived && archivedCount === 0 && toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak Ada Arsip',
                    text: 'Tidak ada tahun ajaran yang diarsipkan saat ini.',
                    confirmButtonText: 'Mengerti'
                });
            });
        }
    });
</script>
@endpush
@endsection