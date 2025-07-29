@extends('layouts.app')

@section('title', 'Tambah Tahun Ajaran')

@section('content')
<div class="p-4 bg-white">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Tambah Tahun Ajaran Baru</h2>
        <div class="flex space-x-3">
            <a href="{{ route('tahun.ajaran.index') }}"
                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-white bg-gray-600">
                Batal
            </a>
            <button type="submit" form="createTahunAjaranForm"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Simpan Tahun Ajaran
            </button>
        </div>
    </div>

    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Terjadi kesalahan:</p>
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Alert Info tentang semester genap -->
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">
                    Tips: Melanjutkan dari Tahun Ajaran Sebelumnya
                </h3>
                <div class="mt-2 text-sm text-green-700">
                    <p>Jika Anda memiliki tahun ajaran semester genap, anda harus menggunakan fitur <strong>"Buat Tahun Ajaran Berikutnya"</strong>. Pada Tahun Ajaran Aktif - Pergi ke <strong> Detail (Icon mata) </strong> - <strong> "Buat Tahun Ajaran Berikutnya" </strong> untuk melanjutkan ke tahun ajaran berikutnya dengan struktur kelas dan guru yang sama.</p>
                </div>
            </div>
        </div>
    </div>

    <form id="createTahunAjaranForm" action="{{ route('tahun.ajaran.store') }}" method="POST" class="space-y-6" data-needs-protection>
        @csrf
        
        <!-- Hidden semester field - always 1 (Ganjil) for new academic years -->
        <input type="hidden" name="semester" value="1">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran" value="{{ old('tahun_ajaran') }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    placeholder="contoh: 2024/2025">
                <p class="mt-1 text-sm text-gray-500">Format: YYYY/YYYY (contoh: 2024/2025)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                <div class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
                    Ganjil (Semester 1)
                    <p class="mt-1 text-xs text-gray-500">Tahun ajaran baru selalu dimulai dengan semester ganjil</p>
                </div>
            </div>

            <div>
                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>

            <div>
                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="md:col-span-2">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                    placeholder="Berikan deskripsi singkat tentang tahun ajaran ini (opsional)">{{ old('deskripsi') }}</textarea>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check semester genap saat halaman dimuat
        checkSemesterGenapOnLoad();
        
        // Validasi format tahun ajaran
        const tahunAjaranInput = document.getElementById('tahun_ajaran');
        tahunAjaranInput.addEventListener('blur', function() {
            const value = this.value.trim();
            const pattern = /^\d{4}\/\d{4}$/;
            
            if (value && !pattern.test(value)) {
                const errorMsg = document.createElement('p');
                errorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'tahun-ajaran-error');
                errorMsg.textContent = 'Format tahun ajaran harus YYYY/YYYY, contoh: 2024/2025';
                
                // Remove any existing error message
                const existingError = document.querySelector('.tahun-ajaran-error');
                if (existingError) existingError.remove();
                
                // Add the error message
                this.parentNode.appendChild(errorMsg);
                
                // Add invalid class to input
                this.classList.add('border-red-500');
            } else {
                // Remove error message if format is correct
                const existingError = document.querySelector('.tahun-ajaran-error');
                if (existingError) existingError.remove();
                
                // Remove invalid class
                this.classList.remove('border-red-500');
            }
        });
        
        // Validasi tanggal selesai harus setelah tanggal mulai
        const tanggalMulaiInput = document.getElementById('tanggal_mulai');
        const tanggalSelesaiInput = document.getElementById('tanggal_selesai');
        
        tanggalSelesaiInput.addEventListener('change', function() {
            if (tanggalMulaiInput.value && this.value) {
                const mulai = new Date(tanggalMulaiInput.value);
                const selesai = new Date(this.value);
                
                if (selesai <= mulai) {
                    const errorMsg = document.createElement('p');
                    errorMsg.classList.add('text-red-500', 'text-sm', 'mt-1', 'tanggal-error');
                    errorMsg.textContent = 'Tanggal selesai harus setelah tanggal mulai';
                    
                    // Remove any existing error message
                    const existingError = document.querySelector('.tanggal-error');
                    if (existingError) existingError.remove();
                    
                    // Add the error message
                    this.parentNode.appendChild(errorMsg);
                    
                    // Add invalid class
                    this.classList.add('border-red-500');
                } else {
                    // Remove error message
                    const existingError = document.querySelector('.tanggal-error');
                    if (existingError) existingError.remove();
                    
                    // Remove invalid class
                    this.classList.remove('border-red-500');
                }
            }
        });
        
        // Form validation before submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const errors = document.querySelectorAll('.text-red-500');
            if (errors.length > 0) {
                e.preventDefault();
                alert('Mohon perbaiki error pada form sebelum melanjutkan.');
            }
        });
    });

    // Function untuk mengecek semester genap saat halaman dimuat
    async function checkSemesterGenapOnLoad() {
        try {
            const response = await fetch('/admin/tahun-ajaran/check-semester-genap', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                console.warn('Gagal mengecek status semester genap');
                return;
            }

            const data = await response.json();
            
            if (data.hasSemseterGenap) {
                // Tampilkan alert dengan SweetAlert2
                Swal.fire({
                    title: 'Ditemukan Tahun Ajaran Semester Genap!',
                    html: `
                        <div class="text-left">
                            <p class="mb-3">Sistem menemukan tahun ajaran <strong>${data.tahunAjaran}</strong> semester genap.</p>
                            <p class="mb-3">Disarankan menggunakan fitur <strong>"Copy Tahun Ajaran"</strong> untuk:</p>
                            <ul class="list-disc ml-5 mb-3">
                                <li>Melanjutkan struktur kelas yang sama</li>
                                <li>Mempertahankan assignment guru</li>
                                <li>Menyalin pengaturan yang sudah ada</li>
                            </ul>
                            <p class="text-sm text-gray-600">Anda tetap bisa membuat tahun ajaran baru jika diperlukan.</p>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Gunakan Copy Tahun Ajaran',
                    cancelButtonText: 'Tetap Buat Baru',
                    confirmButtonColor: '#059669', // Green color
                    cancelButtonColor: '#6b7280', // Gray color
                    reverseButtons: true,
                    allowOutsideClick: false,
                    width: '600px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect ke halaman copy
                        window.location.href = data.copyUrl;
                    }
                    // Jika cancel, tetap di halaman create (tidak ada action)
                });
            }
        } catch (error) {
            console.error('Error checking semester genap:', error);
            // Silent error, tidak mengganggu user experience
        }
    }
</script>
@endpush
@endsection