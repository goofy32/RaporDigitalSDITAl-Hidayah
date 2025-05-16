@extends('layouts.app')

@section('title', 'Proses Kenaikan Kelas')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Proses {{ $isKelasAkhir ? 'Kelulusan' : 'Kenaikan Kelas' }}</h2>
        <a href="{{ route('admin.kenaikan-kelas.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Kelas {{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}</h3>
        <p class="text-gray-600">Wali Kelas: {{ $kelas->waliKelasName }}</p>
        <p class="text-gray-600">Jumlah Siswa: {{ $siswaList->count() }}</p>
    </div>

    @if($siswaList->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-yellow-800">Tidak ada siswa yang perlu diproses di kelas ini. Semua siswa mungkin sudah dipindahkan atau diluluskan.</p>
    </div>
    @else
    
    @if(!$isKelasAkhir && $kelasTujuan->isEmpty())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800">Tidak ada kelas tujuan yang tersedia di tahun ajaran baru. Pastikan kelas untuk tingkat berikutnya sudah dibuat.</p>
        <a href="{{ route('kelas.create') }}" class="text-blue-600 hover:underline mt-2 inline-block">Buat Kelas Baru</a>
    </div>
    @else
    
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <input id="select-all" type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500">
            <label for="select-all" class="ml-2 block text-sm text-gray-900">Pilih Semua Siswa</label>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pilih</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NIS</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jenis Kelamin</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status Rapor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($siswaList as $siswa)
                    <tr data-siswa-id="{{ $siswa->id }}">
                        <td class="py-3 px-4 border-b">
                            <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="student-checkbox h-4 w-4 text-green-600 focus:ring-green-500">
                        </td>
                        <td class="py-3 px-4 border-b">{{ $siswa->nis }}</td>
                        <td class="py-3 px-4 border-b">{{ $siswa->nama }}</td>
                        <td class="py-3 px-4 border-b">{{ $siswa->jenis_kelamin }}</td>
                        <td class="py-3 px-4 border-b">
                            @if($raporStatus[$siswa->id])
                                <span class="inline-flex items-center bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    <span class="w-2 h-2 mr-1 bg-green-500 rounded-full"></span>
                                    Rapor Tersedia
                                </span>
                            @else
                                <span class="inline-flex items-center bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    <span class="w-2 h-2 mr-1 bg-gray-500 rounded-full"></span>
                                    Belum Ada Rapor
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6" id="actionForms" style="display: none;">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Proses Siswa Terpilih</h3>
        <p class="mb-3">Anda telah memilih <span id="selectedCount" class="font-semibold">0</span> siswa.</p>
        
        @if($isKelasAkhir)
        <!-- Form untuk Kelulusan -->
        <form action="{{ route('admin.kenaikan-kelas.process-kelulusan') }}" method="POST" class="space-y-4" id="kelulusanForm">
            @csrf
            <div id="selectedKelulusanIds"></div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Status --</option>
                    <option value="lulus">Lulus</option>
                    <option value="pindah">Pindah</option>
                    <option value="dropout">Dropout</option>
                </select>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="check-rapor-btn px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-action="proses kelulusan">
                    Proses Kelulusan
                </button>
            </div>
        </form>
        @else
        <!-- Form untuk Kenaikan Kelas -->
        <div class="space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Form Naik Kelas -->
                <form action="{{ route('admin.kenaikan-kelas.process-kenaikan') }}" method="POST" class="flex-1 bg-white p-4 rounded-lg border border-gray-200" id="naik-kelas-form">
                    @csrf
                    <div id="selectedNaikIds"></div>
                    
                    <h4 class="text-md font-semibold text-green-700 mb-3">Naik Kelas</h4>
                    
                    <div class="mb-4">
                        <label for="kelas_tujuan_id" class="block text-sm font-medium text-gray-700">Kelas Tujuan</label>
                        <!-- Force green styling with inline style as a backup -->
                        <select 
                            name="kelas_tujuan_id" 
                            id="kelas_tujuan_id" 
                            required 
                            class="mt-1 block w-full rounded-md border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            style="border-color: rgb(134, 239, 172); outline-color: rgb(34, 197, 94);">
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            @foreach($kelasTujuan as $target)
                            <option value="{{ $target->id }}">Kelas {{ $target->nomor_kelas }} {{ $target->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="check-rapor-btn px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-action="kenaikan kelas">
                            Proses Naik Kelas
                        </button>
                    </div>
                </form>
                
                <!-- Form Tinggal Kelas -->
                <form action="{{ route('admin.kenaikan-kelas.process-tinggal') }}" method="POST" class="flex-1 bg-white p-4 rounded-lg border border-gray-200" id="tinggal-kelas-form">
                    @csrf
                    <div id="selectedTinggalIds"></div>
                    
                    <h4 class="text-md font-semibold text-red-700 mb-3">Tinggal Kelas</h4>
                    
                    <div class="mb-4">
                        <label for="kelas_tinggal_id" class="block text-sm font-medium text-gray-700">Kelas Tujuan (Tingkat yang Sama)</label>
                        <select name="kelas_tujuan_id" id="kelas_tinggal_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-yellow-500">
                            <option value="">-- Pilih Kelas Tinggal --</option>
                            @php
                                // Get distinct classes for tinggal kelas option (same grade level in the new academic year)
                                $kelasTinggal = \App\Models\Kelas::where('tahun_ajaran_id', $tahunAjaranBaru->id)
                                        ->where('nomor_kelas', $kelas->nomor_kelas)
                                        ->orderBy('nama_kelas')
                                        ->get()
                                        ->unique(function($item) {
                                            return $item->nomor_kelas . $item->nama_kelas;
                                        });
                            @endphp
                            @foreach($kelasTinggal as $target)
                            <option value="{{ $target->id }}">Kelas {{ $target->nomor_kelas }} {{ $target->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="check-rapor-btn px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-yellow-500" data-action="tinggal kelas">
                            Proses Tinggal Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    @endif
    @endif
</div>

<!-- Add this CSS to enforce styles -->
<style>
    /* Force green and red styles for dropdowns */
    #kelas_tujuan_id {
        border-color: rgb(134, 239, 172) !important;
    }
    #kelas_tujuan_id:focus {
        border-color: rgb(34, 197, 94) !important;
        box-shadow: 0 0 0 1px rgb(34, 197, 94) !important;
    }
    
    #kelas_tinggal_id {
        border-color: rgb(252, 165, 165) !important;
    }
    #kelas_tinggal_id:focus {
        border-color: rgb(239, 68, 68) !important;
        box-shadow: 0 0 0 1px rgb(239, 68, 68) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variable to track selected student IDs
    let selectedStudents = [];
    
    // Report status data from PHP
    const raporStatus = @json($raporStatus);
    
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            
            updateSelectedStudents();
        });
    }
    
    // Individual checkbox functionality
    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedStudents();
            
            // Update "select all" checkbox state
            if (selectAllCheckbox) {
                const allChecked = document.querySelectorAll('.student-checkbox:checked').length === studentCheckboxes.length;
                selectAllCheckbox.checked = allChecked;
            }
        });
    });
    
    // Update selected students list
    function updateSelectedStudents() {
        selectedStudents = [];
        document.querySelectorAll('.student-checkbox:checked').forEach(checkbox => {
            selectedStudents.push(checkbox.value);
        });
        
        // Update display and hidden fields
        const selectedCount = document.getElementById('selectedCount');
        if (selectedCount) {
            selectedCount.textContent = selectedStudents.length;
        }
        
        // Show/hide action forms
        const actionForms = document.getElementById('actionForms');
        if (actionForms) {
            actionForms.style.display = selectedStudents.length > 0 ? 'block' : 'none';
        }
        
        // Update hidden inputs in forms
        updateHiddenInputs('selectedKelulusanIds', selectedStudents);
        updateHiddenInputs('selectedNaikIds', selectedStudents);
        updateHiddenInputs('selectedTinggalIds', selectedStudents);
    }
    
    // Update hidden inputs in a form
    function updateHiddenInputs(containerId, selectedIds) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '';
            
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'siswa_ids[]';
                input.value = id;
                container.appendChild(input);
            });
        }
    }
    
    // Force apply styles to dropdowns
    function forceApplyStyles() {
        // For the naik kelas dropdown
        const naikKelasDropdown = document.getElementById('kelas_tujuan_id');
        if (naikKelasDropdown) {
            naikKelasDropdown.style.borderColor = 'rgb(134, 239, 172)';
            naikKelasDropdown.addEventListener('focus', function() {
                this.style.borderColor = 'rgb(34, 197, 94)';
                this.style.boxShadow = '0 0 0 1px rgb(34, 197, 94)';
            });
            naikKelasDropdown.addEventListener('blur', function() {
                this.style.borderColor = 'rgb(134, 239, 172)';
                this.style.boxShadow = 'none';
            });
        }
        
        // For the tinggal kelas dropdown
        const tinggalKelasDropdown = document.getElementById('kelas_tinggal_id');
        if (tinggalKelasDropdown) {
            tinggalKelasDropdown.style.borderColor = 'rgb(252, 165, 165)';
            tinggalKelasDropdown.addEventListener('focus', function() {
                this.style.borderColor = 'rgb(239, 68, 68)';
                this.style.boxShadow = '0 0 0 1px rgb(239, 68, 68)';
            });
            tinggalKelasDropdown.addEventListener('blur', function() {
                this.style.borderColor = 'rgb(252, 165, 165)';
                this.style.boxShadow = 'none';
            });
        }
    }
    
    // Apply the styles immediately and after any potential framework might override them
    forceApplyStyles();
    setTimeout(forceApplyStyles, 100);
    
    // Add report check functionality to all buttons
    const checkRaporButtons = document.querySelectorAll('.check-rapor-btn');
    checkRaporButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const actionType = this.getAttribute('data-action');
            
            // Check if any selected students don't have reports
            const noRaporStudents = [];
            
            selectedStudents.forEach(id => {
                if (!raporStatus[id]) {
                    const studentRow = document.querySelector(`tr[data-siswa-id='${id}']`);
                    if (studentRow) {
                        const studentName = studentRow.querySelector('td:nth-child(3)').textContent;
                        noRaporStudents.push(studentName);
                    }
                }
            });
            
            // If there are students without reports, show confirmation
            if (noRaporStudents.length > 0) {
                // Build warning message
                let warningHtml = '<p>Siswa berikut belum memiliki rapor:</p><ul class="text-left mt-2">';
                
                noRaporStudents.forEach(name => {
                    warningHtml += `<li>- ${name}</li>`;
                });
                
                warningHtml += `</ul><p class="mt-3">Apakah Anda tetap ingin melanjutkan ${actionType}?</p>`;
                
                // Show SweetAlert confirmation
                Swal.fire({
                    title: 'Perhatian!',
                    html: warningHtml,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                // If all selected students have reports, submit form directly
                form.submit();
            }
        });
    });
    
    // Initialize on page load
    updateSelectedStudents();
});
</script>

