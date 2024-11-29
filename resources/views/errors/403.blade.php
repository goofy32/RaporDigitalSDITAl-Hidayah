@extends('layouts.pengajar.app')

@section('title', '403 Forbidden')

@section('content')
<div class="flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-red-600 mb-4">403</h1>
        <p class="text-xl mb-4">Akses Ditolak</p>
        <p class="mb-4">Anda tidak memiliki akses ke halaman ini.</p>
        <a href="{{ route('pengajar.dashboard') }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection