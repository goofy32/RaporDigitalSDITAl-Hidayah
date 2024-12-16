@extends('layouts.app')

@section('title', 'Format Rapor ' . $type)

@section('content')
<div class="p-4 bg-white mt-14 rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Format Rapor {{ $type }}</h2>
        <button type="button" 
                data-modal-target="uploadModal" 
                data-modal-toggle="uploadModal"
                class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5">
            Upload Format Baru
        </button>
    </div>

    <!-- Content for upload will go here -->
    <div class="flex items-center justify-center p-8">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Format Rapor Kosong</h3>
            <p class="mt-1 text-sm text-gray-500">Upload format rapor baru untuk mulai.</p>
            <div class="mt-6">
                <button type="button" 
                        data-modal-target="uploadModal" 
                        data-modal-toggle="uploadModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Upload Format Baru
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-4 border-b rounded-t">
                <h3 class="text-xl font-semibold text-gray-900">
                    Upload Format Rapor {{ $type }}
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="uploadModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>

            <!-- Modal body -->
            <form action="{{ route('report_format.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Judul Format</label>
                        <input type="text" name="title" required
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" required
                               pattern="\d{4}/\d{4}" placeholder="2023/2024"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <p class="mt-1 text-sm text-gray-500">Format: YYYY/YYYY (contoh: 2023/2024)</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">File Template (.docx)</label>
                        <input type="file" name="template" required accept=".docx"
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50">
                        <p class="mt-1 text-sm text-gray-500">Upload file template dalam format .docx</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">File Preview (.pdf)</label>
                        <input type="file" name="pdf_file" required accept=".pdf"
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50">
                        <p class="mt-1 text-sm text-gray-500">Upload file preview dalam format .pdf</p>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="flex items-center p-6 space-x-2 border-t">
                    <button type="submit"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Upload
                    </button>
                    <button type="button"
                            data-modal-hide="uploadModal"
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tahun ajaran input format
    const tahunAjaranInput = document.querySelector('input[name="tahun_ajaran"]');
    if (tahunAjaranInput) {
        tahunAjaranInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.slice(0,4) + '/' + value.slice(4,8);
            }
            e.target.value = value;
        });
    }
});
</script>
@endpush
@endsection