<!-- resources/views/components/wali-kelas/sidebar.blade.php -->
<aside id="logo-sidebar" 
       class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700" 
       aria-label="Sidebar"
       x-data="{ 
           initImages() {
               this.$el.querySelectorAll('img').forEach(img => {
                   if (!Alpine.store('navigation').isImageLoaded(img.id)) {
                       img.style.opacity = '0';
                       if (img.complete) {
                           img.style.opacity = '1';
                           Alpine.store('navigation').markImageLoaded(img.id);
                       } else {
                           img.addEventListener('load', () => {
                               img.style.opacity = '1';
                               Alpine.store('navigation').markImageLoaded(img.id);
                           });
                       }
                   } else {
                       img.style.opacity = '1';
                   }
               });
           }
       }"
       x-init="initImages">
    <div class="h-full px-3 pb-4 overflow-y-auto">
        <ul class="space-y-2 font-medium">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('wali_kelas.dashboard') }}" 
                   data-turbo-action="replace"
                   data-path="dashboard"
                   onclick="return !window.formChanged || confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')"
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <div x-data class="w-5 h-5">
                        <img src="{{ asset('images/icons/dashboard-icon.png') }}" 
                             alt="Dashboard Icon"
                             class="w-full h-full transition-opacity duration-300"
                             :class="Alpine.store('navigation').isImageLoaded('wk-dashboard-icon') ? 'opacity-100' : 'opacity-0'"
                             id="wk-dashboard-icon">
                    </div>
                    <span class="ml-3">Dashboard</span>
                </a>
            </li>

            <!-- Siswa -->
            <li>
                <a href="{{ route('wali_kelas.student.index') }}" 
                   data-turbo-action="replace"
                   data-path="student"
                   onclick="return !window.formChanged || confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')"
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <div x-data class="w-5 h-5">
                        <img src="{{ asset('images/icons/student-icon.png') }}" 
                             alt="Student Icon"
                             class="w-full h-full transition-opacity duration-300"
                             :class="Alpine.store('navigation').isImageLoaded('wk-student-icon') ? 'opacity-100' : 'opacity-0'"
                             id="wk-student-icon">
                    </div>
                    <span class="ml-3">Siswa</span>
                </a>
            </li>

            <!-- Capaian Kompetensi -->
            <li>
                <a href="{{ route('wali_kelas.capaian_kompetensi.index') }}" 
                   data-turbo-action="replace"
                   data-path="capaian-kompetensi"
                   onclick="return !window.formChanged || confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')"
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <div x-data class="w-5 h-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <span class="ml-3">Capaian Kompetensi</span>
                </a>
            </li>

            <!-- Ekstrakurikuler -->
            <li>
                <a href="{{ route('wali_kelas.ekstrakurikuler.index') }}" 
                   data-turbo-action="replace"
                   data-path="ekstrakurikuler"
                   onclick="return !window.formChanged || confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')"
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <div x-data class="w-5 h-5">
                        <img src="{{ asset('images/icons/extracurricular-icon.png') }}" 
                             alt="Extracurricular Icon"
                             class="w-full h-full transition-opacity duration-300"
                             :class="Alpine.store('navigation').isImageLoaded('wk-extracurricular-icon') ? 'opacity-100' : 'opacity-0'"
                             id="wk-extracurricular-icon">
                    </div>
                    <span class="ml-3">Ekstrakurikuler</span>
                </a>
            </li>

            <!-- Absensi -->
            <li>
                <a href="{{ route('wali_kelas.absence.index') }}" 
                   data-turbo-action="replace"
                   data-path="absence"
                   onclick="return !window.formChanged || confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')"
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <div x-data class="w-5 h-5">
                        <img src="{{ asset('images/icons/absence-icon.png') }}" 
                             alt="Absence Icon"
                             class="w-full h-full transition-opacity duration-300"
                             :class="Alpine.store('navigation').isImageLoaded('wk-absence-icon') ? 'opacity-100' : 'opacity-0'"
                             id="wk-absence-icon">
                    </div>
                    <span class="ml-3">Absensi</span>
                </a>
            </li>

            <!-- Rapor -->
            <li>
                <a href="{{ route('wali_kelas.rapor.index') }}" 
                   data-turbo-action="replace"
                   data-path="rapor"
                   onclick="return !window.formChanged || confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')"
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <div x-data class="w-5 h-5">
                        <img src="{{ asset('images/icons/report-icon.png') }}" 
                             alt="Report Icon"
                             class="w-full h-full transition-opacity duration-300"
                             :class="Alpine.store('navigation').isImageLoaded('wk-report-icon') ? 'opacity-100' : 'opacity-0'"
                             id="wk-report-icon">
                    </div>
                    <span class="ml-3">Rapor</span>
                </a>
            </li>
        </ul>
    </div>
</aside>