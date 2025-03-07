@extends('layouts.app')

@section('title', 'Tambah Data Mata Pelajaran')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Mata Pelajaran</h2>
                <div>
                    <button onclick="window.history.back()" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Kembali
                    </button>
                    <button type="submit" form="addSubjectForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Simpan
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

            <!-- Form -->
            <form id="addSubjectForm" action="{{ route('subject.store') }}" method="POST" @submit="handleSubmit" x-data="formProtection" class="space-y-6">
                @csrf

                <!-- Mata Pelajaran -->
                <div>
                    <label for="mata_pelajaran" class="block mb-2 text-sm font-medium text-gray-900">Mata Pelajaran</label>
                    <input type="text" id="mata_pelajaran" name="mata_pelajaran" value="{{ old('mata_pelajaran') }}" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('mata_pelajaran') border-red-500 @enderror">
                    @error('mata_pelajaran')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <div class="flex items-center">
                        <input id="is_muatan_lokal" name="is_muatan_lokal" type="checkbox" 
                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                            {{ old('is_muatan_lokal') ? 'checked' : '' }}>
                        <label for="is_muatan_lokal" class="ml-2 block text-sm text-gray-900">
                            Tandai sebagai Muatan Lokal
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Muatan lokal hanya dapat diajar oleh guru dengan jabatan guru (bukan wali kelas)</p>
                </div>

                <!-- Kelas Dropdown -->
                <div>
                    <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
                    <select id="kelas" name="kelas" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('kelas') border-red-500 @enderror">
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('kelas') == $class->id ? 'selected' : '' }}>
                            {{ $class->nomor_kelas }} - {{ $class->nama_kelas }}
                        </option>
                        @endforeach
                    </select>
                    @error('kelas')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Siswa Dropdown -->
                <div>
                    <label for="semester" class="block mb-2 text-sm font-medium text-gray-900">Semester</label>
                    <select id="semester" name="semester" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('semester') border-red-500 @enderror">
                        <option value="">Pilih Semester</option>
                        <option value="1" {{ old('semester') == 1 ? 'selected' : '' }}>Semester 1</option>
                        <option value="2" {{ old('semester') == 2 ? 'selected' : '' }}>Semester 2</option>
                    </select>
                    @error('semester')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Guru Pengampu Dropdown -->
                <div>
                    <label for="guru_pengampu" class="block mb-2 text-sm font-medium text-gray-900">Guru Pengampu</label>
                    <select id="guru_pengampu" name="guru_pengampu" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('guru_pengampu') border-red-500 @enderror">
                        <option value="">Pilih Guru</option>
                        <!-- Loop through teachers -->
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" data-jabatan="{{ $teacher->jabatan }}" {{ old('guru_pengampu') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->nama }} ({{ $teacher->jabatan == 'guru_wali' ? 'Wali Kelas' : 'Guru' }})
                        </option>
                        @endforeach
                    </select>
                    @error('guru_pengampu')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lingkup Materi -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900">Lingkup Materi</label>
                    <div id="lingkupMateriContainer">
                        <div class="flex items-center mb-2">
                            <input type="text" name="lingkup_materi[]" required
                                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <button type="button" onclick="addLingkupMateri()" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @error('lingkup_materi')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </div>
    </div>


@push('scripts')
<script>
// JavaScript for dynamic Lingkup Materi
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
}

function removeLingkupMateri(button) {
    button.parentElement.remove();
}

