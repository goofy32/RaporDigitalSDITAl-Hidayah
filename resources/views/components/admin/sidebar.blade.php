<aside id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
    aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('dashboard') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                    {{ Request::routeIs('dashboard') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/dashboard-icon.png') }}" alt="Dashboard Icon" class="w-5 h-5">
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('profile') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                    {{ Request::routeIs('profile', 'profile.edit') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/profile-icon.png') }}" alt="Profile Icon" class="w-5 h-5">
                    <span class="ms-3">Profile Sekolah</span>
                </a>
            </li>
            <li>
                <a href="{{ route('kelas.index') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                    {{ Request::routeIs('kelas.*') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/class-icon.png') }}" alt="Class Icon" class="w-5 h-5">
                    <span class="ms-3">Kelas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('teacher') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                    {{ Request::routeIs('teacher', 'teacher.*') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/teacher-icon.png') }}" alt="Teacher Icon" class="w-5 h-5">
                    <span class="ms-3">Pengajar</span>
                </a>
            </li>
            <li>
                <a href="{{ route('student') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                    {{ Request::routeIs('student', 'student.*') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/student-icon.png') }}" alt="Student Icon" class="w-5 h-5">
                    <span class="ms-3">Siswa</span>
                </a>
            </li>
            <li>
                <a href="{{ route('subject.index') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                    {{ Request::routeIs('subject', 'subject.*') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/subject-icon.png') }}" alt="Subject Icon" class="w-5 h-5">
                    <span class="ms-3">Pelajaran</span>
                </a>
            </li>
            <li>
                <a href="{{ route('ekstra') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                    {{ Request::routeIs('ekstra') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/extracurricular-icon.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                    <span class="ms-3">Ekstrakurikuler</span>
                </a>
            </li>
            <li>
                <a href="{{ route('achievement') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group 
                    {{ Request::routeIs('achievement') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/achievement-icon.png') }}" alt="Achievement Icon" class="w-5 h-5">
                    <span class="ms-3">Prestasi</span>
                </a>
            </li>
            <li>
                <button type="button"
                    data-collapse-toggle="dropdown-rapor"
                    class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg group dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700
                    {{ Request::routeIs('report_format') ? 'bg-green-100 shadow-md' : '' }}">
                    <img src="{{ asset('images/icons/report-icon.png') }}" alt="Report Icon" class="w-5 h-5">
                    <span class="ml-3">Format Rapot</span>
                    <svg class="w-4 h-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06-.02L10 10.67l3.71-3.48a.75.75 0 111.04 1.08l-4.25 4a.75.75 0 01-1.04 0l-4.25-4a.75.75 0 01-.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul id="dropdown-rapor" class="hidden py-2 space-y-2" data-turbo-permanent>
                    <li>
                        <a href="{{ route('report_format', ['type' => 'UTS']) }}"
                            class="flex items-center w-full p-2 pl-10 text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:hover:bg-gray-700">
                            UTS
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('report_format', ['type' => 'UAS']) }}"
                            class="flex items-center w-full p-2 pl-10 text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:hover:bg-gray-700">
                            UAS
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</aside>