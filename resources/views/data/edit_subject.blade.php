@extends('layouts.app')

@section('title', 'Edit Data Mata Pelajaran')

@section('content')
<div>
    <div class="p-4 bg-white mt-14">
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

        @if ($errors->any())
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium">Terdapat beberapa kesalahan:</h3>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Form -->
        <form id="editSubjectForm" action="{{ route('subject.update', $subject->id) }}" method="POST" data-subject-id="{{ $subject->id }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Mata Pelajaran -->
            <div>
                <label for="mata_pelajaran" class="block mb-2 text-sm font-medium text-gray-900">Mata Pelajaran</label>
                <input type="text" id="mata_pelajaran" name="mata_pelajaran" value="{{ old('mata_pelajaran', $subject->nama_pelajaran) }}" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('mata_pelajaran') border-red-500 @enderror">
                @error('mata_pelajaran')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <div class="flex items-center">
                    <input id="is_muatan_lokal" name="is_muatan_lokal" type="checkbox" 
                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                        {{ old('is_muatan_lokal', isset($subject) && $subject->is_muatan_lokal ? 'checked' : '') }}>
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
                        <option value="{{ $class->id }}" {{ old('kelas', $subject->kelas_id) == $class->id ? 'selected' : '' }}>
                            {{ $class->nomor_kelas }}-{{ $class->nama_kelas }}
                            @if($class->hasWaliKelas() && $class->getWaliKelas())
                                (Wali Kelas: {{ $class->getWaliKelas()->nama }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('kelas')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Semester Dropdown -->
            <div>
                <label for="semester" class="block mb-2 text-sm font-medium text-gray-900">Semester</label>
                <select id="semester" name="semester" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('semester') border-red-500 @enderror">
                    <option value="">Pilih Semester</option>
                    <option value="1" {{ old('semester', $subject->semester) == 1 ? 'selected' : '' }}>Semester 1</option>
                    <option value="2" {{ old('semester', $subject->semester) == 2 ? 'selected' : '' }}>Semester 2</option>
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
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" data-jabatan="{{ $teacher->jabatan }}" {{ old('guru_pengampu', $subject->guru_id) == $teacher->id ? 'selected' : '' }}>
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
                    @foreach($subject->lingkupMateris as $index => $lingkupMateri)
                        <div class="flex items-center mb-2">
                            <input type="text" name="lingkup_materi[]" value="{{ old('lingkup_materi.'.$index, $lingkupMateri->judul_lingkup_materi) }}" required
                                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            @if($index === 0)
                                <button type="button" onclick="addLingkupMateri()" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            @else
                                <button type="button" onclick="removeLingkupMateri(this)" class="ml-2 p-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('lingkup_materi')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </form>
    </div>
</div>
@push('scripts')
<!-- JavaScript for dynamic Lingkup Materi -->
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
    const kelasSelect = document.getElementById('kelas');
    const guruSelect = document.getElementById('guru_pengampu');
    const muatanLokalCheckbox = document.getElementById('is_muatan_lokal');
    
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
    
    // List of wali kelas IDs
    const guruWaliList = Object.entries(guruRoles)
        .filter(([_, role]) => role === 'wali_kelas')
        .map(([id, _]) => parseInt(id));
    
    // List of regular guru IDs
    const guruBiasaList = Object.entries(guruRoles)
        .filter(([_, role]) => role === 'guru')
        .map(([id, _]) => parseInt(id));
    
    // Data of wali kelas for each class
    const kelasWali = {
        @foreach($classes as $class)
            @if($class->hasWaliKelas() && $class->getWaliKelas())
                {{ $class->id }}: {{ $class->getWaliKelasId() }},
            @endif
        @endforeach
    };
    
    // Main function to update form state based on the current selections
    function updateFormState() {
        const isMuatanLokal = muatanLokalCheckbox.checked;
        const selectedKelasId = kelasSelect.value;
        const waliKelasId = kelasWali[selectedKelasId]; // Wali kelas untuk kelas yang dipilih
        
        // Reset semua opsi
        Array.from(guruSelect.options).forEach(option => {
            option.disabled = false;
        });
        
        // Jika kelas dipilih
        if (selectedKelasId) {
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
                showWaliKelasInfo(false);
                showNoWaliKelasInfo(false);
                
                // Reset pilihan jika wali kelas terpilih
                if (waliKelasId && guruSelect.value == waliKelasId) {
                    guruSelect.value = '';
                }
            } else {
                // Untuk mata pelajaran wajib (bukan muatan lokal):
                if (waliKelasId) {
                    // Jika kelas memiliki wali kelas, hanya wali kelas yang bisa mengajar
                    Array.from(guruSelect.options).forEach(option => {
                        if (option.value && parseInt(option.value) != waliKelasId) {
                            option.disabled = true;
                        }
                    });
                    
                    // Auto-select wali kelas
                    guruSelect.value = waliKelasId;
                    
                    // Tampilkan info
                    showWaliKelasInfo(true, selectedKelasId);
                    showMuatanLokalInfo(false);
                    showNoWaliKelasInfo(false);
                } else {
                    // Jika kelas tidak memiliki wali kelas, tampilkan peringatan
                    showWaliKelasInfo(false);
                    showMuatanLokalInfo(false);
                    showNoWaliKelasInfo(true);
                }
            }
        } else {
            // Jika tidak ada kelas yang dipilih, sembunyikan semua info
            hideAllInfoElements();
        }
    }
    
    // Function to update the selected teacher based on form state
    function updateSelectedTeacher() {
        const selectedGuruId = parseInt(guruSelect.value);
        if (!selectedGuruId) return;
        
        const isMuatanLokal = muatanLokalCheckbox.checked;
        const isGuruWali = guruWaliList.includes(selectedGuruId);
        
        // If the selected teacher doesn't match the muatan lokal state
        if ((isMuatanLokal && isGuruWali) || (!isMuatanLokal && !isGuruWali)) {
            // Reset the selection
            guruSelect.value = '';
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
        kelasSelect.addEventListener('change', updateFormState);
    }
    
    if (muatanLokalCheckbox) {
        muatanLokalCheckbox.addEventListener('change', updateFormState);
    }
    
    if (guruSelect) {
        guruSelect.addEventListener('change', function() {
            // If user manually selects a teacher, update muatan lokal checkbox accordingly
            const selectedGuruId = parseInt(guruSelect.value);
            if (selectedGuruId) {
                const isWaliKelas = guruWaliList.includes(selectedGuruId);
                muatanLokalCheckbox.checked = !isWaliKelas;
            }
            
            // Update the form state
            updateFormState();
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
    const mataPelajaranInput = document.getElementById('mata_pelajaran');
    const kelasSelect = document.getElementById('kelas');
    const semesterSelect = document.getElementById('semester');
    const submitButton = document.querySelector('button[type="submit"]');
    
    // Dapatkan ID subjek yang sedang diedit
    // Pastikan nilai ini benar ada
    const currentId = {{ $subject->id ?? 'null' }};
    
    // Fungsi untuk memeriksa duplikasi
    function checkDuplication() {
        const mataPelajaran = mataPelajaranInput.value.trim();
        const kelasId = parseInt(kelasSelect.value);
        const semester = parseInt(semesterSelect.value);
        
        // Jika salah satu field kosong, lewati validasi
        if (!mataPelajaran || !kelasId || isNaN(semester)) return true;
        
        console.log("Checking duplication for:", {
            name: mataPelajaran,
            kelasId: kelasId,
            semester: semester,
            currentId: currentId
        });
        
        // Periksa duplikasi, kecuali untuk mata pelajaran yang sedang diedit
        const duplicate = window.mapelData.find(subject => 
            subject.nama.toLowerCase() === mataPelajaran.toLowerCase() && 
            subject.kelas_id === kelasId && 
            subject.semester === semester && 
            subject.id !== currentId
        );
        
        console.log("Found duplicate:", duplicate);
        
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
    const form = document.getElementById('editSubjectForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            console.log("Form submission attempted");
            console.log("Current Subject ID:", window.currentSubjectId);
            console.log("Form values:", {
                mataPelajaran: mataPelajaranInput.value,
                kelas: kelasSelect.value,
                semester: semesterSelect.value
            });
            
            if (!checkDuplication()) {
                event.preventDefault();
                console.log("Duplication found, preventing submission");
                alert('Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.');
                validateMataPelajaran();
                return false;
            }
            
            console.log("Validation passed, submitting form");
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