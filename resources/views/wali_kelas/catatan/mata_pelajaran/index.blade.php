@extends('layouts.wali_kelas.app')

@section('title', 'Catatan Mata Pelajaran')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-sm mt-14">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-green-700">Catatan Mata Pelajaran</h2>
            <p class="text-gray-600 mt-1">
                Kelas: <span class="font-semibold">{{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}</span>
            </p>
        </div>
    </div>

    @if($mataPelajarans->isEmpty())
        <div class="text-center py-8">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ada Mata Pelajaran</h3>
            <p class="text-gray-500">Belum ada mata pelajaran untuk semester ini.</p>
        </div>
    @else
        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($mataPelajarans as $mataPelajaran)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    {{ $mataPelajaran->nama_pelajaran }}
                                </h3>
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ $mataPelajaran->guru->nama ?? 'Tidak ada guru' }}
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if($mataPelajaran->is_muatan_lokal)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Mulok
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Umum
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">Semester:</span>
                                <span class="font-medium text-gray-900">{{ $mataPelajaran->semester }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-4 border-t border-gray-100">
                            <a href="{{ route('wali_kelas.catatan.mata_pelajaran.show', $mataPelajaran->id) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Kelola Catatan
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Info Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-blue-900 mb-1">Informasi Catatan Mata Pelajaran</h4>
                    <p class="text-sm text-blue-700">
                        Anda dapat menambahkan catatan untuk setiap siswa pada mata pelajaran tertentu. 
                        Catatan ini akan muncul di rapor sesuai dengan placeholder yang telah ditentukan.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection