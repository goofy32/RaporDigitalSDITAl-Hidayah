<!-- resources/views/components/admin/topbar.blade.php -->
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

            <!-- User Menu dan Tahun Ajaran Selector -->
            <div class="flex items-center relative space-x-4">
                <!-- Tahun Ajaran Selector - Komponen Baru -->
                @if(isset($tahunAjarans) && $tahunAjarans->count() > 0)
                <div x-data="tahunAjaranSelector" class="relative hidden md:block group">
                    <div class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            {{ isset($activeTahunAjaran) ? $activeTahunAjaran->tahun_ajaran . ' - ' . ($activeTahunAjaran->semester == 1 ? 'Ganjil' : 'Genap') : 'Pilih Tahun Ajaran' }}
                            @if(session('tahun_ajaran_id') && isset($activeTahunAjaran) && session('tahun_ajaran_id') != $activeTahunAjaran->id)
                                <span class="ml-2 px-2 py-1 text-xs font-medium bg-blue-200 text-blue-800 rounded-full animate-pulse">
                                    Tampilan Data Berbeda
                                </span>
                            @endif
                        </span>
                        
                        @if(Auth::guard('web')->check())
                        <!-- Dropdown untuk admin -->
                        <svg @click="toggleDropdown" class="w-4 h-4 ml-2 cursor-pointer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        @endif
                    </div>
                

                    
                    @if(Auth::guard('web')->check())
                    <!-- Dropdown menu - hanya untuk admin -->
                    <div 
                        x-show="isOpen" 
                        @click.away="isOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 z-50 w-64 mt-2 origin-top-right bg-white divide-y divide-gray-100 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                        style="display: none;"
                    >
                        <div class="py-1">
                            <div class="px-3 py-2 text-xs text-gray-500 font-medium">
                                <div class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Tampilan Data Tahun Ajaran</span>
                                </div>
                            </div>
                            @foreach($tahunAjarans as $ta)
                            <button 
                                @click="changeTahunAjaran('{{ $ta->id }}', '{{ $ta->tahun_ajaran }} - {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}', {{ $ta->is_active ? 'true' : 'false' }})" 
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left {{ isset($activeTahunAjaran) && $ta->id === $activeTahunAjaran->id ? 'bg-green-50 text-green-700' : '' }} {{ session('tahun_ajaran_id') == $ta->id ? 'bg-blue-50' : '' }}"
                            >
                                <div class="flex-1">
                                    <span>{{ $ta->tahun_ajaran }} - {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}</span>
                                    @if($ta->is_active)
                                        <span class="ml-2 px-1.5 py-0.5 text-xs bg-green-100 text-green-700 rounded">Aktif</span>
                                    @endif
                                </div>
                                @if(session('tahun_ajaran_id') == $ta->id)
                                <svg class="w-5 h-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Info Pengguna -->
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-900 hidden md:block">
                        @if(Auth::guard('guru')->check())
                            {{ Auth::guard('guru')->user()->nama }}
                        @else
                            {{ Auth::user()->name }}
                        @endif
                    </span>
                    
                    <!-- User Dropdown Button -->
                    <div x-data="{ open: false }">
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
                            class="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
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

                                @if(Auth::guard('web')->check())
                                    <a href="{{ route('admin.audit.index') }}" 
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                    role="menuitem">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            Security Audit
                                        </div>
                                    </a>
                                    
                                    <!-- Menu item khusus admin untuk pengaturan -->
                                    <a href="#" 
                                       @click.prevent="$dispatch('open-settings')"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                       role="menuitem">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                            </svg>
                                            Pengaturan Rapor
                                        </div>
                                    </a>
                                @endif
                                
                                <!-- Tahun Ajaran (untuk mobile) -->
                                @if(isset($tahunAjarans) && $tahunAjarans->count() > 0)
                                    <div class="md:hidden border-t border-gray-100">
                                        <p class="px-4 py-2 text-xs font-semibold text-gray-500">TAHUN AJARAN</p>
                                        @foreach($tahunAjarans as $ta)
                                            <a href="{{ route('tahun.ajaran.set-session', ['id' => $ta->id]) }}" 
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ isset($activeTahunAjaran) && $ta->id === $activeTahunAjaran->id ? 'bg-green-50 text-green-700' : '' }}" 
                                            role="menuitem">
                                                {{ $ta->tahun_ajaran }} - {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}
                                                @if(isset($activeTahunAjaran) && $ta->id === $activeTahunAjaran->id)
                                                    <span class="ml-2 text-green-600">✓</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
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
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tahunAjaranSelector', () => ({
        isOpen: false,
        
        toggleDropdown() {
            this.isOpen = !this.isOpen;
        },

        // Add this new method
        changeTahunAjaran(id, tahunAjaranText, isActive) {
            if (!isActive) {
                const activeTahunAjaran = '{{ isset($activeTahunAjaran) ? $activeTahunAjaran->tahun_ajaran : "" }}';
                
                Swal.fire({
                    title: 'Perhatian!',
                    html: `Anda akan melihat data untuk tahun ajaran <strong>${tahunAjaranText}</strong>, sedangkan tahun ajaran aktif adalah <strong>${activeTahunAjaran}</strong>.<br><br>Data baru tetap akan disimpan di tahun ajaran aktif.`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `/admin/set-tahun-ajaran/${id}`;
                    }
                });
            } else {
                window.location.href = `/admin/set-tahun-ajaran/${id}`;
            }
        }
    }));
});
</script>
@endpush