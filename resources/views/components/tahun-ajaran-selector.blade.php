<div x-data="tahunAjaranSelector" class="relative">
    <button @click="toggleDropdown" class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
        <span>{{ $activeTahunAjaran ? $activeTahunAjaran->tahun_ajaran . ' - ' . ($activeTahunAjaran->semester == 1 ? 'Ganjil' : 'Genap') : 'Pilih Tahun Ajaran' }}</span>
        <svg class="w-5 h-5 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>
    
    <div 
        x-show="isOpen" 
        @click.away="isOpen = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-10 w-56 mt-2 origin-top-right bg-white divide-y divide-gray-100 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
    >
        <div class="py-1">
            @foreach($tahunAjarans as $ta)
            <a 
                href="{{ route('tahun.ajaran.set-session', ['id' => $ta->id]) }}" 
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $ta->id === $activeTahunAjaran->id ? 'bg-green-50 text-green-700' : '' }}"
            >
                {{ $ta->tahun_ajaran }} - {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}
                @if($ta->id === $activeTahunAjaran->id)
                <span class="float-right text-green-600">✓</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</div>