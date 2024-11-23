<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
   <div class="h-full px-3 pb-4 overflow-y-auto">
       <ul class="space-y-2 font-medium">
           <!-- Dashboard -->
           <li>
               <a href="{{ route('pengajar.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                   <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                       <path d="M10 2a8 8 0 11-8 8 8 8 0 018-8zm1 5H9v3H6v2h3v3h2v-3h3V8h-3z" />
                   </svg>
                   <span class="ml-3">Dashboard</span>
               </a>
           </li>
           <!-- Data Pembelajaran -->
           <li>
            <a href="{{ route('pengajar.score') }}" 
               class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:group-hover:text-white" 
                     xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 3.5a6.5 6.5 0 11-6.5 6.5A6.507 6.507 0 0110 3.5z" />
                </svg>
                <span class="ml-3">Data Pembelajaran</span>
            </a>
        </li>
           <!-- Data Mata Pelajaran -->
           <li>
               <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                   <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                       <path d="M10 2a8 8 0 11-8 8 8 8 0 018-8zm1 5H9v3H6v2h3v3h2v-3h3V8h-3z" />
                   </svg>
                   <span class="ml-3">Data Mata Pelajaran</span>
               </a>
           </li>
       </ul>
   </div>
</aside>