@extends('layouts.pengajar.app')

@section('title', 'Data Pembelajaran')

@section('content')
<div class="p-4 bg-white shadow-md rounded-lg">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Data Pembelajaran</h2>
        <input 
            type="text" 
            placeholder="Cari..." 
            class="border border-gray-300 rounded-lg px-4 py-2 w-1/4"
        >
    </div>

    <!-- Tabel Data Pembelajaran -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">No</th>
                    <th scope="col" class="px-6 py-3">Kelas</th>
                    <th scope="col" class="px-6 py-3">Mata Pelajaran</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subjects as $index => $subject)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <!-- Nomor -->
                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                    
                    <!-- Kelas -->
                    <td class="px-6 py-4">{{ $subject['class'] }}</td>
                    
                    <!-- Mata Pelajaran -->
                    <td class="px-6 py-4">{{ $subject['name'] }}</td>
                    
                    <!-- Aksi -->
                    <td class="px-6 py-4 flex space-x-2">
                        <a href="{{ route('pengajar.input_score', $subject['id']) }}" 
                            class="text-green-600 hover:text-green-800">
                            <i class="fas fa-eye"></i> Input Nilai
                        </a>
                        <button type="button" class="text-red-600 hover:text-red-800" onclick="alert('Simulasi hapus data!');">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
