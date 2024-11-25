  <div class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start">
                <button data-drawer-target="sidebar" data-drawer-toggle="sidebar" aria-controls="sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                    </svg>
                </button>
                <a href="#" class="flex ms-2 md:me-24">
                  @if(isset($schoolProfile->logo))
                      <img src="{{ asset('storage/' . $schoolProfile->logo) }}" class="h-8 me-3" alt="Logo Sekolah" />
                  @else
                      <img src="https://flowbite.com/docs/images/logo.svg" class="h-8 me-3" alt="Default Logo" />
                  @endif
                  <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">{{ $schoolProfile->nama_sekolah ?? 'SDIT AL Hidayah' }}</span>
              </a>
            </div>
            <div class="flex items-center">
              <div class="relative ml-3" x-data="{ open: false }">
                  <div>
                      <button type="button" 
                              @click="open = !open" 
                              class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300"
                              id="user-menu-button">
                          <span class="sr-only">Open user menu</span>
                          @if(Auth::guard('web')->check() && Auth::guard('web')->user()->photo)
                              <img class="w-8 h-8 rounded-full" 
                                   src="{{ asset('storage/' . Auth::guard('web')->user()->photo) }}" 
                                   alt="user photo">
                          @elseif(Auth::guard('guru')->check() && Auth::guard('guru')->user()->photo)
                              <img class="w-8 h-8 rounded-full" 
                                   src="{{ asset('storage/' . Auth::guard('guru')->user()->photo) }}" 
                                   alt="user photo">
                          @else
                              <div class="relative w-8 h-8 overflow-hidden bg-gray-100 rounded-full">
                                  <svg class="absolute w-10 h-10 text-gray-400 -left-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                              </div>
                          @endif
                      </button>
                  </div>
                  <div x-show="open" 
                       @click.away="open = false"
                       class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                       role="menu" 
                       aria-orientation="vertical" 
                       aria-labelledby="user-menu-button" 
                       tabindex="-1">
                       @if(Auth::guard('guru')->check())
                       <a href="{{ route('pengajar.profile') }}" 
                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                          role="menuitem">Profile</a>
                   @endif
                      <form method="POST" action="{{ route('logout') }}">
                          @csrf
                          <button type="submit" 
                                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                  role="menuitem">Logout</button>
                      </form>
                  </div>
              </div>
          </div>
        </div>
    </div>
</div>


