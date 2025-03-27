@extends('layouts.app')

@section('title', 'Manajemen Tahun Ajaran')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Manajemen Tahun Ajaran</h2>
        <a href="{{ route('tahun.ajaran.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i> Tambah Tahun Ajaran
        </a>
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
                        <a href="{{ route('tahun.ajaran.show', $activeTahunAjaran->id) }}" class="px-3 py-1 bg-blue-500 text-white rounded-md mr-2 text-sm hover:bg-blue-600 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
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
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 px-4 border-b">
                            <div class="font-medium text-gray-900">{{ $tahunAjaran->tahun_ajaran }}</div>
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
                            @if($tahunAjaran->is_active)
                            <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 border-b text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('tahun.ajaran.show', $tahunAjaran->id) }}" class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Detail
                                </a>
                                <a href="{{ route('tahun.ajaran.edit', $tahunAjaran->id) }}" class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                                @if(!$tahunAjaran->is_active)
                                <form action="{{ route('tahun.ajaran.set-active', $tahunAjaran->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 flex items-center" onclick="return confirm('Apakah Anda yakin ingin mengaktifkan tahun ajaran ini?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Aktifkan
                                    </button>
                                </form>
                                <a href="{{ route('tahun.ajaran.copy', $tahunAjaran->id) }}" class="px-2 py-1 bg-indigo-500 text-white rounded hover:bg-indigo-600 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Salin
                                </a>
                                @else
                                <span class="px-2 py-1 bg-gray-300 text-gray-500 rounded cursor-not-allowed flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Aktif
                                </span>
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
@endsection 