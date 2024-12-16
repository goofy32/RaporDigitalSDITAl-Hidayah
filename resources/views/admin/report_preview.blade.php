@extends('layouts.app')

@section('title', 'Preview Format Rapor')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-green-700">
                    Preview Format: {{ $format->title }}
                </h2>
                <p class="text-sm text-gray-600">{{ $format->tahun_ajaran }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('report_format.index', ['type' => $format->type]) }}" 
                   class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5">
                    Kembali
                </a>
                <a href="{{ asset('storage/' . $format->template_path) }}"
                   class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5"
                   download>
                    Download Template
                </a>
            </div>
        </div>

        <!-- Preview Container -->
        <div class="preview-container bg-white p-4 rounded-lg border">
            <div class="mb-4">
                <h4 class="text-sm font-medium mb-2">Placeholders yang tersedia:</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                    @if($format->placeholders)
                        @foreach($format->placeholders as $placeholder)
                            <div class="text-xs bg-gray-50 p-1 rounded">
                                {{ $placeholder }}
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500">Tidak ada placeholder</p>
                    @endif
                </div>
            </div>
            
            <!-- PDF Preview with responsive height -->
            <div class="mt-4">
                <h4 class="text-sm font-medium mb-2">Preview PDF:</h4>
                <div class="w-full h-[600px] border rounded-lg overflow-hidden">
                    <embed 
                        src="{{ asset('storage/' . $format->pdf_path) }}"
                        type="application/pdf"
                        width="100%"
                        height="100%"
                        class="w-full h-full"
                    />
                </div>
            </div>
        </div>

        <!-- Format Info -->
        <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold mb-4">Informasi Template</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Tipe Rapor</p>
                    <p class="font-medium">{{ $format->type }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-medium">
                        @if($format->is_active)
                            <span class="text-green-600">Aktif</span>
                        @else
                            <span class="text-gray-600">Tidak Aktif</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection