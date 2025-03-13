@extends('layouts.app')

@section('title', 'Tambah Data Mata Pelajaran')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-2xl font-bold text-green-700 break-words max-w-full sm:max-w-lg">Form Tambah Data Mata Pelajaran</h2>
            <div class="flex flex-wrap gap-2">
                <button onclick="window.history.back()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Kembali
                </button>
                <button type="submit" form="addSubjectForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Simpan Semua
                </button>
            </div>
        </div>

        <!-- Flash Message untuk Error/Success -->
        @if(session('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
            <p>{{ session('error') }}</p>
        </div>
        @endif

        @if(session('success'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p>{{ session('success') }}</p>
        </div>
        @endif

        @if(session('errors') && count(session('errors')) > 0)
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
            <h4 class="font-medium">Terjadi beberapa kesalahan:</h4>
            <ul class="ml-4 mt-2 list-disc">
                @foreach(session('errors') as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Form -->
        <form id="addSubjectForm" action="{{ route('subject.store') }}" method="POST" @submit="handleSubmit" x-data="formProtection" class="space-y-6" data-needs-protection>
            @csrf

            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Data Mata Pelajaran</h3>
                <button type="button" onclick="addSubjectEntry()" class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Mata Pelajaran
                </button>
            </div>

            <!-- Multiple Subject Entry Form -->
            <div id="subjectEntriesContainer">
                <!-- Template for a subject entry -->
                <div class="subject-entry bg-gray-50 p-4 rounded-lg mb-6" data-previous-muatan-lokal="false">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-md font-medium text-gray-800">Mata Pelajaran 1</h4>
                        <button type="button" onclick="removeSubjectEntry(this)" class="text-red-600 hover:text-red-800 hidden remove-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mata Pelajaran -->
                    <div class="mb-4">
                        <label for="mata_pelajaran_0" class="block mb-2 text-sm font-medium text-gray-900">Nama Mata Pelajaran</label>
                        <input type="text" id="mata_pelajaran_0" name="subjects[0][mata_pelajaran]" value="{{ old('mata_pelajaran') }}" required
                            class="block w-full p-2.5 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Muatan Lokal Checkbox -->
                    <div class="mb-4">
                        <div class="flex items-center">
                            <input id="is_muatan_lokal_0" name="subjects[0][is_muatan_lokal]" type="checkbox" 
                                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded muatan-lokal-checkbox"
                                onchange="updateGuruOptions(this.closest('.subject-entry'))">
                            <label for="is_muatan_lokal_0" class="ml-2 block text-sm text-gray-900">
                                Tandai sebagai Muatan Lokal
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Muatan lokal hanya dapat diajar oleh guru dengan jabatan guru (bukan wali kelas)</p>
                    </div>

                    <!-- Opsi Non-muatan lokal dengan guru bukan wali kelas -->
                    <div class="mb-4 non-muatan-lokal-options" style="display: none;">
                        <div class="flex items-center">
                            <input id="allow_non_wali_0" name="subjects[0][allow_non_wali]" type="checkbox" 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded allow-non-wali-checkbox"
                                onchange="updateGuruOptions(this.closest('.subject-entry'))">
                            <label for="allow_non_wali_0" class="ml-2 block text-sm text-gray-900">
                                Pelajaran wajib dengan guru bukan wali kelas
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Centang ini jika ingin mengizinkan guru biasa (bukan wali kelas) mengajar mata pelajaran non-muatan lokal</p>
                    </div>

                    <!-- Kelas Dropdown (Single Select) -->
                    <div class="mb-4">
                        <label for="kelas_0" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
                        <select id="kelas_0" name="subjects[0][kelas]" required
                            class="block w-full p-2.5 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 kelas-select"
                            onchange="updateGuruOptions(this.closest('.subject-entry'))">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" data-has-wali="{{ $class->hasWaliKelas() ? 'true' : 'false' }}" data-wali-id="{{ $class->getWaliKelasId() }}">
                                {{ $class->nomor_kelas }} - {{ $class->nama_kelas }}
                                {{ $class->hasWaliKelas() ? '(Ada Wali Kelas)' : '(Belum Ada Wali Kelas)' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Semester -->
                    <div class="mb-4">
                        <label for="semester_0" class="block mb-2 text-sm font-medium text-gray-900">Semester</label>
                        <select id="semester_0" name="subjects[0][semester]" required
                            class="block w-full p-2.5 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Semester</option>
                            <option value="1" {{ old('semester') == 1 ? 'selected' : '' }}>Semester 1</option>
                            <option value="2" {{ old('semester') == 2 ? 'selected' : '' }}>Semester 2</option>
                        </select>
                    </div>

                    <!-- Guru Pengampu -->
                    <div class="mb-4">
                        <label for="guru_pengampu_0" class="block mb-2 text-sm font-medium text-gray-900">Guru Pengampu</label>
                        <select id="guru_pengampu_0" name="subjects[0][guru_pengampu]" required
                            class="block w-full p-2.5 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 guru-select">
                            <option value="">Pilih Guru</option>
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" data-jabatan="{{ $teacher->jabatan }}" {{ old('guru_pengampu') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->nama }} ({{ $teacher->jabatan == 'guru_wali' ? 'Wali Kelas' : 'Guru' }})
                            </option>
                            @endforeach
                        </select>
                        <!-- Tempat untuk pesan info -->
                        <div class="info-container mt-2"></div>
                    </div>

                    <!-- Lingkup Materi -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Lingkup Materi</label>
                        <div class="lingkup-materi-container">
                            <div class="flex items-center mb-2">
                                <input type="text" name="subjects[0][lingkup_materi][]" required
                                    class="block w-full p-2.5 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                                <button type="button" onclick="addLingkupMateri(this)" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let subjectCount = 1;
let waliKelasMap = {!! $waliKelasMap !!};

function addSubjectEntry() {
    subjectCount++;
    const container = document.getElementById('subjectEntriesContainer');
    const template = container.querySelector('.subject-entry').cloneNode(true);
    
    // Update IDs and names
    template.querySelectorAll('input, select').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace(/subjects\[0\]/, `subjects[${subjectCount-1}]`));
        }
        
        const id = input.getAttribute('id');
        if (id) {
            const newId = id.replace(/_0$/, `_${subjectCount-1}`);
            input.setAttribute('id', newId);
        }
        
        // Clear values
        if (input.tagName === 'INPUT' && input.type !== 'checkbox') {
            input.value = '';
        } else if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        } else if (input.type === 'checkbox') {
            input.checked = false;
        }

        // Re-attach event handlers for checkboxes
        if (input.classList.contains('muatan-lokal-checkbox') || input.classList.contains('allow-non-wali-checkbox')) {
            input.setAttribute('onchange', "updateGuruOptions(this.closest('.subject-entry'))");
        }
        
        // Re-attach event handlers for select boxes
        if (input.classList.contains('kelas-select')) {
            input.setAttribute('onchange', "updateGuruOptions(this.closest('.subject-entry'))");
        }
    });
    
    // Update labels
    template.querySelectorAll('label').forEach(label => {
        const forAttr = label.getAttribute('for');
        if (forAttr) {
            label.setAttribute('for', forAttr.replace(/_0$/, `_${subjectCount-1}`));
        }
    });
    
    // Update heading
    template.querySelector('h4').textContent = `Mata Pelajaran ${subjectCount}`;
    
    // Show the remove button for this entry
    template.querySelector('.remove-btn').classList.remove('hidden');
    
    // Reset lingkup materi container - keep only one entry
    const lingkupContainer = template.querySelector('.lingkup-materi-container');
    const firstLingkupEntry = lingkupContainer.querySelector('.flex.items-center').cloneNode(true);
    lingkupContainer.innerHTML = '';
    lingkupContainer.appendChild(firstLingkupEntry);
    firstLingkupEntry.querySelector('input').value = '';

    // Hide non-muatan lokal options initially
    template.querySelector('.non-muatan-lokal-options').style.display = 'none';
    
    // Clear any info messages
    template.querySelector('.info-container').innerHTML = '';
    
    // Add the new entry to the container
    container.appendChild(template);
    
    // If there's more than one entry, show all remove buttons
    if (document.querySelectorAll('.subject-entry').length > 1) {
        document.querySelectorAll('.subject-entry .remove-btn').forEach(btn => {
            btn.classList.remove('hidden');
        });
    }
}

function removeSubjectEntry(button) {
    const entry = button.closest('.subject-entry');
    
    // Only allow removal if there's more than one entry
    const allEntries = document.querySelectorAll('.subject-entry');
    if (allEntries.length > 1) {
        entry.remove();
        
        // Update subject numbers in headings
        document.querySelectorAll('.subject-entry h4').forEach((heading, index) => {
            heading.textContent = `Mata Pelajaran ${index + 1}`;
        });
        
        // If there's only one entry left, hide its remove button
        if (document.querySelectorAll('.subject-entry').length === 1) {
            document.querySelector('.subject-entry .remove-btn').classList.add('hidden');
        }
    }
}

function addLingkupMateri(button) {
    const container = button.closest('.lingkup-materi-container');
    const entryIndex = button.closest('.subject-entry').querySelector('input[type="text"]').name.match(/subjects\[(\d+)\]/)[1];
    
    const div = document.createElement('div');
    div.className = 'flex items-center mb-2';
    
    div.innerHTML = `
        <input type="text" name="subjects[${entryIndex}][lingkup_materi][]" required
            class="block w-full p-2.5 bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
        <button type="button" onclick="removeLingkupMateri(this)" class="ml-2 p-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    `;
    
    container.appendChild(div);
}

function removeLingkupMateri(button) {
    button.parentElement.remove();
}

function updateGuruOptions(subjectEntry) {
    // Ambil elemen dari entry yang aktif
    const isMuatanLokalElement = subjectEntry.querySelector('input[name*="[is_muatan_lokal]"]');
    const nonMuatanOptions = subjectEntry.querySelector('.non-muatan-lokal-options');
    const allowNonWaliElement = subjectEntry.querySelector('input[name*="[allow_non_wali]"]');
    const kelasSelect = subjectEntry.querySelector('select[name*="[kelas]"]');
    const guruSelect = subjectEntry.querySelector('select[name*="[guru_pengampu]"]');
    const infoContainer = subjectEntry.querySelector('.info-container');
    
    // Periksa apakah elemen ada sebelum mengakses propertinya
    const isMuatanLokal = isMuatanLokalElement ? isMuatanLokalElement.checked : false;
    const allowNonWali = allowNonWaliElement ? allowNonWaliElement.checked : false;
    
    // Pastikan infoContainer ada
    if (!infoContainer) return;
    
    // Clear previous info
    infoContainer.innerHTML = '';
    
    // Pastikan guruSelect ada
    if (!guruSelect) return;
    
    // Reset all options to enabled
    Array.from(guruSelect.options).forEach(option => {
        option.disabled = false;
    });

    if (isMuatanLokal !== subjectEntry.getAttribute('data-previous-muatan-lokal') === 'true') {
        guruSelect.selectedIndex = 0; // Reset selection to the default option
        subjectEntry.setAttribute('data-previous-muatan-lokal', isMuatanLokal);
    }
    
    // Toggle display of non-muatan lokal options jika elemen ada
    if (nonMuatanOptions) {
        nonMuatanOptions.style.display = isMuatanLokal ? 'none' : 'block';
    }
    
    // Pastikan kelasSelect ada
    if (!kelasSelect) return;
    
    // Get selected kelas info
    const selectedKelasId = parseInt(kelasSelect.value);
    if (!selectedKelasId) return; // No kelas selected
    
    const selectedOption = kelasSelect.options[kelasSelect.selectedIndex];
    if (!selectedOption) return;
    
    const hasWaliKelas = selectedOption.getAttribute('data-has-wali') === 'true';
    const waliKelasIdAttr = selectedOption.getAttribute('data-wali-id');
    const waliKelasId = waliKelasIdAttr ? parseInt(waliKelasIdAttr) : null;
    
    // Apply rules
    if (isMuatanLokal) {
        // For muatan lokal: only regular teachers, not wali kelas
        Array.from(guruSelect.options).forEach(option => {
            if (option.value && option.getAttribute('data-jabatan') === 'guru_wali') {
                option.disabled = true;
            }
        });
        
        showInfo(infoContainer, 'info', 'Mata pelajaran muatan lokal hanya dapat diajar oleh guru dengan jabatan guru biasa (bukan wali kelas).');
    } else {
        // For non-muatan lokal
        if (!hasWaliKelas) {
            // Class doesn't have wali kelas
            showInfo(infoContainer, 'warning', 'Kelas ini belum memiliki wali kelas. Harap tambahkan wali kelas terlebih dahulu, atau centang opsi "Pelajaran non-muatan lokal dengan guru bukan wali kelas".');
            
            if (allowNonWali) {
                // Allow only regular teachers, NOT wali kelas
                Array.from(guruSelect.options).forEach(option => {
                    if (option.value && option.getAttribute('data-jabatan') === 'guru_wali') {
                        option.disabled = true;
                    }
                });
                showInfo(infoContainer, 'info', 'Anda memilih mata pelajaran non-muatan lokal yang diajar oleh guru biasa.');
            } else {
                // No valid options
                Array.from(guruSelect.options).forEach(option => {
                    if (option.value) {
                        option.disabled = true;
                    }
                });
            }
        } else if (waliKelasId) { // Pastikan waliKelasId tidak null
            // Class has wali kelas
            if (allowNonWali) {
                // Allow only regular teachers, NOT wali kelas
                Array.from(guruSelect.options).forEach(option => {
                    if (option.value && option.getAttribute('data-jabatan') === 'guru_wali') {
                        option.disabled = true;
                    }
                });
                showInfo(infoContainer, 'info', 'Anda memilih mata pelajaran non-muatan lokal yang diajar oleh guru biasa, bukan wali kelas.');
            } else {
                // Allow only the wali kelas of this class
                Array.from(guruSelect.options).forEach(option => {
                    if (option.value && parseInt(option.value) !== waliKelasId) {
                        option.disabled = true;
                    }
                });
                
                // Auto-select wali kelas if not already selected
                if (guruSelect.value !== waliKelasId.toString()) {
                    guruSelect.value = waliKelasId.toString();
                }
                
                showInfo(infoContainer, 'info', 'Untuk mata pelajaran wajib (bukan muatan lokal), guru pengampu harus wali kelas dari kelas ini.');
            }
        }
    }
}

function showInfo(container, type, message) {
    let className, icon;
    
    switch(type) {
        case 'info':
            className = 'bg-blue-50 border border-blue-200 text-blue-800';
            icon = `<svg class="h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
            </svg>`;
            break;
        case 'warning':
            className = 'bg-yellow-50 border border-yellow-200 text-yellow-800';
            icon = `<svg class="h-5 w-5 text-yellow-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>`;
            break;
        case 'error':
            className = 'bg-red-50 border border-red-200 text-red-800';
            icon = `<svg class="h-5 w-5 text-red-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>`;
            break;
    }
    
    container.innerHTML = `
        <div class="p-2 ${className} rounded-md flex items-start">
            ${icon}
            <p class="text-sm">${message}</p>
        </div>
    `;
}

// Validasi Duplikasi
window.mapelData = [
    @foreach(App\Models\MataPelajaran::select('id', 'nama_pelajaran', 'kelas_id', 'semester')->get() as $mapel)
    {
        id: {{ $mapel->id }},
        nama: "{{ $mapel->nama_pelajaran }}",
        kelas_id: {{ $mapel->kelas_id }},
        semester: {{ $mapel->semester }}
    },
    @endforeach
];

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form protection
    if (window.Alpine) {
        document.addEventListener('alpine:init', () => {
            Alpine.data('formProtection', () => ({
                formChanged: false,
                isSubmitting: false,
                
                init() {
                    this.setupFormChangeListeners();
                    this.setupNavigationProtection();
                },
                
                setupFormChangeListeners() {
                    this.$el.querySelectorAll('input, select').forEach(element => {
                        element.addEventListener('change', () => {
                            this.formChanged = true;
                        });
                        
                        if (element.tagName === 'INPUT' && element.type !== 'checkbox') {
                            element.addEventListener('keyup', () => {
                                this.formChanged = true;
                            });
                        }
                    });
                },
                
                setupNavigationProtection() {
                    window.addEventListener('beforeunload', (e) => {
                        if (this.formChanged && !this.isSubmitting) {
                            e.preventDefault();
                            e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
                            return e.returnValue;
                        }
                    });
                },
                
                handleSubmit(e) {
                    // Validate before submission
                    if (!validateForm()) {
                        e.preventDefault();
                        return false;
                    }
                    
                    this.isSubmitting = true;
                    return true;
                }
            }));
        });
    }
    
    // Form validation
    const form = document.getElementById('addSubjectForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!validateForm()) {
                event.preventDefault();
                return false;
            }
            return true;
        });
    }
    
    // Initialize all entries
    document.querySelectorAll('.subject-entry').forEach(entry => {
        updateGuruOptions(entry);
    });
});