@if(session('siswa_details'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Siapkan detail siswa
    let detailHtml = '<div class="max-h-60 overflow-y-auto py-2">';
    detailHtml += '<ul class="text-left">';
    
    @foreach(session('siswa_details') as $detail)
        @if(session('action_type') == 'kenaikan')
            detailHtml += '<li class="mb-2 flex items-start">' + 
                          '<span class="text-green-600 mr-1">↗</span> ' +
                          '<div><strong>{{ $detail['nama'] }}</strong><br>' + 
                          '{{ $detail['kelas_asal'] }} → {{ $detail['kelas_tujuan'] }}</div></li>';
        @elseif(session('action_type') == 'tinggal')
            detailHtml += '<li class="mb-2 flex items-start">' + 
                          '<span class="text-yellow-600 mr-1">↔</span> ' +
                          '<div><strong>{{ $detail['nama'] }}</strong><br>' + 
                          '{{ $detail['kelas_asal'] }} → {{ $detail['kelas_tujuan'] }}</div></li>';
        @elseif(session('action_type') == 'kelulusan')
            detailHtml += '<li class="mb-2 flex items-start">' + 
                          '<span class="{{ session('status') == 'lulus' ? 'text-blue-600' : (session('status') == 'pindah' ? 'text-purple-600' : 'text-red-600') }} mr-1">{{ session('status') == 'lulus' ? '🎓' : (session('status') == 'pindah' ? '🔄' : '⛔') }}</span> ' +
                          '<div><strong>{{ $detail['nama'] }}</strong><br>' + 
                          '{{ $detail['kelas_asal'] }} → {{ session('status') }}</div></li>';
        @endif
    @endforeach
    
    detailHtml += '</ul></div>';
    
    // Tampilkan SweetAlert dengan detail
    Swal.fire({
        title: 'Berhasil!',
        html: detailHtml,
        icon: 'success',
        confirmButtonColor: '#10b981',
        confirmButtonText: 'OK'
    });
});
</script>
@endif
@endsection