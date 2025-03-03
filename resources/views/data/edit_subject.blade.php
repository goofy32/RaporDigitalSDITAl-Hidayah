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
        <form id="editSubjectForm" action="{{ route('subject.update', $subject->id) }}" @submit="handleSubmit" x-data="formProtection" method="POST" class="space-y-6">
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
                        <option value="{{ $teacher->id }}" {{ old('guru_pengampu', $subject->guru_id) == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->nama }}
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
        </button>d-lg hover:bg-red-700">
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
    
    // Simpan data wali kelas untuk setiap kelas
    const kelasWali = {
        @foreach($classes as $class)
            @if($class->hasWaliKelas() && $class->getWaliKelas())
                {{ $class->id }}: {{ $class->getWaliKelasId() }},
            @endif
        @endforeach
    };
    
    // Database guru wali kelas (ID guru yang sudah menjadi wali kelas)
    const guruWaliList = [
        @foreach($classes as $class)
            @if($class->hasWaliKelas() && $class->getWaliKelas())
                {{ $class->getWaliKelasId() }},
            @endif
        @endforeach
    ];
    
    // Simpan nilai awal
    const initialKelasId = kelasSelect.value;
    const initialGuruId = guruSelect.value;
    
    // Fungsi untuk mengatur guru pengampu berdasarkan kelas yang dipilih
    function updateGuruOptions() {
        if (!kelasSelect || !guruSelect) return;
        
        const selectedKelasId = kelasSelect.value;
        const waliKelasId = kelasWali[selectedKelasId];
        
        // Reset status disabled
        Array.from(guruSelect.options).forEach(option => {
            option.disabled = false;
        });
        
        // Jika tidak ada kelas yang dipilih, jangan lakukan apa-apa
        if (!selectedKelasId) {
            showWaliKelasInfo(false);
            return;
        }
        
        // Jika kelas yang dipilih memiliki wali kelas
        if (waliKelasId) {
            // Pilih otomatis wali kelas
            guruSelect.value = waliKelasId;
            
            // Nonaktifkan semua opsi guru kecuali wali kelas
            Array.from(guruSelect.options).forEach(option => {
                if (option.value && option.value != waliKelasId) {
                    option.disabled = true;
                }
            });
            
            // Tampilkan pesan informasi
            showWaliKelasInfo(true, selectedKelasId);
        } else {
            // Kelas tanpa wali kelas
            // Nonaktifkan semua guru yang sudah menjadi wali kelas
            Array.from(guruSelect.options).forEach(option => {
                if (option.value && guruWaliList.includes(parseInt(option.value))) {
                    option.disabled = true;
                }
            });
            
            // Sembunyikan pesan informasi wali kelas
            showWaliKelasInfo(false);
        }
    }
    
    // Tambahkan event listener untuk perubahan dropdown kelas
    if (kelasSelect) {
        kelasSelect.addEventListener('change', updateGuruOptions);
        
        // Juga jalankan saat halaman pertama kali dimuat
        updateGuruOptions();
    }
    
    function showWaliKelasInfo(show, kelasId) {
        let infoElement = document.getElementById('wali-kelas-info');
        let waliRuleInfo = document.getElementById('wali-rule-info');
        
        // Info untuk kelas yang memiliki wali kelas
        if (show) {
            // Dapatkan informasi kelas untuk pesan
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
                infoElement.innerHTML = `<p class="text-sm text-blue-800"><span class="font-medium">Info:</span> Untuk ${kelasInfo}, guru pengampu secara otomatis dipilih sebagai wali kelas dan tidak dapat diubah.</p>`;
                
                // Tambahkan info setelah dropdown guru
                guruSelect.parentNode.appendChild(infoElement);
            } else {
                infoElement.innerHTML = `<p class="text-sm text-blue-800"><span class="font-medium">Info:</span> Untuk ${kelasInfo}, guru pengampu secara otomatis dipilih sebagai wali kelas dan tidak dapat diubah.</p>`;
                infoElement.style.display = 'block';
            }
        } else if (infoElement) {
            infoElement.style.display = 'none';
        }
        
        // Selalu tampilkan informasi tentang aturan wali kelas
        if (!waliRuleInfo) {
            waliRuleInfo = document.createElement('div');
            waliRuleInfo.id = 'wali-rule-info';
            waliRuleInfo.className = 'mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded-md';
            waliRuleInfo.innerHTML = '<p class="text-sm text-yellow-800"><span class="font-medium">Penting:</span> Guru yang sudah menjadi wali kelas hanya dapat mengajar di kelas yang diwalikannya. Guru yang menjadi wali kelas di kelas lain tidak dapat dipilih.</p>';
            
            // Tambahkan info setelah dropdown guru (atau setelah info wali kelas jika ada)
            if (infoElement && infoElement.style.display !== 'none') {
                infoElement.after(waliRuleInfo);
            } else {
                guruSelect.parentNode.appendChild(waliRuleInfo);
            }
        }
    }
});
</script>

<!-- Validasi Duplikasi -->
<script>
// Definisikan array data mata pelajaran terlebih dahulu
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
    console.log('DOM fully loaded');
    
    // Dapatkan elemen-elemen yang dibutuhkan
    const mataPelajaranInput = document.getElementById('mata_pelajaran');
    const kelasSelect = document.getElementById('kelas');
    const semesterSelect = document.getElementById('semester');
    const submitButton = document.querySelector('button[type="submit"]');
    const currentId = {{ $subject->id }}; // ID mata pelajaran yang sedang diedit
    
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
    
    // Form submit handler dengan capture phase
    document.getElementById('editSubjectForm').addEventListener('submit', function(event) {
        if (!checkDuplication()) {
            event.preventDefault();
            event.stopPropagation();
            
            // Tampilkan pesan error
            alert('Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.');
            
            // Validasi visual
            validateMataPelajaran();
            
            return false;
        }
        
        return true;
    }, true); // true untuk capture phase
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