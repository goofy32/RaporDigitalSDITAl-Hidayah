<div class="fixed top-0 z-50 w-full bg-white border-b border-gray-200"
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
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <!-- Logo dan Toggle Sidebar -->
            <div class="flex items-center justify-start">
                <button data-drawer-target="logo-sidebar" 
                        data-drawer-toggle="logo-sidebar" 
                        aria-controls="logo-sidebar" 
                        type="button" 
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                    </svg>
                </button>
                <div class="flex ms-2 md:me-24">
                    @if(isset($schoolProfile->logo))
                        <img src="{{ asset('storage/' . $schoolProfile->logo) }}" class="h-11 me-3" alt="Logo Sekolah" />
                    @else
                        <div x-data class="h-8">
                            <img src="https://flowbite.com/docs/images/logo.svg" 
                                 class="h-full me-3 transition-opacity duration-300"
                                 :class="Alpine.store('navigation').isImageLoaded('default-logo') ? 'opacity-100' : 'opacity-0'"
                                 id="default-logo">
                        </div>
                    @endif
                    <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">
                    {{ $schoolProfile->nama_sekolah ?? 'SDIT AL Hidayah' }}
                    </span>
                </div>
            </div>

            <!-- User Menu -->
            <div class="flex items-center relative" x-data="{ open: false }">
                <div class="flex items-center space-x-8">
                    <span class="text-sm font-medium text-gray-900">
                        @if(Auth::guard('guru')->check())
                            {{ Auth::guard('guru')->user()->nama }}
                        @else
                            {{ Auth::user()->name }}
                        @endif
                    </span>
                    
                    <button @click="open = !open" 
                            @click.away="open = false"
                            class="flex items-center text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300">
                        <span class="sr-only">Open user menu</span>
                        @if(Auth::guard('guru')->check() && Auth::guard('guru')->user()->photo)
                            <div x-data class="w-8 h-8">
                                <img class="w-full h-full rounded-full transition-opacity duration-300"
                                     src="{{ asset('storage/' . Auth::guard('guru')->user()->photo) }}" 
                                     alt="user photo"
                                     :class="Alpine.store('navigation').isImageLoaded('user-photo') ? 'opacity-100' : 'opacity-0'"
                                     id="user-photo">
                            </div>
                        @else
                            <div class="relative w-8 h-8 overflow-hidden bg-gray-100 rounded-full"
                                 id="default-user-photo">
                                <svg class="absolute w-10 h-10 text-gray-400 -left-1" 
                                     fill="currentColor" 
                                     viewBox="0 0 20 20" 
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" 
                                          d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" 
                                          clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @endif
                    </button>

                    <!-- Dropdown menu -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-16 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                         style="display: none;">
                        <!-- User Info -->
                        <div class="px-4 py-3">
                            @if(Auth::guard('guru')->check())
                                <p class="text-sm font-medium text-gray-900">{{ Auth::guard('guru')->user()->nama }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ Auth::guard('guru')->user()->email }}</p>
                            @else
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            @endif
                        </div>
                        <!-- Menu Items -->
                        <div class="border-t border-gray-100">
                            @if(Auth::guard('guru')->check())
                                @if(session('selected_role') === 'wali_kelas')
                                    <a href="{{ route('wali_kelas.profile') }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                       role="menuitem">Profile</a>
                                @else
                                    <a href="{{ route('pengajar.profile') }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                       role="menuitem">Profile</a>
                                @endif
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                                @csrf
                                <button type="submit" 
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                        role="menuitem">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>