document.addEventListener('DOMContentLoaded', function() {
    // Get the necessary elements
    const kelasSelect = document.getElementById('kelas');
    const guruSelect = document.getElementById('guru_pengampu');
    const muatanLokalCheckbox = document.getElementById('is_muatan_lokal');
    
    // Make sure we have the required elements before proceeding
    if (!kelasSelect || !guruSelect || !muatanLokalCheckbox) {
        console.error('Required form elements not found');
        return;
    }
    
    // Store all teachers' roles
    const guruRoles = {};
    Array.from(guruSelect.options).forEach(option => {
        if (option.value) {
            // Check if data-jabatan attribute exists, otherwise use text content to determine role
            const isWaliKelas = option.hasAttribute('data-jabatan') 
                ? option.getAttribute('data-jabatan') === 'guru_wali'
                : option.textContent.includes('Wali Kelas');
            
            guruRoles[option.value] = isWaliKelas ? 'wali_kelas' : 'guru';
        }
    });
    
    // List of wali kelas IDs (homeroom teachers)
    const guruWaliList = Object.entries(guruRoles)
        .filter(([_, role]) => role === 'wali_kelas')
        .map(([id, _]) => parseInt(id));
    
    // List of regular guru IDs
    const guruBiasaList = Object.entries(guruRoles)
        .filter(([_, role]) => role === 'guru')
        .map(([id, _]) => parseInt(id));
    
    console.log('Teacher roles:', guruRoles);
    console.log('Homeroom teacher IDs:', guruWaliList);
    console.log('Regular teacher IDs:', guruBiasaList);
    
    // Data of wali kelas for each class
    const kelasWali = {};
    
    // Populate the kelasWali object with class-to-homeroom-teacher mapping
    // This should be replaced with actual server-side data in the Blade template
    @foreach($classes as $class)
        @if($class->hasWaliKelas() && $class->getWaliKelasId())
            kelasWali[{{ $class->id }}] = {{ $class->getWaliKelasId() }};
        @endif
    @endforeach
    
    console.log('Class to Homeroom Teacher mapping:', kelasWali);
    
    // Main function to update form state based on the current selections
    function updateFormState() {
        const isMuatanLokal = muatanLokalCheckbox.checked;
        const selectedKelasId = kelasSelect.value;
        const waliKelasId = kelasWali[selectedKelasId]; // Wali kelas untuk kelas yang dipilih
        
        console.log('Update form state:', {
            isMuatanLokal,
            selectedKelasId,
            waliKelasId
        });
        
        // Reset semua opsi
        Array.from(guruSelect.options).forEach(option => {
            option.disabled = false;
        });
        
        // Hide all info elements first
        hideAllInfoElements();
        
        // If no class selected, do nothing further
        if (!selectedKelasId) {
            return;
        }
        
        if (isMuatanLokal) {
            // Untuk mata pelajaran muatan lokal:
            // Hanya guru biasa (bukan wali kelas) yang bisa mengajar
            Array.from(guruSelect.options).forEach(option => {
                if (option.value) {
                    const guruId = parseInt(option.value);
                    if (guruWaliList.includes(guruId)) {
                        option.disabled = true;
                    }
                }
            });
            
            // Tampilkan info muatan lokal
            showMuatanLokalInfo(true);
            
            // Reset pilihan jika wali kelas terpilih
            if (waliKelasId && guruSelect.value == waliKelasId) {
                guruSelect.value = '';
            }
        } else {
            // Untuk mata pelajaran wajib (bukan muatan lokal):
            if (waliKelasId) {
                // Jika kelas memiliki wali kelas, hanya wali kelas yang bisa mengajar
                Array.from(guruSelect.options).forEach(option => {
                    if (option.value) {
                        const guruId = parseInt(option.value);
                        // Disable all options except the homeroom teacher
                        if (guruId != waliKelasId) {
                            option.disabled = true;
                        }
                    }
                });
                
                // Auto-select wali kelas
                guruSelect.value = waliKelasId;
                
                // Tampilkan info wali kelas
                showWaliKelasInfo(true, selectedKelasId);
            } else {
                // Jika kelas tidak memiliki wali kelas:
                // Disable all options since we need a homeroom teacher
                Array.from(guruSelect.options).forEach(option => {
                    if (option.value) {
                        option.disabled = true;
                    }
                });
                
                // Reset pilihan guru
                guruSelect.value = '';
                
                // Tampilkan peringatan tidak ada wali kelas
                showNoWaliKelasInfo(true);
            }
        }
    }
    
    // Function to show info when a class has a wali kelas
    function showWaliKelasInfo(show, kelasId) {
        let infoElement = document.getElementById('wali-kelas-info');
        
        if (show) {
            // Get class info for the message
            let kelasInfo = '';
            if (kelasId) {
                const kelasOption = kelasSelect.querySelector(`option[value="${kelasId}"]`);
                if (kelasOption) {
                    kelasInfo = kelasOption.textContent;
                }
            }
            
            if (!infoElement) {
                infoElement = document.createElement('div');
                infoElement.id = 'wali-kelas-info';
                infoElement.className = 'mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md';
                infoElement.innerHTML = `<p class="text-sm text-blue-800"><span class="font-medium">Info:</span> Kelas ${kelasInfo} memiliki wali kelas. Untuk mata pelajaran wajib (bukan muatan lokal), guru pengampu harus wali kelas dari kelas ini.</p>`;
                
                guruSelect.parentNode.appendChild(infoElement);
            } else {
                infoElement.innerHTML = `<p class="text-sm text-blue-800"><span class="font-medium">Info:</span> Kelas ${kelasInfo} memiliki wali kelas. Untuk mata pelajaran wajib (bukan muatan lokal), guru pengampu harus wali kelas dari kelas ini.</p>`;
                infoElement.style.display = 'block';
            }
        } else if (infoElement) {
            infoElement.style.display = 'none';
        }
    }
    
    // Function to show info when a class doesn't have a wali kelas
    function showNoWaliKelasInfo(show) {
        let infoElement = document.getElementById('no-wali-kelas-info');
        
        if (show) {
            if (!infoElement) {
                infoElement = document.createElement('div');
                infoElement.id = 'no-wali-kelas-info';
                infoElement.className = 'mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-md';
                infoElement.innerHTML = '<p class="text-sm text-yellow-800"><span class="font-medium">Perhatian:</span> Kelas ini tidak memiliki wali kelas. Untuk mata pelajaran wajib (bukan muatan lokal), Anda harus menambahkan wali kelas terlebih dahulu.</p>';
                
                guruSelect.parentNode.appendChild(infoElement);
            } else {
                infoElement.style.display = 'block';
            }
        } else if (infoElement) {
            infoElement.style.display = 'none';
        }
    }
    
    // Function to show muatan lokal info
    function showMuatanLokalInfo(show) {
        let infoElement = document.getElementById('muatan-lokal-info');
        
        if (show) {
            if (!infoElement) {
                infoElement = document.createElement('div');
                infoElement.id = 'muatan-lokal-info';
                infoElement.className = 'mt-2 p-2 bg-green-50 border border-green-200 rounded-md';
                infoElement.innerHTML = '<p class="text-sm text-green-800"><span class="font-medium">Info:</span> Mata pelajaran muatan lokal hanya dapat diajar oleh guru dengan jabatan guru biasa (bukan wali kelas).</p>';
                
                guruSelect.parentNode.appendChild(infoElement);
            } else {
                infoElement.style.display = 'block';
            }
        } else if (infoElement) {
            infoElement.style.display = 'none';
        }
    }
    
    // Function to hide all info elements
    function hideAllInfoElements() {
        const infoElements = [
            document.getElementById('wali-kelas-info'),
            document.getElementById('no-wali-kelas-info'),
            document.getElementById('muatan-lokal-info')
        ];
        
        infoElements.forEach(element => {
            if (element) {
                element.style.display = 'none';
            }
        });
    }
    
    // Add event listeners
    if (kelasSelect) {
        kelasSelect.addEventListener('change', function() {
            updateFormState();
            console.log('Class changed to:', this.value);
            console.log('Wali Kelas ID for this class:', kelasWali[this.value]);
        });
    }
    
    if (muatanLokalCheckbox) {
        muatanLokalCheckbox.addEventListener('change', function() {
            updateFormState();
            console.log('Muatan Lokal changed to:', this.checked);
        });
    }
    
    // Run initial setup
    updateFormState();
});

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

