@extends('layouts.app')

@section('title', 'Data Semester ' . $semester . ' - ' . $tahunAjaran->tahun_ajaran)

@section('content')
<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">
            Data {{ $semester == 1 ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)' }} - {{ $tahunAjaran->tahun_ajaran }}
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('tahun.ajaran.show', $tahunAjaran->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali ke Detail
            </a>
            <a href="{{ route('tahun.ajaran.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    @if($snapshot)
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center">
            <h3 class="text-lg font-semibold text-blue-800">
                Informasi Snapshot
            </h3>
        </div>
        <p class="mt-2 text-sm text-gray-600">
            Snapshot diambil pada: {{ $snapshot->snapshot_date->format('d F Y H:i') }}
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="bg-white p-3 rounded shadow">
                <p class="text-sm text-gray-500">Mata Pelajaran</p>
                <p class="text-xl font-bold">{{ $snapshot->data['mata_pelajaran_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white p-3 rounded shadow">
                <p class="text-sm text-gray-500">Siswa</p>
                <p class="text-xl font-bold">{{ $snapshot->data['siswa_count'] ?? 0 }}</p>
            </div>
            <div class="bg-white p-3 rounded shadow">
                <p class="text-sm text-gray-500">Kelas</p>
                <p class="text-xl font-bold">{{ $snapshot->data['kelas_count'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Mata Pelajaran -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Mata Pelajaran</h3>
        
        @if($mataPelajarans->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 border-b text-left">Nama Pelajaran</th>
                            <th class="py-3 px-4 border-b text-left">Kelas</th>
                            <th class="py-3 px-4 border-b text-left">Guru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mataPelajarans as $mapel)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 border-b">{{ $mapel->nama_pelajaran }}</td>
                                <td class="py-3 px-4 border-b">
                                    @if($mapel->kelas)
                                        Kelas {{ $mapel->kelas->nomor_kelas }} {{ $mapel->kelas->nama_kelas }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 border-b">
                                    @if($mapel->guru)
                                        {{ $mapel->guru->nama }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500">Tidak ada data mata pelajaran untuk semester ini.</p>
        @endif
    </div>

    <!-- Report Templates -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Template Rapor</h3>
        
        @if($reportTemplates->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 border-b text-left">Nama File</th>
                            <th class="py-3 px-4 border-b text-left">Tipe</th>
                            <th class="py-3 px-4 border-b text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportTemplates as $template)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 border-b">{{ $template->filename }}</td>
                                <td class="py-3 px-4 border-b">{{ $template->type }}</td>
                                <td class="py-3 px-4 border-b">
                                    @if($template->is_active)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500">Tidak ada template rapor untuk semester ini.</p>
        @endif
    </div>
</div>
@endsection