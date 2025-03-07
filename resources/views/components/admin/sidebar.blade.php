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
                <a href="{{ route('report.template.index') }}" 
                data-path="report-template"
                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                    <img src="{{ asset('images/icons/report-icon.png') }}" class="w-5 h-5" alt="Format Rapor" />
                    <span class="flex-1 ml-3 whitespace-nowrap">Format Rapor</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.report.history') }}" 
                data-path="report-history"
                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                    <img src="{{ asset('images/icons/history-icon.png') }}" class="w-5 h-5" alt="History Rapor" />
                    <span class="flex-1 ml-3 whitespace-nowrap">History Rapor</span>
                </a>
            </li>
        </ul>
    </div>
</aside>