<!-- resources/views/admin/report_format/index.blade.php -->
@extends('layouts.app')

@section('title', 'Format Rapor')

@section('content')
<div>
    <div class="p-4 bg-white mt-14 rounded-lg shadow">
        <!-- Header -->
        @if(session('error'))
        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-green-700">
                    Format Rapor - {{ strtoupper($type) }}
                </h2>
                <p class="text-sm text-gray-600">Kelola format rapor yang akan digunakan</p>
            </div>
            <button 
                data-modal-target="uploadModal" 
                data-modal-toggle="uploadModal" 
                class="px-4 py-2 text-white bg-green-700 rounded hover:bg-green-800"
            >
                Upload Format Baru
            </button>
        </div>

        <!-- Format List -->
<!-- Format List -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($formats as $format)
    <div class="border rounded-lg p-4 {{ $format->is_active ? 'border-green-500' : 'border-gray-300' }}">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h3 class="font-semibold">{{ $format->title }}</h3>
                <p class="text-sm text-gray-600">{{ $format->tahun_ajaran }}</p>
            </div>
            @if($format->is_active)
                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Aktif</span>
            @endif
        </div>

        <!-- Ganti bagian preview container -->
        <div class="preview-container bg-white p-4 rounded-lg border">
            <!-- Tampilkan placeholders sebagai preview sederhana -->
            <div class="mb-4">
                <h4 class="text-sm font-medium mb-2">Placeholders:</h4>
                <div class="grid grid-cols-2 gap-2">
                    @if($format->placeholders && is_array($format->placeholders))
                        @foreach($format->placeholders as $placeholder)
                            <div class="text-xs bg-gray-50 p-1 rounded">
                                {{ $placeholder }}
                            </div>
                        @endforeach
                    @else
                        <div class="text-xs text-gray-500">
                            Tidak ada placeholder tersedia
                        </div>
                    @endif
                </div>
            </div>

            <!-- Preview PDF jika tersedia -->
            @if($format->pdf_path)
                <div class="mt-4">
                    <embed 
                        src="{{ asset('storage/' . $format->pdf_path) }}"
                        type="application/pdf"
                        width="100%"
                        height="300px"
                        class="rounded border"
                    />
                </div>
            @endif

            <!-- Tombol download template -->
            <div class="mt-4 flex space-x-2">
                <a href="{{ asset('storage/' . $format->template_path) }}"
                class="text-sm text-blue-600 hover:text-blue-800"
                download>
                    Download DOCX Template
                </a>
                @if($format->pdf_path)
                    <a href="{{ asset('storage/' . $format->pdf_path) }}"
                    class="text-sm text-blue-600 hover:text-blue-800"
                    download>
                        Download PDF
                    </a>
                @endif
            </div>
        </div>
        
        <div class="flex space-x-2 mt-4">
            @if(!$format->is_active)
            <form action="{{ route('report_format.activate', $format) }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                    Aktifkan
                </button>
            </form>

            <a href="{{ route('report_format.preview', $format) }}"
               class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                Detail Preview
            </a>
            @endif
            <form action="{{ route('report_format.destroy', $format) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                    Hapus
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>


<!-- Upload Modal -->
<div id="uploadModal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <!-- Modal content -->
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Upload Format Rapor Baru
                </h3>
                <button type="button" data-modal-hide="uploadModal" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            <!-- Modal body -->
            <form action="{{ route('report_format.upload') }}" method="POST" enctype="multipart/form-data" class="p-4 md:p-5" id="uploadForm">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Judul Format</label>
                        <input type="text" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" required>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" required>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">File Template (.docx)</label>
                        <input type="file" 
                               name="template" 
                               accept=".docx" 
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" 
                               required>
                        <p class="mt-1 text-sm text-gray-500">Upload file DOCX yang berisi placeholder</p>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">File PDF</label>
                        <input type="file" 
                               name="pdf_file" 
                               accept=".pdf" 
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" 
                               required>
                        <p class="mt-1 text-sm text-gray-500">Upload file PDF untuk preview</p>
                    </div>
                </div>
                <button type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 text-sm font-medium text-white bg-green-700 rounded-lg hover:bg-green-800">
                    Upload
                </button>
            </form>
        </div>
    </div>
</div>

@endsection