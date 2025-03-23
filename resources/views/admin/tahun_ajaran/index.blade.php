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
                    <h4 class="text-xl font-bold text-green-800">{{ $activeTahunAjaran->tahun_ajaran }}</h4>
                    <p class="text-green-600">Semester {{ $activeTahunAjaran->semester }} ({{ $activeTahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }})</p>
                    <p class="text-sm text-gray-600">{{ date('d F Y', strtotime($activeTahunAjaran->tanggal_mulai)) }} - {{ date('d F Y', strtotime($activeTahunAjaran->tanggal_selesai)) }}</p>
                </div>
                <div>
                    <a href="{{ route('tahun.ajaran.show', $activeTahunAjaran->id) }}" class="px-3 py-1 bg-blue-500 text-white rounded-md mr-2 text-sm hover:bg-blue-600">
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
                                <a href="{{ route('tahun.ajaran.show', $tahunAjaran->id) }}" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('tahun.ajaran.edit', $tahunAjaran->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$tahunAjaran->is_active)
                                <form action="{{ route('tahun.ajaran.set-active', $tahunAjaran->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900" title="Aktifkan" onclick="return confirm('Apakah Anda yakin ingin mengaktifkan tahun ajaran ini?')">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                </form>
                                <a href="{{ route('tahun.ajaran.copy', $tahunAjaran->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Salin ke Tahun Ajaran Baru">
                                    <i class="fas fa-copy"></i>
                                </a>
                                @else
                                <span class="text-gray-400 cursor-not-allowed">
                                    <i class="fas fa-check-circle"></i>
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