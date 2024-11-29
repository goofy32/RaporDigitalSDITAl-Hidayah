<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
   <div class="h-full px-3 pb-4 overflow-y-auto">
       <ul class="space-y-2 font-medium">
           <!-- Dashboard -->
           <li>
               <a href="{{ route('pengajar.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                    <img src="{{ asset('images/icons/dashboard-icon.png') }}" alt="Dashboard Icon" class="w-5 h-5">
                   <span class="ml-3">Dashboard</span>
               </a>
           </li>
           <!-- Data Pembelajaran -->
           <li>
            <a href="{{ route('pengajar.score') }}" 
               class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                    <img src="{{ asset('images/icons/score.png') }}" alt="Dashboard Icon" class="w-5 h-5">
                <span class="ml-3">Data Pembelajaran</span>
            </a>
        </li>
           <!-- Data Mata Pelajaran -->
           <li>
            <a href="{{ route('pengajar.subject.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                <img src="{{ asset('images/icons/subject-icon.png') }}" alt="Dashboard Icon" class="w-5 h-5">
                <span class="ml-3">Data Mata Pelajaran</span>
            </a>
        </li>
       </ul>
   </div>
</aside>