@extends('layouts.app')

@section('title', 'Edit Data Pengajar')

@section('content')
<div>
   <div class="p-4 bg-white mt-14 rounded-lg shadow">
       <!-- Error Messages -->
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

       @if(session('error'))
       <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
           <div class="flex">
               <div class="flex-shrink-0">
                   <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                       <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                   </svg>
               </div>
               <div class="ml-3">
                   <p class="text-sm">{{ session('error') }}</p>
               </div>
           </div>
       </div>
       @endif

       <!-- Success Message -->
       @if (session('success'))
       <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
           <div class="flex">
               <div class="flex-shrink-0">
                   <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                       <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                   </svg>
               </div>
               <div class="ml-3">
                   <p class="text-sm font-medium">{{ session('success') }}</p>
               </div>
           </div>
       </div>
       @endif

       <!-- Header -->
       <div class="flex justify-between items-center mb-6">
           <h2 class="text-2xl font-bold text-green-700">Form Edit Data Pengajar</h2>
           <div class="flex space-x-2">
               <button onclick="window.history.back()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                   Kembali
               </button>
               <button form="editTeacherForm" type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                   Simpan
               </button>
           </div>
       </div>

       <!-- Form -->
       <form id="editTeacherForm" action="{{ route('teacher.update', $teacher->id) }}"  @submit="handleSubmit" method="POST" x-data="formProtection" enctype="multipart/form-data">
           @csrf
           @method('PUT')

           <input type="hidden" name="tahun_ajaran_id" value="{{ session('tahun_ajaran_id') }}">

           <div class="w-full px-4">
               <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                   <!-- Kolom Kiri -->
                   <div class="space-y-4">
                       <!-- NUPTK -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">NUPTK</label>
                           <input type="number" name="nuptk" value="{{ old('nuptk', $teacher->nuptk) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);">
                          <p class="mt-1 text-sm text-gray-500">Masukkan hanya angka (9-15 digit)</p>
                       </div>

                       <!-- Nama -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">Nama</label>
                           <input type="text" name="nama" value="{{ old('nama', $teacher->nama) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                       </div>

                       <!-- Jenis Kelamin -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                           <select name="jenis_kelamin" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                               <option value="Laki-laki" {{ old('jenis_kelamin', $teacher->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                               <option value="Perempuan" {{ old('jenis_kelamin', $teacher->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                           </select>
                       </div>

                       <!-- Tanggal Lahir -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                           <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $teacher->tanggal_lahir) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                       </div>

                       <!-- No. Handphone -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">No. Handphone</label>
                           <input type="number" name="no_handphone" value="{{ old('no_handphone', $teacher->no_handphone) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);">
                           <p class="mt-1 text-sm text-gray-500">Masukkan hanya angka (10-15 digit)</p>
                       </div>

                       <!-- Email -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">Email</label>
                           <input type="email" name="email" value="{{ old('email', $teacher->email) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                       </div>

                       <!-- Alamat -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">Alamat</label>
                           <textarea name="alamat" rows="3" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">{{ old('alamat', $teacher->alamat) }}</textarea>
                       </div>
                   </div>

                   <!-- Kolom Kanan -->
                   <div class="space-y-4">
                       <!-- Jabatan -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">Jabatan</label>
                           <select name="jabatan" id="jabatan" onchange="handleJabatanChange()" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                               <option value="guru" {{ $teacher->jabatan === 'guru' ? 'selected' : '' }}>Guru</option>
                               <option value="guru_wali" {{ $teacher->jabatan === 'guru_wali' ? 'selected' : '' }}>Guru dan Wali Kelas</option>
                           </select>
                       </div>

                       <!-- Kelas Mengajar -->
                       <div id="kelas_mengajar_section">
                           <label class="block text-sm font-medium text-gray-700">Kelas yang Diajar</label>
                           
                           @php
                               // Ambil semua kelas yang diajar (pengajar), kecuali yang sudah diwalikan
                               $kelasAjar = $teacher->kelas()->wherePivot('role', 'pengajar')->pluck('kelas.id')->toArray();
                               
                               // Ambil kelas yang diwalikan
                               $kelasWali = $teacher->kelas()->wherePivot('is_wali_kelas', true)
                                                            ->wherePivot('role', 'wali_kelas')
                                                            ->first();
                           @endphp
                           
                           <select name="kelas_ids[]" multiple required id="kelas_mengajar"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 min-h-[120px]">
                               @foreach($kelasList as $kelas)
                                   <option value="{{ $kelas->id }}" 
                                       {{ in_array($kelas->id, $kelasAjar) ? 'selected' : '' }}>
                                       Kelas {{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}
                                   </option>
                               @endforeach
                           </select>
                           <p class="mt-1 text-sm text-gray-500">Tekan CTRL untuk memilih beberapa kelas yang akan diajar</p>
                           
                           @if($kelasWali)
                               <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                                   <p class="text-sm text-blue-800">
                                       <span class="font-medium">Catatan:</span> Guru ini menjadi wali kelas untuk Kelas {{ $kelasWali->nomor_kelas }} {{ $kelasWali->nama_kelas }}. 
                                       Kelas wali tidak perlu dipilih di daftar kelas mengajar, karena akan otomatis ditambahkan.
                                   </p>
                               </div>
                           @endif
                       </div>

                       <!-- Wali Kelas -->
                       <div id="wali_kelas_section" style="{{ $teacher->jabatan === 'guru_wali' ? '' : 'display:none;' }}">
                           <label class="block text-sm font-medium text-gray-700">Wali Kelas Untuk</label>
                           <select name="wali_kelas_id" id="wali_kelas_id"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                               <option value="">Pilih Kelas</option>
                               @foreach($availableKelas as $kelas)
                                   <option value="{{ $kelas->id }}" 
                                       {{ ($kelasWali && $kelasWali->id === $kelas->id) ? 'selected' : '' }}>
                                       Kelas {{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}
                                   </option>
                               @endforeach
                           </select>
                           @if($kelasWali)
                               <p class="mt-1 text-sm text-gray-600">
                                   Saat ini menjadi wali kelas: 
                                   Kelas {{ $kelasWali->nomor_kelas }} {{ $kelasWali->nama_kelas }}
                               </p>
                           @endif
                       </div>

                       <!-- Username -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">Username</label>
                           <input type="text" name="username" value="{{ old('username', $teacher->username) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                       </div>

                       <!-- Photo -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">Photo (ukuran 4x6 atau 2x3)</label>
                           <input type="file" name="photo" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                       </div>

                       <!-- Password Section -->
                       <div class="pt-4 border-t border-gray-200">
                           <h3 class="text-lg font-medium text-gray-900 mb-4">Ubah Password</h3>
                           
                           <div class="space-y-4">
                               <div>
                                   <label class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                                   <input type="password" name="current_password"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                               </div>

                               <div>
                                   <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                                   <input type="password" name="password"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                               </div>

                               <div>
                                   <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                                   <input type="password" name="password_confirmation"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                               </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Function untuk handle perubahan jabatan
    window.handleJabatanChange = function() {
        const jabatan = document.getElementById('jabatan').value;
        const waliKelasSection = document.getElementById('wali_kelas_section');
        const kelasMengajarSection = document.getElementById('kelas_mengajar_section');
        const waliKelasSelect = document.querySelector('[name="wali_kelas_id"]');
        const kelasMengajarSelect = document.querySelector('[name="kelas_ids[]"]');

        if (jabatan === 'guru_wali') {
            waliKelasSection.style.display = 'block';
            kelasMengajarSection.style.display = 'block';
            if(waliKelasSelect) waliKelasSelect.required = true;
            if(kelasMengajarSelect) kelasMengajarSelect.required = true;
            
            // Tambahkan event listener untuk perubahan pada wali kelas
            if(waliKelasSelect) {
                waliKelasSelect.addEventListener('change', function() {
                    updateKelasMengajarForWali();
                });
            }
        } else if (jabatan === 'guru') {
            waliKelasSection.style.display = 'none';
            kelasMengajarSection.style.display = 'block';
            if(waliKelasSelect) waliKelasSelect.required = false;
            if(kelasMengajarSelect) kelasMengajarSelect.required = true;
            
            // Enable semua opsi kelas mengajar
            enableAllKelasMengajarOptions();
            
            if(waliKelasSelect) waliKelasSelect.value = '';
        } else {
            waliKelasSection.style.display = 'none';
            kelasMengajarSection.style.display = 'none';
            if(waliKelasSelect) waliKelasSelect.required = false;
            if(kelasMengajarSelect) kelasMengajarSelect.required = false;
        }
        
        // Update kelas mengajar berdasarkan kelas wali jika sudah dipilih
        if(jabatan === 'guru_wali' && waliKelasSelect && waliKelasSelect.value) {
            updateKelasMengajarForWali();
        }
    };
    
    // Function untuk update kelas mengajar berdasarkan kelas wali
    function updateKelasMengajarForWali() {
        const waliKelasId = document.querySelector('[name="wali_kelas_id"]').value;
        const kelasMengajarSelect = document.querySelector('[name="kelas_ids[]"]');
        
        if(waliKelasId && kelasMengajarSelect) {
            // Disable semua opsi terlebih dahulu
            Array.from(kelasMengajarSelect.options).forEach(option => {
                option.disabled = true;
                option.selected = false;
            });
            
            // Enable dan select hanya kelas yang menjadi wali kelas
            Array.from(kelasMengajarSelect.options).forEach(option => {
                if(option.value === waliKelasId) {
                    option.disabled = false;
                    option.selected = true;
                }
            });
            
            // Tambahkan pesan informasi
            let infoText = document.getElementById('kelas_mengajar_info');
            if(!infoText) {
                infoText = document.createElement('p');
                infoText.id = 'kelas_mengajar_info';
                infoText.className = 'mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-800';
                kelasMengajarSelect.parentElement.appendChild(infoText);
            }
            infoText.innerHTML = '<span class="font-medium">Info:</span> Karena Anda terpilih sebagai wali kelas, Anda hanya dapat mengajar di kelas wali yang dipilih.';
        }
    }
    
    // Function untuk enable semua opsi kelas mengajar
    function enableAllKelasMengajarOptions() {
        const kelasMengajarSelect = document.querySelector('[name="kelas_ids[]"]');
        if(kelasMengajarSelect) {
            Array.from(kelasMengajarSelect.options).forEach(option => {
                option.disabled = false;
            });
            
            // Hapus pesan informasi jika ada
            const infoText = document.getElementById('kelas_mengajar_info');
            if(infoText) {
                infoText.remove();
            }
        }
    }

    // Password validation
    const passwordForm = document.getElementById('editTeacherForm');
    const newPassword = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="password_confirmation"]');
    const currentPassword = document.querySelector('input[name="current_password"]');

    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            if (newPassword.value) {
                if (!currentPassword.value) {
                    e.preventDefault();
                    alert('Password saat ini harus diisi untuk mengubah password');
                    currentPassword.focus();
                    return;
                }

                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Konfirmasi password tidak cocok');
                    confirmPassword.focus();
                    return;
                }
            }
        });
    }

    // Form validation
    const form = document.querySelector('form');
    const requiredFields = form.querySelectorAll('[required]');

    form.addEventListener('submit', function(e) {
        let hasError = false;
        requiredFields.forEach(field => {
            if (field.multiple && field.selectedOptions.length === 0) {
                hasError = true;
                field.classList.add('border-red-500');
                addErrorMessage(field, 'Pilih minimal satu kelas');
            } else if (!field.multiple && !field.value.trim()) {
                hasError = true;
                field.classList.add('border-red-500');
                addErrorMessage(field, `${field.getAttribute('placeholder') || field.getAttribute('name')} wajib diisi`);
            }
        });

        // Validasi khusus untuk guru_wali
        const jabatan = document.getElementById('jabatan').value;
        const waliKelasId = document.getElementById('wali_kelas_id');
        
        if (jabatan === 'guru_wali' && (!waliKelasId.value || waliKelasId.value === '')) {
            hasError = true;
            waliKelasId.classList.add('border-red-500');
            addErrorMessage(waliKelasId, 'Wali kelas harus dipilih untuk guru dengan jabatan Guru dan Wali Kelas');
        }

        if (hasError) {
            e.preventDefault();
            const firstError = form.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    function addErrorMessage(field, message) {
        let errorDiv = field.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('p');
            errorDiv.className = 'error-message text-red-500 text-xs mt-1';
            field.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }

    // File validation
    const photoInput = document.querySelector('input[type="file"]');
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            validateFile(this);
        });
    }

    function validateFile(input) {
        const file = input.files[0];
        if (!file) return;
        
        const fileType = file.type;
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 2 * 1024 * 1024;
        
        if (!allowedTypes.includes(fileType)) {
            alert('Format file harus JPG, JPEG, atau PNG');
            input.value = '';
            return;
        }
        
        if (file.size > maxSize) {
            alert('Ukuran file maksimal 2MB');
            input.value = '';
            return;
        }
    }

    // Set initial state
    handleJabatanChange();
    
    // Event listener untuk perubahan pada wali kelas di awal load
    const waliKelasSelect = document.querySelector('[name="wali_kelas_id"]');
    const jabatan = document.getElementById('jabatan').value;
    if(jabatan === 'guru_wali' && waliKelasSelect && waliKelasSelect.value) {
        updateKelasMengajarForWali();
    }
});
</script>
@endpush
@endsection