@extends('layouts.pengajar.app')

@section('title', 'Tambah Data Mata Pelajaran')

@section('content')
<div>
    <div class="p-6 bg-white mt-14">
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

        <!-- Status messages -->
        <div id="statusMessage" class="mb-4 hidden">
            <div id="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative hidden" role="alert">
                <span class="block sm:inline" id="successText"></span>
            </div>
            <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative hidden" role="alert">
                <span class="block sm:inline" id="errorText"></span>
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
        <form id="addSubjectForm" action="{{ route('pengajar.subject.store') }}" method="POST" @submit="handleSubmit" x-data="formProtection" class="space-y-6">
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

            <!-- Hanya tampilkan informasi muatan lokal untuk guru biasa -->
            @if(auth()->guard('guru')->user()->jabatan == 'guru')
            <div class="mt-4">
                <div class="flex items-center">
                    <input id="is_muatan_lokal_display" type="checkbox" 
                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                        checked disabled>
                    <label for="is_muatan_lokal_display" class="ml-2 block text-sm text-gray-900">
                        Tandai sebagai Muatan Lokal
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">Sebagai guru biasa, mata pelajaran Anda ditetapkan sebagai muatan lokal secara otomatis.</p>
                
                <!-- Hidden input with a different name to avoid manipulation -->
                <input type="hidden" name="__secure_is_muatan_lokal" value="1">
            </div>
            @endif

            <!-- Kelas Dropdown -->
            <div>
                <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
                <select id="kelas" name="kelas" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('kelas') border-red-500 @enderror">
                    <option value="">Pilih Kelas</option>
                    @if($classes->isEmpty())
                        <option value="" disabled>Tidak ada kelas yang ditugaskan</option>
                    @else
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('kelas') == $class->id || (auth()->guard('guru')->user()->isWaliKelas() && auth()->guard('guru')->user()->getWaliKelasId() == $class->id) ? 'selected' : '' }}>
                                Kelas {{ $class->nomor_kelas }} {{ $class->nama_kelas }}
                                {{ auth()->guard('guru')->user()->isWaliKelas() && 
                                   auth()->guard('guru')->user()->getWaliKelasId() == $class->id ? 
                                   '(Wali Kelas)' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('kelas')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                
                @if($classes->isEmpty())
                    <p class="mt-2 text-sm text-red-600">Anda belum ditugaskan ke kelas manapun. Silakan hubungi admin.</p>
                @endif
                
                @if(auth()->guard('guru')->user()->isWaliKelas())
                    <div id="wali-kelas-info" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                        <p class="text-sm text-blue-800">
                            <span class="font-medium">Info:</span> 
                            Sebagai wali kelas, Anda secara otomatis dapat mengajar di kelas yang Anda walikan.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Semester Dropdown -->
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

            <!-- Hidden input untuk guru_id -->
            <input type="hidden" name="guru_pengampu" value="{{ auth()->guard('guru')->id() }}">

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup form protection
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
                    this.$el.querySelectorAll('input, select, textarea').forEach(element => {
                        element.addEventListener('change', () => {
                            this.formChanged = true;
                        });
                        
                        if (element.tagName === 'INPUT' && element.type !== 'checkbox' && element.type !== 'radio') {
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
                    if (!checkDuplication()) {
                        e.preventDefault();
                        alert('Mata pelajaran dengan nama yang sama sudah ada di kelas ini untuk semester yang sama.');
                        validateMataPelajaran();
                        return false;
                    }
                    
                    this.isSubmitting = true;
                    return true;
                }
            }));
        });
    }

    const kelasSelect = document.getElementById('kelas');
    
    // Simpan data wali kelas untuk setiap kelas
    const kelasWali = {
        @foreach($classes as $class)
            @if($class->hasWaliKelas() && $class->getWaliKelas())
                {{ $class->id }}: {{ $class->getWaliKelasId() }},
            @endif
        @endforeach
    };
    
    // Fungsi untuk mengunci dropdown guru pengampu
    function lockGuruDropdown(selectedKelasId) {
        // Jika tidak ada kelas yang dipilih atau tidak ada wali kelas
        if (!selectedKelasId || !kelasWali[selectedKelasId]) {
            // Sembunyikan pesan informasi
            showWaliKelasInfo(false);
            return;
        }
        
        // Dapatkan ID wali kelas
        const waliKelasId = kelasWali[selectedKelasId];
        
        // Tampilkan pesan informasi
        showWaliKelasInfo(true, selectedKelasId);
    }
    
    // Tambahkan event listener untuk perubahan dropdown kelas
    if (kelasSelect) {
        kelasSelect.addEventListener('change', function() {
            const selectedKelasId = this.value;
            lockGuruDropdown(selectedKelasId);
            validateMataPelajaran(); // Cek validasi duplikat saat kelas berubah
        });
        
        // Periksa juga saat halaman pertama kali dimuat
        lockGuruDropdown(kelasSelect.value);
    }
    
    function showWaliKelasInfo(show, kelasId) {
        let infoElement = document.getElementById('wali-kelas-info');
        
        if (!infoElement) return;
        
        if (show) {
            // Dapatkan informasi kelas untuk pesan
            let kelasInfo = '';
            if (kelasId) {
                const kelasOption = kelasSelect.querySelector(`option[value="${kelasId}"]`);
                if (kelasOption) {
                    kelasInfo = kelasOption.textContent;
                }
            }
            
            infoElement.style.display = 'block';
        } else if (infoElement) {
            infoElement.style.display = 'none';
        }
    }

    // Definisikan array data mata pelajaran yang sudah ada
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

    // Dapatkan elemen-elemen yang dibutuhkan
    const mataPelajaranInput = document.getElementById('mata_pelajaran');
    const semesterSelect = document.getElementById('semester');
    const submitButton = document.querySelector('button[type="submit"]');
    
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
        
        // Periksa duplikasi
        const duplicate = window.mapelData.find(subject => 
            subject.nama.toLowerCase() === mataPelajaran.toLowerCase() && 
            subject.kelas_id === kelasId && 
            subject.semester === semester
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
    document.getElementById('addSubjectForm').addEventListener('submit', function(event) {
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

    // Fungsi untuk menampilkan pesan
    function showMessage(message, type) {
        const statusMessageElement = document.getElementById('statusMessage');
        const successMessageElement = document.getElementById('successMessage');
        const errorMessageElement = document.getElementById('errorMessage');
        const successTextElement = document.getElementById('successText');
        const errorTextElement = document.getElementById('errorText');
        
        // Hide all messages first
        successMessageElement.classList.add('hidden');
        errorMessageElement.classList.add('hidden');
        
        // Show the appropriate message
        if (type === 'success') {
            successTextElement.textContent = message;
            successMessageElement.classList.remove('hidden');
        } else {
            errorTextElement.textContent = message;
            errorMessageElement.classList.remove('hidden');
        }
        
        // Show the container
        statusMessageElement.classList.remove('hidden');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            statusMessageElement.classList.add('hidden');
        }, 5000);
    }
});

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
    
    // Mark form as changed for protection
    if (window.Alpine) {
        document.querySelector('[x-data="formProtection"]').__x.$data.formChanged = true;
    }
}

function removeLingkupMateri(button) {
    button.parentElement.remove();
    // Mark form as changed for protection
    if (window.Alpine) {
        document.querySelector('[x-data="formProtection"]').__x.$data.formChanged = true;
    }
}
</script>

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert("{{ session('error') }}");
    });
</script>
@endif
@endsection