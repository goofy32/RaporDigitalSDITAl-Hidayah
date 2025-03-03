@extends('layouts.pengajar.app')

@section('title', 'Edit Data Mata Pelajaran')

@section('content')
<div>
    <div class="p-6 bg-white mt-14 shadow-lg rounded-lg">
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
        <form id="editSubjectForm" 
            action="{{ route('pengajar.subject.update', $subject->id) }}" 
            x-data="formProtection"
            method="POST" 
            class="space-y-6"
            data-subject-id="{{ $subject->id }}">
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

            <!-- Hanya tampilkan informasi muatan lokal untuk guru biasa -->
            @if(auth()->guard('guru')->user()->jabatan == 'guru')
            <div class="mt-4">
                <div class="flex items-center">
                    <input id="is_muatan_lokal" type="checkbox" 
                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                        checked disabled>
                    <label for="is_muatan_lokal" class="ml-2 block text-sm text-gray-900">
                        Tandai sebagai Muatan Lokal
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">Sebagai guru biasa, mata pelajaran Anda ditetapkan sebagai muatan lokal secara otomatis.</p>
                <!-- Hidden input untuk memastikan nilai is_muatan_lokal tetap terkirim saat form disubmit -->
                <input type="hidden" name="is_muatan_lokal" value="1">
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
                            <option value="{{ $class->id }}" 
                                {{ old('kelas', $subject->kelas_id) == $class->id ? 'selected' : '' }}
                                {{ auth()->guard('guru')->user()->isWaliKelas() && 
                                   auth()->guard('guru')->user()->getWaliKelasId() == $class->id ? 
                                   'data-wali-kelas="true"' : '' }}>
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
                
                <!-- Informasi wali kelas akan ditampilkan oleh JavaScript jika relevan -->
                <div id="wali-kelas-info" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md hidden">
                    <p class="text-sm text-blue-800">
                        <span class="font-medium">Info:</span> 
                        Sebagai wali kelas, Anda secara otomatis dapat mengajar di kelas yang Anda walikan.
                    </p>
                </div>
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

            <!-- Lingkup Materi -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Lingkup Materi</label>
                <div id="lingkupMateriContainer">
                @foreach($subject->lingkupMateris as $index => $lm)
                <div class="flex items-center mb-2" data-lm-id="{{ $lm->id }}">
                    <input type="text" name="lingkup_materi[]" value="{{ old('lingkup_materi.'.$index, $lm->judul_lingkup_materi) }}" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                        data-original-value="{{ $lm->judul_lingkup_materi }}">
                    @if($index == 0)
                        <button type="button" onclick="addLingkupMateri()" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @else
                    <button type="button" onclick="confirmDeleteLingkupMateri(this, {{ $lm->id }})" class="ml-2 p-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
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

