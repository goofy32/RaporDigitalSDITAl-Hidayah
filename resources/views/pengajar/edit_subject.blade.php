@extends('layouts.pengajar.app')

@section('title', 'Edit Data Mata Pelajaran')

@section('content')
<div>
    <div class="p-6 bg-white mt-14">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Form Edit Data Mata Pelajaran</h2>
            <div>
                <button onclick="window.history.back()" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Kembali
                </button>
                <button type="submit" form="editSubjectForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Simpan
                </button>
            </div>
        </div>

        <!-- Form -->
        <form id="editSubjectForm" 
            action="{{ route('pengajar.subject.update', $subject->id) }}" 
            x-data="formProtection"
            @submit.prevent="handleNormalSubmit"
            method="POST" 
            class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Mata Pelajaran -->
            <div>
                <label for="mata_pelajaran" class="block mb-2 text-sm font-medium text-gray-900">Mata Pelajaran</label>
                <input type="text" id="mata_pelajaran" name="mata_pelajaran" value="{{ $subject->nama_pelajaran }}" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Kelas Dropdown -->
            <div>
                <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
                <select id="kelas" name="kelas" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih Kelas</option>
                    @if($classes->isEmpty())
                        <option value="" disabled>Tidak ada kelas yang ditugaskan</option>
                    @else
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $subject->kelas_id == $class->id ? 'selected' : '' }}>
                                Kelas {{ $class->nomor_kelas }} {{ $class->nama_kelas }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @if($classes->isEmpty())
                    <p class="mt-2 text-sm text-red-600">Anda belum ditugaskan ke kelas manapun. Silakan hubungi admin.</p>
                @endif
            </div>
            <!-- Semester Dropdown -->
            <div>
                <label for="semester" class="block mb-2 text-sm font-medium text-gray-900">Semester</label>
                <select id="semester" name="semester" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih Semester</option>
                    <option value="1" {{ $subject->semester == 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ $subject->semester == 2 ? 'selected' : '' }}>Semester 2</option>
                </select>
            </div>

            <!-- Lingkup Materi -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Lingkup Materi</label>
                <div id="lingkupMateriContainer">
                @foreach($subject->lingkupMateris as $index => $lm)
                <div class="flex items-center mb-2">
                    <input type="text" name="lingkup_materi[]" value="{{ $lm->judul_lingkup_materi }}" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    @if($index == 0)
                        <button type="button" onclick="addLingkupMateri()" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @else
                        <button type="button" onclick="removeLingkupMateri(this, {{ $lm->id }})" class="ml-2 p-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endforeach
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Script sama seperti di add_subject.blade.php
    function addLingkupMateri() {
        const container = document.getElementById('lingkupMateriContainer');
        const div = document.createElement('div');
        div.className = 'flex items-center mb-2';
        
        div.innerHTML = `
            <input type="text" name="lingkup_materi[]" required
                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            <button type="button" onclick="removeLingkupMateri(this)" class="ml-2 p-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        `;
        
        container.appendChild(div);
        
        // Tandai form berubah saat menambah Lingkup Materi baru
        Alpine.store('formProtection').markAsChanged();
        
        // Tambahkan listener untuk input baru
        div.querySelector('input').addEventListener('change', () => {
            Alpine.store('formProtection').markAsChanged();
        });
    }
    
    function removeLingkupMateri(button, id) {
        if (confirm('Apakah Anda yakin ingin menghapus Lingkup Materi ini?')) {
            Alpine.store('formProtection').startSubmitting(); // Tandai sedang submit
            
            fetch(`/pengajar/lingkup-materi/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    button.closest('.flex.items-center').remove();
                    Alpine.store('formProtection').markAsChanged(); // Tandai form berubah
                } else {
                    alert('Gagal menghapus Lingkup Materi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus Lingkup Materi');
            })
            .finally(() => {
                Alpine.store('formProtection').isSubmitting = false; // Reset flag submit
            });
        }
    }
</script>
@endsection