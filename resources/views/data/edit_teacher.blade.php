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
           <div class="w-full px-4">
               <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                   <!-- Kolom Kiri -->
                   <div class="space-y-4">
                       <!-- NUPTK -->
                       <div>
                           <label class="block text-sm font-medium text-gray-700">NUPTK</label>
                           <input type="text" name="nuptk" value="{{ old('nuptk', $teacher->nuptk) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
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
                           <input type="text" name="no_handphone" value="{{ old('no_handphone', $teacher->no_handphone) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
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
                           <select name="kelas_ids[]" multiple required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 min-h-[120px]">
                               @foreach($kelasList as $kelas)
                                   <option value="{{ $kelas->id }}" 
                                       {{ in_array($kelas->id, $teacher->kelas->pluck('id')->toArray()) ? 'selected' : '' }}>
                                       Kelas {{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}
                                   </option>
                               @endforeach
                           </select>
                           <p class="mt-1 text-sm text-gray-500">Tekan CTRL untuk memilih beberapa kelas yang akan diajar</p>
                       </div>

                       <!-- Wali Kelas -->
                       <div id="wali_kelas_section" style="{{ $teacher->jabatan === 'guru_wali' ? '' : 'display:none;' }}">
                           <label class="block text-sm font-medium text-gray-700">Wali Kelas Untuk</label>
                           <select name="wali_kelas_id" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                               <option value="">Pilih Kelas</option>
                               @foreach($availableKelas as $kelas)
                                   <option value="{{ $kelas->id }}" 
                                       {{ $currentWaliKelas && $currentWaliKelas->id === $kelas->id ? 'selected' : '' }}>
                                       Kelas {{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}
                                   </option>
                               @endforeach
                           </select>
                           @if($currentWaliKelas)
                               <p class="mt-1 text-sm text-gray-600">
                                   Saat ini menjadi wali kelas: 
                                   Kelas {{ $currentWaliKelas->nomor_kelas }} {{ $currentWaliKelas->nama_kelas }}
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
        } else if (jabatan === 'guru') {
            waliKelasSection.style.display = 'none';
            kelasMengajarSection.style.display = 'block';
            if(waliKelasSelect) waliKelasSelect.required = false;
            if(kelasMengajarSelect) kelasMengajarSelect.required = true;
        } else {
            waliKelasSection.style.display = 'none';
            kelasMengajarSection.style.display = 'none';
            if(waliKelasSelect) waliKelasSelect.required = false;
            if(kelasMengajarSelect) kelasMengajarSelect.required = false;
        }
    };

    // Password validation
    const passwordForm = document.getElementById('editTeacherForm');
    const newPassword = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="password_confirmation"]');
    const currentPassword = document.querySelector('input[name="current_password"]');

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

    // Form validation
    const form = document.querySelector('form');
    const requiredFields = form.querySelectorAll('[required]');

    form.addEventListener('submit', function(e) {
        let hasError = false;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                hasError = true;
                field.classList.add('border-red-500');
                addErrorMessage(field, `${field.getAttribute('placeholder') || field.getAttribute('name')} wajib diisi`);
            }
        });

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

    // NUPTK and phone number validation
    const numericInputs = document.querySelectorAll('input[name="nuptk"], input[name="no_handphone"]');
    numericInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);
        });
    });

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
});
</script>
@endpush
@endsection