@extends('layouts.app')

@section('title', 'Detail Tahun Ajaran')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Detail Tahun Ajaran</h2>
        <div class="flex space-x-2">
            <a href="{{ route('tahun.ajaran.edit', $tahunAjaran->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('tahun.ajaran.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
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

    <!-- Basic Information -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Informasi Tahun Ajaran</h3>
            @if($tahunAjaran->is_active)
            <span class="ml-3 px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>
            @else
            <span class="ml-3 px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Tidak Aktif</span>
            
            <form action="{{ route('tahun.ajaran.set-active', $tahunAjaran->id) }}" method="POST" class="inline ml-2">
                @csrf
                <button type="submit" class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full hover:bg-blue-200" 
                    onclick="return confirm('Apakah Anda yakin ingin mengaktifkan tahun ajaran ini?')">
                    Aktifkan Sekarang
                </button>
            </form>
            @endif
        </div>
        
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Tahun Ajaran</p>
                    <p class="text-lg font-medium">{{ $tahunAjaran->tahun_ajaran }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Semester</p>
                    <p class="text-lg font-medium">{{ $tahunAjaran->semester }} ({{ $tahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }})</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Tanggal Mulai</p>
                    <p class="text-lg font-medium">{{ $tahunAjaran->tanggal_mulai->format('d F Y') }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Tanggal Selesai</p>
                    <p class="text-lg font-medium">{{ $tahunAjaran->tanggal_selesai->format('d F Y') }}</p>
                </div>
                
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Deskripsi</p>
                    <p class="text-base">{{ $tahunAjaran->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <p class="text-2xl font-bold text-blue-700">{{ $totalKelas }}</p>
                <p class="text-sm text-blue-600">Kelas</p>
            </div>
            
            <div class="bg-green-50 border border-green-100 rounded-lg p-4">
                <p class="text-2xl font-bold text-green-700">{{ $totalSiswa }}</p>
                <p class="text-sm text-green-600">Siswa</p>
            </div>
            
            <div class="bg-purple-50 border border-purple-100 rounded-lg p-4">
                <p class="text-2xl font-bold text-purple-700">{{ $totalMataPelajaran }}</p>
                <p class="text-sm text-purple-600">Mata Pelajaran</p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tindakan</h3>
        
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('tahun.ajaran.edit', $tahunAjaran->id) }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                <i class="fas fa-edit mr-2"></i>
                Edit Tahun Ajaran
            </a>
            
            <a href="{{ route('tahun.ajaran.copy', $tahunAjaran->id) }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-copy mr-2"></i>
                Salin ke Tahun Ajaran Baru
            </a>
            
            @if(!$tahunAjaran->is_active)
            <form action="{{ route('tahun.ajaran.set-active', $tahunAjaran->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    onclick="return confirm('Apakah Anda yakin ingin mengaktifkan tahun ajaran ini?')">
                    <i class="fas fa-check-circle mr-2"></i>
                    Aktifkan Tahun Ajaran
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection