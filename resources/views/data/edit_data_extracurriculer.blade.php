@extends('layouts.app')

@section('title', 'Edit Data Ekstrakurikuler')

@section('content')
<div class="p-6 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Edit Data Ekstrakurikuler</h2>
        <div>
            <button onclick="window.history.back()" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Kembali
            </button>
            <button type="submit" form="editExtraForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="editExtraForm" action="{{ route('ekstra.update', $ekstrakurikuler->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Nama Ekstrakurikuler -->
        <div>
            <label for="nama_ekstrakurikuler" class="block mb-2 text-sm font-medium text-gray-900">Nama Ekstrakurikuler</label>
            <input type="text" id="nama_ekstrakurikuler" name="nama_ekstrakurikuler" 
                   value="{{ old('nama_ekstrakurikuler', $ekstrakurikuler->nama_ekstrakurikuler) }}"
                   required
                   class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('nama_ekstrakurikuler') border-red-500 @enderror">
            @error('nama_ekstrakurikuler')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Pembina -->
        <div>
            <label for="pembina" class="block mb-2 text-sm font-medium text-gray-900">Pembina</label>
            <input type="text" id="pembina" name="pembina" 
                   value="{{ old('pembina', $ekstrakurikuler->pembina) }}"
                   required
                   class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('pembina') border-red-500 @enderror">
            @error('pembina')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </form>
</div>
@endsection