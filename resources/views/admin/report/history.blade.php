@extends('layouts.app')

@section('title', 'History Rapor')

@section('content')
<div class="p-4 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">History Cetak Rapor</h2>
    </div>

    <!-- Filter Controls -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div class="flex gap-2">
            <select id="tahun-ajaran-selector" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 p-2.5">
                <option value="">Semua Tahun Ajaran</option>
                @foreach($tahunAjarans as $ta)
                    <option value="{{ $ta->id }}" {{ isset($tahunAjaranId) && $ta->id == $tahunAjaranId ? 'selected' : '' }}>
                        {{ $ta->tahun_ajaran }} - {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}
                    </option>
                @endforeach
            </select>
            <select id="filter-type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 p-2.5">
                <option value="">Semua Tipe</option>
                <option value="UTS">UTS</option>
                <option value="UAS">UAS</option>
            </select>
            
            <select id="filter-kelas" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 p-2.5">
                <option value="">Semua Kelas</option>
                @foreach(\App\Models\Kelas::orderBy('nomor_kelas')->get() as $kelas)
                    <option value="{{ $kelas->id }}">{{ $kelas->full_kelas }}</option>
                @endforeach
            </select>
        </div>
        
        <!-- Search Box -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="search" 
                   id="search-input"
                   class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-green-500 focus:border-green-500" 
                   placeholder="Cari siswa...">
        </div>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto shadow-md rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
    <tr>
        <th class="px-6 py-3">No</th>
        <th class="px-6 py-3">NIS/Nama Siswa</th>
        <th class="px-6 py-3">Kelas</th>
        <th class="px-6 py-3">Tipe Rapor</th>
        <th class="px-6 py-3">Template</th> <!-- Kolom baru -->
        <th class="px-6 py-3">Tahun Ajaran</th>
        <th class="px-6 py-3">Dicetak Oleh</th>
        <th class="px-6 py-3">Waktu Cetak</th>
        <th class="px-6 py-3">Aksi</th>
    </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
            <tr class="bg-white border-b hover:bg-gray-50" 
                data-type="{{ $report->type }}" 
                data-kelas="{{ $report->kelas_id }}"
                data-tahun-ajaran="{{ $report->tahun_ajaran_id }}"
                data-search="{{ $report->siswa->nama }} {{ $report->siswa->nis }}">
                <td class="px-6 py-4">{{ $reports->firstItem() + $index }}</td>
                <td class="px-6 py-4 font-medium text-gray-900">
                    {{ $report->siswa->nis }} - {{ $report->siswa->nama }}
                </td>
                <td class="px-6 py-4">{{ $report->kelas->full_kelas }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium {{ $report->type === 'UTS' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }} rounded-full">
                        {{ $report->type }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($report->template && $report->template->kelas_id)
                        <span class="text-xs">{{ $report->template->kelas->full_kelas }}</span>
                    @else
                        <span class="text-xs text-gray-500">Global</span>
                    @endif
                </td>
                <td class="px-6 py-4">{{ $report->tahun_ajaran }}</td>
                <td class="px-6 py-4">{{ $report->generator->nama }}</td>
                <td class="px-6 py-4">{{ $report->created_at->format('d M Y H:i') }}</td>
                <td class="px-6 py-4">
                    <a href="{{ route('admin.report.history.download', $report->id) }}" 
                        class="text-green-600 hover:text-green-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center py-6">
                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 mb-2">Belum ada history cetak rapor</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $reports->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tahunAjaranSelector = document.getElementById('tahun-ajaran-selector');
        tahunAjaranSelector.addEventListener('change', function() {
            const selectedTahunAjaran = this.value;
            
            // If specific academic year is selected, only show those reports
            if (selectedTahunAjaran) {
                window.location.href = "{{ route('admin.report.history') }}?tahun_ajaran_id=" + selectedTahunAjaran;
            } else {
                window.location.href = "{{ route('admin.report.history') }}";
            }
        });
        // Filter berdasarkan tipe rapor
        const filterType = document.getElementById('filter-type');
        filterType.addEventListener('change', applyFilters);
        
        // Filter berdasarkan kelas
        const filterKelas = document.getElementById('filter-kelas');
        filterKelas.addEventListener('change', applyFilters);
        
        // Filter berdasarkan pencarian
        const searchInput = document.getElementById('search-input');
        searchInput.addEventListener('input', applyFilters);
        
        function applyFilters() {
            const typeFilter = filterType.value;
            const kelasFilter = filterKelas.value;
            const searchFilter = searchInput.value.toLowerCase();
            
            document.querySelectorAll('tbody tr').forEach(row => {
                const rowType = row.getAttribute('data-type');
                const rowKelas = row.getAttribute('data-kelas');
                const rowSearchText = row.getAttribute('data-search').toLowerCase();
                
                // Cek apakah baris memenuhi semua filter
                const matchesType = !typeFilter || rowType === typeFilter;
                const matchesKelas = !kelasFilter || rowKelas === kelasFilter;
                const matchesSearch = !searchFilter || rowSearchText.includes(searchFilter);
                
                // Tampilkan/sembunyikan baris berdasarkan hasil filter
                row.style.display = matchesType && matchesKelas && matchesSearch ? '' : 'none';
            });
        }
    });
</script>
@endpush
@endsection