<script>
    // Variable to track lingkup materi items that need to be updated
    let lingkupMateriChanges = [];
    
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
    
    function addLingkupMateri() {
        const container = document.getElementById('lingkupMateriContainer');
        const div = document.createElement('div');
        div.className = 'flex items-center mb-2';
        div.setAttribute('data-lm-id', 'new');
        
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
        
        // Add change listener for the new input
        div.querySelector('input').addEventListener('change', () => {
            if (window.Alpine) {
                document.querySelector('[x-data="formProtection"]').__x.$data.formChanged = true;
            }
        });
    }
    
    function removeLingkupMateri(button) {
        // For new items that haven't been saved to DB
        button.closest('.flex.items-center').remove();
        if (window.Alpine) {
            document.querySelector('[x-data="formProtection"]').__x.$data.formChanged = true;
        }
    }
    
    function confirmDeleteLingkupMateri(button, id) {
        if (confirm('Apakah Anda yakin ingin menghapus Lingkup Materi ini? Semua tujuan pembelajaran terkait juga akan dihapus.')) {
            deleteLingkupMateri(button, id);
        }
    }

    async function checkForDependentData(lingkupMateriId) {
        try {
            // Gunakan route pengajar (bukan wali kelas)
            const response = await fetch(`/pengajar/lingkup-materi/${lingkupMateriId}/check-dependencies`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            // Periksa respons terlebih dahulu
            if (!response.ok) {
                console.error("Server returned error:", response.status);
                // Jika response error, kita langsung lewati pengecekan dependensi
                return false;
            }
            
            // Parse JSON response
            try {
                const data = await response.json();
                return data.hasDependents;
            } catch (jsonError) {
                console.error("JSON parsing error:", jsonError);
                // Jika gagal parsing JSON, skip pengecekan dependensi
                return false;
            }
        } catch (error) {
            console.error("Error checking dependencies:", error);
            // Jika ada error, kita asumsikan tidak ada dependensi agar bisa langsung hapus
            return false;
        }
    }
    
    function deleteLingkupMateri(button, id) {
        // Periksa Alpine.js dengan lebih aman
        let alpineComponent = document.querySelector('[x-data="formProtection"]');
        if (window.Alpine && alpineComponent && alpineComponent.__x) {
            try {
                alpineComponent.__x.$data.formChanged = true;
                // Gunakan isSubmitting, bukan startSubmitting (mungkin tidak ada di Alpine data)
                if ('isSubmitting' in alpineComponent.__x.$data) {
                    alpineComponent.__x.$data.isSubmitting = true;
                }
            } catch (error) {
                console.error("Alpine data error:", error);
                // Lanjutkan meskipun ada error di Alpine
            }
        }
        
        fetch(`/pengajar/subject/lingkup-materi/${id}`, {
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
                
                // Update Alpine data dengan aman
                if (window.Alpine && alpineComponent && alpineComponent.__x) {
                    try {
                        alpineComponent.__x.$data.formChanged = true;
                    } catch (error) {
                        console.error("Alpine data update error:", error);
                    }
                }
                
                showMessage('Lingkup materi berhasil dihapus', 'success');
            } else {
                showMessage(data.message || 'Gagal menghapus Lingkup Materi', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Terjadi kesalahan saat menghapus Lingkup Materi', 'error');
        })
        .finally(() => {
            // Reset Alpine data dengan aman
            if (window.Alpine && alpineComponent && alpineComponent.__x) {
                try {
                    if ('isSubmitting' in alpineComponent.__x.$data) {
                        alpineComponent.__x.$data.isSubmitting = false;
                    }
                } catch (error) {
                    console.error("Alpine data reset error:", error);
                }
            }
        });
    }

    
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
    
    // Add event listeners
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

        const mataPelajaranInput = document.getElementById('mata_pelajaran');
        const kelasSelect = document.getElementById('kelas');
        const semesterSelect = document.getElementById('semester');
        const submitButton = document.querySelector('button[type="submit"]');
        const currentId = {{ $subject->id }}; // ID mata pelajaran yang sedang diedit
        
        const guru = {
            id: {{ auth()->guard('guru')->id() }},
            isWaliKelas: {{ auth()->guard('guru')->user()->isWaliKelas() ? 'true' : 'false' }},
            waliKelasId: {{ auth()->guard('guru')->user()->getWaliKelasId() ?? 'null' }}
        };
        
        // Jika ada elemen yang tidak ditemukan, hentikan eksekusi
        if (!mataPelajaranInput || !kelasSelect || !semesterSelect || !submitButton) {
            console.error('Required elements not found');
            return;
        }
        
        // Cek apakah kelas yang dipilih adalah kelas wali
        if (kelasSelect && guru.isWaliKelas) {
            // Tampilkan info wali kelas jika kelas yang dipilih adalah kelas wali
            if (kelasSelect.value == guru.waliKelasId) {
                const infoElement = document.getElementById('wali-kelas-info');
                if (infoElement) {
                    infoElement.classList.remove('hidden');
                }
            }
            
            // Tambahkan event listener untuk perubahan kelas
            kelasSelect.addEventListener('change', function() {
                const selectedKelasId = this.value;
                const infoElement = document.getElementById('wali-kelas-info');
                
                // Tampilkan/sembunyikan info wali kelas berdasarkan pilihan
                if (selectedKelasId == guru.waliKelasId) {
                    infoElement.classList.remove('hidden');
                } else {
                    infoElement.classList.add('hidden');
                }
                
                // Validasi juga setiap kali kelas berubah
                validateMataPelajaran();
            });
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
        
        // Add listeners for existing Lingkup Materi inputs
        const lingkupMateriInputs = document.querySelectorAll('#lingkupMateriContainer input[name="lingkup_materi[]"]');
        
        lingkupMateriInputs.forEach(input => {
            const originalValue = input.getAttribute('data-original-value');
            
            input.addEventListener('change', () => {
                const container = input.closest('[data-lm-id]');
                const lmId = container.getAttribute('data-lm-id');
                
                // Only track changes for existing items (with an ID)
                if (lmId !== 'new') {
                    const currentValue = input.value.trim();
                    
                    // Check if the value has changed
                    if (currentValue !== originalValue) {
                        // Flag this for update
                        if (window.Alpine) {
                            document.querySelector('[x-data="formProtection"]').__x.$data.formChanged = true;
                        }
                    }
                }
            });
        });
        
        // Event listener untuk input dan perubahan
        mataPelajaranInput.addEventListener('input', function() {
            validateMataPelajaran();
            if (window.Alpine) {
                document.querySelector('[x-data="formProtection"]').__x.$data.formChanged = true;
            }
        });
        
        semesterSelect.addEventListener('change', function() {
            validateMataPelajaran();
            if (window.Alpine) {
                document.querySelector('[x-data="formProtection"]').__x.$data.formChanged = true;
            }
        });
        
        kelasSelect.addEventListener('change', function() {
            validateMataPelajaran();
            if (window.Alpine) {
                document.querySelector('[x-data="formProtection"]').__x.$data.formChanged = true;
            }
        });
        
        // Validasi setelah halaman dimuat
        validateMataPelajaran();
        
        // Intercept form submission
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
            
            // Jika validasi lolos, biarkan form submit normal
            if (window.Alpine) {
                document.querySelector('[x-data="formProtection"]').__x.$data.isSubmitting = true;
            }
            return true;
        }, true); // true untuk capture phase (akan dijalankan sebelum event handler lain)
    });
</script>

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert("{{ session('error') }}");
    });
</script>
@endif
@endsection