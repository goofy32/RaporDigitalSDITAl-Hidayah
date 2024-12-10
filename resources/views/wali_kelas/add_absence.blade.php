@extends('layouts.wali_kelas.app')

@section('title', 'Tambah Absensi')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-sm">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Form Tambah Absensi</h2>
    </div>

    <form action="{{ route('wali_kelas.absence.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Tanggal -->
        <div>
            <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" 
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 @error('tanggal') border-red-500 @enderror"
                value="{{ old('tanggal', date('Y-m-d')) }}" required>
            @error('tanggal')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Table untuk multiple students -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">NIS</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white border-b">
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{ $student->nis }}</td>
                        <td class="px-6 py-4">{{ $student->nama }}</td>
                        <td class="px-6 py-4">
                            <select name="status[{{ $student->id }}]" 
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-2">
            <button type="submit" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5">
                Simpan
            </button>
            <a href="{{ route('wali_kelas.absence.index') }}" class="text-gray-900 bg-gray-300 hover:bg-gray-400 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5">
                Kembali
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('formData', () => ({
            // Handle status change untuk show/hide keterangan field
            handleStatusChange(event, studentId) {
                const status = event.target.value;
                const keteranganInput = document.querySelector(`input[name="keterangan[${studentId}]"]`);
                
                if (status !== 'Hadir') {
                    keteranganInput.required = true;
                    keteranganInput.parentElement.classList.remove('hidden');
                } else {
                    keteranganInput.required = false;
                    keteranganInput.parentElement.classList.add('hidden');
                    keteranganInput.value = '';
                }
            }
        }));
    });

    // Initialize status handlers
    document.querySelectorAll('select[name^="status"]').forEach(select => {
        select.addEventListener('change', (event) => {
            const studentId = select.getAttribute('name').match(/\d+/)[0];
            Alpine.data('formData')().handleStatusChange(event, studentId);
        });
    });
</script>
@endpush
@endsection">