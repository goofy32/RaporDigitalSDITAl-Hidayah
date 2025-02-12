<aside id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
    aria-label="Sidebar"
    data-turbo-permanent>
    <div x-data="{ openDropdown: $store.sidebar.dropdownState.formatRapor }" 
     x-init="$store.sidebar.initDropdown('formatRapor')"
     class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('admin.dashboard') }}"
                    data-turbo-frame="main"
                    data-path="dashboard"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <img src="{{ asset('images/icons/dashboard-icon.png') }}" alt="Dashboard Icon" class="w-5 h-5">
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('profile') }}"
                    data-turbo-frame="main"
                    data-path="profile"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <img src="{{ asset('images/icons/profile-icon.png') }}" alt="Profile Icon" class="w-5 h-5">
                    <span class="ms-3">Profile Sekolah</span>
                </a>
            </li>
            <li>
                <a href="{{ route('kelas.index') }}"
                    data-turbo-frame="main"
                    data-path="kelas"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <img src="{{ asset('images/icons/class-icon.png') }}" alt="Class Icon" class="w-5 h-5">
                    <span class="ms-3">Kelas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('teacher') }}"
                    data-turbo-frame="main"
                    data-path="teacher"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <img src="{{ asset('images/icons/teacher-icon.png') }}" alt="Teacher Icon" class="w-5 h-5">
                    <span class="ms-3">Pengajar</span>
                </a>
            </li>
            <li>
                <a href="{{ route('student') }}"
                    data-turbo-frame="main"
                    data-path="student"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <img src="{{ asset('images/icons/student-icon.png') }}" alt="Student Icon" class="w-5 h-5">
                    <span class="ms-3">Siswa</span>
                </a>
            </li>
            <li>
                <a href="{{ route('subject.index') }}"
                    data-turbo-frame="main"
                    data-path="subject"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <img src="{{ asset('images/icons/subject-icon.png') }}" alt="Subject Icon" class="w-5 h-5">
                    <span class="ms-3">Pelajaran</span>
                </a>
            </li>
            <li>
                <a href="{{ route('ekstra.index') }}"
                    data-turbo-frame="main"
                    data-path="ekstra"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <img src="{{ asset('images/icons/extracurricular-icon.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                    <span class="ms-3">Ekstrakurikuler</span>
                </a>
            </li>
            <li>
                <a href="{{ route('achievement.index') }}"
                    data-turbo-frame="main"
                    data-path="achievement"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                    <img src="{{ asset('images/icons/achievement-icon.png') }}" alt="Achievement Icon" class="w-5 h-5">
                    <span class="ms-3">Prestasi</span>
                </a>
            </li>
            <li>
                <button type="button"
                    @click="$store.sidebar.toggleDropdown('formatRapor')"
                    class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100">
                    <img src="{{ asset('images/icons/report-icon.png') }}" 
                        class="w-5 h-5"
                        loading="lazy">
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Format Rapor</span>
                    <svg class="w-3 h-3 transition-transform" 
                        :class="{'rotate-180': $store.sidebar.dropdownState.formatRapor }" 
                        aria-hidden="true">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <ul x-show="$store.sidebar.dropdownState.formatRapor"
                    x-cloak
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="py-2 space-y-2">
                        <li>
                            <a href="{{ route('report.template.index', 'UTS') }}"
                            class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100">
                                UTS
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('report.template.index', 'UAS') }}"
                            class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100">
                                UAS
                            </a>
                        </li>
                    </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>