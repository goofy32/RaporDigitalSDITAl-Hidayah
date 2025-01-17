@extends('layouts.wali_kelas.app')

@section('title', 'Dashboard Wali Kelas')

@section('content')
<div class="p-4">
    <!-- Statistik Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-14">
        <div class="flex flex-col justify-center h-24 rounded-lg shadow bg-white border border-gray-200 p-4">
            <p class="text-sm font-semibold text-gray-600">TAHUN AJARAN</p>
            <p class="text-lg font-bold text-green-600">{{ $schoolProfile->tahun_pelajaran ?? '-' }}</p>
        </div>
        <div class="flex flex-col justify-center h-24 rounded-lg shadow bg-white border border-gray-200 p-4">
            <p class="text-sm font-semibold text-gray-600">SISWA</p>
            <p class="text-lg font-bold text-green-600">{{ $totalSiswa }} Siswa</p>
        </div>
        <div class="flex flex-col justify-center h-24 rounded-lg shadow bg-white border border-gray-200 p-4">
            <p class="text-sm font-semibold text-gray-600">MATA PELAJARAN</p>
            <p class="text-lg font-bold text-green-600">{{ $totalMapel }} Mata Pelajaran</p>
        </div>
    </div>

    <!-- Informasi Section -->
    <div class="mt-8">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <h2 class="text-lg font-semibold">Informasi</h2>
            </div>
            
            <!-- Timeline notifikasi -->
            <div class="relative pl-6 border-l-2 border-gray-200">
                @foreach($notifications as $notification)
                <div class="mb-4 relative">
                    <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="bg-white rounded-lg border shadow-sm p-3">
                        <div>
                            <h3 class="text-sm font-medium">{{ $notification->title }}</h3>
                            <p class="text-xs text-gray-600">{{ $notification->content }}</p>
                            <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection