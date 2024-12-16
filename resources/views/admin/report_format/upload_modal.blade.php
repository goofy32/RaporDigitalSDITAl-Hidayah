<div id="uploadModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-start justify-between p-4 border-b rounded-t">
                <h3 class="text-xl font-semibold text-gray-900">
                    Upload Format Rapor {{ $type ?? 'UTS' }}
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="uploadModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>

            <!-- Modal body -->
            <div class="p-6 space-y-6">
                <form action="{{ route('report_format.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type ?? 'UTS' }}">
                    
                    <div class="mb-6">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Judul Format</label>
                        <input type="text" name="title" required
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>

                    <div class="mb-6">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" required
                               pattern="\d{4}/\d{4}" placeholder="2023/2024"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <p class="mt-1 text-sm text-gray-500">Format: YYYY/YYYY (contoh: 2023/2024)</p>
                    </div>

                    <div class="mb-6">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Template Word (.docx)</label>
                        <input type="file" name="template" required accept=".docx"
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50">
                        <p class="mt-1 text-sm text-gray-500">Upload file template dalam format .docx</p>
                    </div>

                    <div class="mb-6">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Preview PDF</label>
                        <input type="file" name="pdf_file" required accept=".pdf"
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50">
                        <p class="mt-1 text-sm text-gray-500">Upload file preview dalam format .pdf</p>
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <button type="button" data-modal-hide="uploadModal"
                                class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">
                            Batal
                        </button>
                        <button type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate tahun ajaran format
    const tahunAjaranInput = document.getElementById('tahun_ajaran');
    tahunAjaranInput.addEventListener('input', function(e) {
        let value = e.target.value;
        if (value.length === 4) {
            value += '/';
        }
        if (value.length > 9) {
            value = value.slice(0, 9);
        }
        e.target.value = value;
    });

    // File size validation
    const maxSize = 5 * 1024 * 1024; // 5MB
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size > maxSize) {
                alert('File terlalu besar. Maksimal ukuran file adalah 5MB.');
                e.target.value = '';
            }
        });
    });
});
</script>
@endpush