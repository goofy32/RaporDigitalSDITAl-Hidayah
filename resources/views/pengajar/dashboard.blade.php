@extends('layouts.pengajar.app')

@section('title', 'Dashboard Pengajar')

@section('content')
<div class="p-4">
    <!-- Statistik Utama -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mt-14">
        <!-- Box KELAS -->
        <div class="p-4 bg-white rounded-lg shadow-md border">
            <p class="text-sm font-semibold text-gray-600">KELAS</p>
            <p class="text-xl font-bold text-green-600">4 Kelas</p>
        </div>
        <!-- Box SISWA -->
        <div class="p-4 bg-white rounded-lg shadow-md border">
            <p class="text-sm font-semibold text-gray-600">SISWA</p>
            <p class="text-xl font-bold text-green-600">80 Siswa</p>
        </div>
        <!-- Box MATA PELAJARAN -->
        <div class="p-4 bg-white rounded-lg shadow-md border">
            <p class="text-sm font-semibold text-gray-600">MATA PELAJARAN</p>
            <p class="text-xl font-bold text-green-600">5 Mata Pelajaran</p>
        </div>
    </div>

    <!-- Dropdown Pilih Kelas -->
    <div class="mt-8">
        <label for="kelas" class="block text-sm font-medium text-gray-700">Pilih Kelas</label>
        <select id="kelas" name="kelas" class="block w-full p-2 mt-1 rounded-lg border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
            <option value="">Pilih kelas...</option>
            <option value="1">Kelas 1</option>
            <option value="2">Kelas 2</option>
            <option value="3">Kelas 3</option>
        </select>
    </div>

    <!-- Progres Input Nilai -->
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Progres Input Nilai</h3>
        <div class="flex items-center justify-center">
            <div class="relative">
                <div class="w-32 h-32 rounded-full bg-green-100 flex items-center justify-center">
                    <p class="text-2xl font-bold text-green-600">75%</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