function validateForm() {
    // Clear all previous errors
    document.querySelectorAll('.mata-pelajaran-error').forEach(el => el.remove());
    document.querySelectorAll('input.border-red-500').forEach(el => el.classList.remove('border-red-500'));
    
    let formValid = true;
    
    // Validate each subject entry
    document.querySelectorAll('.subject-entry').forEach((entry, index) => {
        const mataPelajaranInput = entry.querySelector(`input[name="subjects[${index}][mata_pelajaran]"]`);
        const mataPelajaran = mataPelajaranInput.value.trim();
        const kelasSelect = entry.querySelector(`select[name="subjects[${index}][kelas]"]`);
        const kelasId = parseInt(kelasSelect.value);
        const semesterSelect = entry.querySelector(`select[name="subjects[${index}][semester]"]`);
        const semester = parseInt(semesterSelect.value);
        
        // Skip validation for incomplete entries
        if (!mataPelajaran || !kelasId || isNaN(semester)) {
            return;
        }
        
        // Check for duplicate subjects
        const duplicate = window.mapelData.find(subject => 
            subject.nama.toLowerCase() === mataPelajaran.toLowerCase() && 
            subject.kelas_id === kelasId && 
            subject.semester === semester
        );
        
        if (duplicate) {
            // Show error
            mataPelajaranInput.classList.add('border-red-500');
            
            const errorElement = document.createElement('p');
            errorElement.className = 'mata-pelajaran-error mt-1 text-sm text-red-500';
            errorElement.textContent = `"${mataPelajaran}" sudah ada di kelas ini untuk semester ${semester}`;
            mataPelajaranInput.parentNode.appendChild(errorElement);
            
            formValid = false;
        }
    });
    
    if (!formValid) {
        alert('Terdapat duplikasi mata pelajaran. Silakan periksa kembali form.');
    }
    
    return formValid;
}
</script>

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert("{{ session('error') }}");
    });
</script>
@endif
@endpush
@endsection