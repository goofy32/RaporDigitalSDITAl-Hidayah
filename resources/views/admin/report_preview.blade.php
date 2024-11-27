@extends('layouts.app')

@section('title', 'Preview Format Rapor')

@section('content')
<div class="p-4 bg-white mt-14 rounded-lg shadow">
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
            <div class="grid grid-cols-2 gap-2">
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
        
        <!-- PDF Preview -->
        <div class="mt-4">
            <h4 class="text-sm font-medium mb-2">Preview PDF:</h4>
            <embed 
                src="{{ asset('storage/' . $format->pdf_path) }}"
                type="application/pdf"
                width="100%"
                height="300px"
                class="rounded border"
            />
        </div>
    </div>

    <!-- Format Info -->
    <div class="mt-6 border-t pt-4">
        <h3 class="font-semibold mb-4">Informasi Template</h3>
        <div class="grid grid-cols-2 gap-4">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan fallback jika PDF tidak bisa di-embed
    const embed = document.querySelector('embed');
    embed.onerror = function() {
        embed.outerHTML = `
            <div class="p-6 text-center">
                <p class="text-gray-500 mb-4">Preview tidak tersedia dalam browser.</p>
                <a href="{{ asset('storage/' . $format->template_path) }}"
                   class="text-blue-600 hover:underline"
                   download>
                    Download untuk melihat
                </a>
            </div>
        `;
    };
});
</script>
@endpush
@endsection