// Tunggu dokumen sepenuhnya dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Dapatkan elemen-elemen yang dibutuhkan
    const mataPelajaranInput = document.getElementById('mata_pelajaran');
    const kelasSelect = document.getElementById('kelas');
    const semesterSelect = document.getElementById('semester');
    const submitButton = document.querySelector('button[type="submit"]');
    
    // Get current subject ID if editing (will be undefined for add page)
    const currentId = document.getElementById('editSubjectForm') ? 
        parseInt(document.getElementById('editSubjectForm').getAttribute('data-subject-id')) : 
        undefined;
    
    // Jika ada elemen yang tidak ditemukan, hentikan eksekusi
    if (!mataPelajaranInput || !kelasSelect || !semesterSelect || !submitButton) {
        console.error('Required elements not found');
        return;
    }
    
    // Fungsi untuk memeriksa duplikasi
    function checkDuplication() {
        const mataPelajaran = mataPelajaranInput.value.trim();
        const kelasId = parseInt(kelasSelect.value);
        const semester = parseInt(semesterSelect.value);
        
        // Jika salah satu field kosong, lewati validasi
        if (!mataPelajaran || !kelasId || isNaN(semester)) return true;
        
        // Periksa duplikasi, kecuali untuk mata pelajaran yang sedang diedit
        // dan memastikan semester sama (bukan berbeda)
        const duplicate = window.mapelData.find(subject => 
            subject.nama.toLowerCase() === mataPelajaran.toLowerCase() && 
            subject.kelas_id === kelasId && 
            subject.semester === semester && 
            subject.id !== currentId
        );
        
        return !duplicate;
    }
    // Real-time validation
    function validateMataPelajaran() {
        if (!checkDuplication()) {
            mataPelajaranInput.classList.add('border-red-500');
            
            // Buat pesan error di bawah input jika belum ada
            let errorElement = document.getElementById('mata-pelajaran-error');
            if (!errorElement) {
                errorElement = document.createElement('p');
                errorElement.id = 'mata-pelajaran-error';
                errorElement.className = 'mt-1 text-sm text-red-500';
                errorElement.textContent = 'Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.';
                mataPelajaranInput.parentNode.appendChild(errorElement);
            }
            
            return false;
        } else {
            // Hapus class error dan pesan error jika validasi berhasil
            mataPelajaranInput.classList.remove('border-red-500');
            const errorElement = document.getElementById('mata-pelajaran-error');
            if (errorElement) {
                errorElement.remove();
            }
            
            return true;
        }
    }
    
    // Event listener untuk input dan perubahan
    mataPelajaranInput.addEventListener('input', validateMataPelajaran);
    kelasSelect.addEventListener('change', validateMataPelajaran);
    semesterSelect.addEventListener('change', validateMataPelajaran);

    
    // Form submit handler
    const form = document.getElementById('addSubjectForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            // Debug: Log form data
            console.log('Form submission attempt:');
            console.log('Mata Pelajaran:', mataPelajaranInput.value);
            console.log('Kelas:', kelasSelect.value);
            console.log('Semester:', semesterSelect.value);
            console.log('Guru:', document.getElementById('guru_pengampu').value);
            
            // Continue with validation...
            if (!checkDuplication()) {
                event.preventDefault();
                event.stopPropagation();
                
                alert('Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.');
                validateMataPelajaran();
                return false;
            }
            
            // Form is valid
            console.log('Form validation passed, submitting...');
            return true;
        });
    }
});
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