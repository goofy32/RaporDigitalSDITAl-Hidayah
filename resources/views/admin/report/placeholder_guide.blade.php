<div class="p-6 max-h-[80vh] overflow-y-auto">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-green-700">Panduan Placeholder Template Rapor</h3>
        <button onclick="closePlaceholderGuide()" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="mb-4">
        <p class="mb-2">Template rapor harus menggunakan format Word (.docx) dengan placeholder yang sesuai untuk data yang akan diisi.</p>
        <p class="mb-2">Format placeholder: <code class="px-2 py-1 bg-gray-100 rounded">${nama_placeholder}</code></p>
        
        <div class="mt-4 mb-2 p-2 bg-yellow-50 border-l-4 border-yellow-400">
            <p class="font-medium">Tips:</p>
            <ul class="list-disc list-inside">
                <li>Pastikan format placeholder tepat, termasuk tanda kurung dan underscore</li>
                <li>Placeholder wajib harus ada dalam template</li>
                <li>UTS dan UAS memiliki placeholder yang berbeda, sesuaikan dengan template yang dipilih</li>
                <li>Anda dapat download contoh template untuk referensi</li>
            </ul>
        </div>

        <!-- Type Selection Tabs -->
        <div class="flex mb-4 border-b">
            <button id="btn-uts" onclick="showPlaceholderType('uts')" 
                    class="px-4 py-2 border-b-2 border-green-500 text-green-700 font-medium">
                UTS (Tengah Semester)
            </button>
            <button id="btn-uas" onclick="showPlaceholderType('uas')"
                    class="px-4 py-2 border-b-2 border-transparent hover:text-green-700 hover:border-green-200">
                UAS (Akhir Semester)
            </button>
        </div>
    </div>

    <!-- UTS Placeholders -->
    <div id="uts-placeholders" class="placeholder-content">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Data Siswa Section - UTS -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-semibold text-gray-700 mb-2">Data Siswa</h4>
                <div class="space-y-2">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nama_siswa}</code>
                            <span class="text-sm">Nama lengkap siswa</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nisn}</code>
                            <span class="text-sm">NISN siswa</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nis}</code>
                            <span class="text-sm">NIS siswa</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${kelas}</code>
                            <span class="text-sm">Kelas siswa</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${tahun_ajaran}</code>
                            <span class="text-sm">Tahun ajaran</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mata Pelajaran Section - UTS -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-semibold text-gray-700 mb-2">Nilai Mata Pelajaran</h4>
                <div class="space-y-2">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nilai_pai}</code>
                            <span class="text-sm">Nilai Pendidikan Agama Islam</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${capaian_pai}</code>
                            <span class="text-sm">Capaian PAI</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nilai_matematika}</code>
                            <span class="text-sm">Nilai Matematika</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nilai_bahasa_indonesia}</code>
                            <span class="text-sm">Nilai Bahasa Indonesia</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${capaian_bahasa_indonesia}</code>
                            <span class="text-sm">Capaian Bahasa Indonesia</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <p>Placeholder mata pelajaran lain: nilai_ppkn, capaian_ppkn, nilai_pjok, capaian_pjok, nilai_seni_musik, dll.</p>
                </div>
            </div>

            <!-- Mulok & Ekstrakurikuler - UTS -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-semibold text-gray-700 mb-2">Muatan Lokal & Ekstrakurikuler</h4>
                <div class="space-y-2">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nama_mulok1}</code>
                            <span class="text-sm">Nama Muatan Lokal 1</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nilai_mulok1}</code>
                            <span class="text-sm">Nilai Muatan Lokal 1</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${ekskul1_nama}</code>
                            <span class="text-sm">Nama Ekstrakurikuler 1</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${ekskul1_keterangan}</code>
                            <span class="text-sm">Keterangan Ekstrakurikuler 1</span>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <p>Tersedia placeholder untuk mulok1-5 dan ekskul1-6 (nama dan keterangan)</p>
                </div>
            </div>

            <!-- Kehadiran & Lainnya - UTS -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-semibold text-gray-700 mb-2">Kehadiran & Lainnya</h4>
                <div class="space-y-2">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${sakit}</code>
                            <span class="text-sm">Jumlah hari sakit</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${izin}</code>
                            <span class="text-sm">Jumlah hari izin</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${tanpa_keterangan}</code>
                            <span class="text-sm">Jumlah hari tanpa keterangan</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${catatan_guru}</code>
                            <span class="text-sm">Catatan guru</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nomor_telepon}</code>
                            <span class="text-sm">Nomor telepon sekolah</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            <p><span class="text-red-500 font-bold">*</span> Placeholder wajib yang harus ada dalam template UTS</p>
            <p>Untuk referensi lengkap, lihat file template contoh di <code class="bg-gray-100 px-1 py-0.5 rounded">RTS template rapor digital.docx</code></p>
        </div>
    </div>

    <!-- UAS Placeholders -->
    <div id="uas-placeholders" class="placeholder-content hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Data Siswa Section - UAS -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-semibold text-gray-700 mb-2">Data Siswa & Sekolah</h4>
                <div class="space-y-2">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nama_siswa}</code>
                            <span class="text-sm">Nama lengkap siswa</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nisn}</code>
                            <span class="text-sm">NISN siswa</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nis}</code>
                            <span class="text-sm">NIS siswa</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${kelas}</code>
                            <span class="text-sm">Kelas siswa</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${tahun_ajaran}</code>
                            <span class="text-sm">Tahun ajaran</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <p>UAS memiliki lebih banyak detail identitas siswa. Lihat template contoh untuk daftar lengkap.</p>
                </div>
            </div>

            <!-- Mata Pelajaran Section - UAS -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-semibold text-gray-700 mb-2">Nilai Mata Pelajaran</h4>
                <div class="space-y-2">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nilai_pai}</code>
                            <span class="text-sm">Nilai Pendidikan Agama Islam</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${capaian_pai}</code>
                            <span class="text-sm">Capaian PAI</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nilai_matematika}</code>
                            <span class="text-sm">Nilai Matematika</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nilai_bahasa_indonesia}</code>
                            <span class="text-sm">Nilai Bahasa Indonesia</span>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <p>UAS menggunakan format nilai yang lebih rinci. Placeholder nilai sama seperti UTS.</p>
                </div>
            </div>

            <!-- Mulok & Ekstrakurikuler - UAS -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-semibold text-gray-700 mb-2">Muatan Lokal & Ekstrakurikuler</h4>
                <div class="space-y-2">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nama_mulok1}</code>
                            <span class="text-sm">Nama Muatan Lokal 1</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${nilai_mulok1}</code>
                            <span class="text-sm">Nilai Muatan Lokal 1</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${ekskul1_nama}</code>
                            <span class="text-sm">Nama Ekstrakurikuler 1</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${ekskul1_keterangan}</code>
                            <span class="text-sm">Keterangan Ekstrakurikuler 1</span>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <p>Tersedia placeholder untuk mulok1-5 dan ekskul1-6 (nama dan keterangan)</p>
                </div>
            </div>

            <!-- Kehadiran & Lainnya - UAS -->
            <div class="bg-white p-4 rounded-lg border">
                <h4 class="font-semibold text-gray-700 mb-2">Kehadiran & Lainnya</h4>
                <div class="space-y-2">
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${sakit}</code>
                            <span class="text-sm">Jumlah hari sakit</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${izin}</code>
                            <span class="text-sm">Jumlah hari izin</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${tanpa_keterangan}</code>
                            <span class="text-sm">Jumlah hari tanpa keterangan</span>
                            <span class="ml-1 text-red-500 text-xs font-bold">*</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center">
                            <code class="text-xs px-1 py-0.5 bg-gray-100 rounded mr-2">${catatan_guru}</code>
                            <span class="text-sm">Catatan guru</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            <p><span class="text-red-500 font-bold">*</span> Placeholder wajib yang harus ada dalam template UAS</p>
            <p>Format UAS lebih kompleks dan memiliki lebih banyak placeholder. Untuk referensi lengkap, lihat file template contoh di <code class="bg-gray-100 px-1 py-0.5 rounded">RAKHIR template rapor digital.docx</code></p>
        </div>
    </div>
</div>

<script>
    function showPlaceholderType(type) {
        // Hide all content
        document.querySelectorAll('.placeholder-content').forEach(el => {
            el.classList.add('hidden');
        });
        
        // Reset all tab buttons
        document.querySelectorAll('#btn-uts, #btn-uas').forEach(el => {
            el.classList.remove('border-green-500', 'text-green-700', 'font-medium');
            el.classList.add('border-transparent', 'hover:text-green-700', 'hover:border-green-200');
        });
        
        // Show selected content and activate tab
        if (type === 'uts') {
            document.getElementById('uts-placeholders').classList.remove('hidden');
            document.getElementById('btn-uts').classList.remove('border-transparent', 'hover:text-green-700', 'hover:border-green-200');
            document.getElementById('btn-uts').classList.add('border-green-500', 'text-green-700', 'font-medium');
        } else {
            document.getElementById('uas-placeholders').classList.remove('hidden');
            document.getElementById('btn-uas').classList.remove('border-transparent', 'hover:text-green-700', 'hover:border-green-200');
            document.getElementById('btn-uas').classList.add('border-green-500', 'text-green-700', 'font-medium');
        }
    }
</script>