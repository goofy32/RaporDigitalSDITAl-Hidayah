<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto">
        <ul class="space-y-2 font-medium">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('wali_kelas.dashboard') }}" 
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('wali_kelas.dashboard') ? 'bg-gray-100' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span class="ml-3">Dashboard</span>
                </a>
            </li>

            <!-- Siswa -->
            <li>
                <a href="{{ route('wali_kelas.student.index') }}" 
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('wali_kelas.student.*') ? 'bg-gray-100' : '' }}">
                   <img src="{{ asset('images/icons/student-icon.png') }}" alt="Student Icon" class="w-5 h-5">
                    <span class="ml-3">Siswa</span>
                </a>
            </li>

            <!-- Ekstrakurikuler -->
            <li>
                <a href="{{ route('wali_kelas.ekstrakurikuler.index') }}" 
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('wali_kelas.ekstrakurikuler.*') ? 'bg-gray-100' : '' }}">
                   <img src="{{ asset('images/icons/extracurricular-icon.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                    <span class="ml-3">Ekstrakurikuler</span>
                </a>
            </li>

            <!-- Absensi -->
            <li>
                <a href="{{ route('wali_kelas.absence.index') }}" 
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('wali_kelas.absence.*') ? 'bg-gray-100' : '' }}">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="ml-3">Absensi</span>
                </a>
            </li>

            <!-- Rapor -->
            <li>
                <a href="{{ route('wali_kelas.rapor.index') }}" 
                   class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('wali_kelas.rapor.*') ? 'bg-gray-100' : '' }}">
                   <img src="{{ asset('images/icons/report-icon.png') }}" alt="Report Icon" class="w-5 h-5">
                    <span class="ml-3">Rapor</span>
                </a>
            </li>
        </ul>
    </div>
</aside>