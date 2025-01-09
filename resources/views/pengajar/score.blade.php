@extends('layouts.pengajar.app')

@section('title', 'Data Pembelajaran')

@section('content')

<div class="p-4 bg-white mt-14 shadow-md rounded-lg">
            <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-green-700 mb-4">Data Pembelajaran</h2>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <input 
                type="text" 
                id="searchInput"
                class="block w-full p-4 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-green-500 focus:border-green-500"
                placeholder="Cari kelas atau mata pelajaran..."
                onkeyup="searchTable()"
            >
        </div>
    </div>

    <!-- Debug information -->
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tabel Data Pembelajaran -->
    <div class="overflow-x-auto">
        <table id="pembelajaranTable" class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">No</th>
                    <th scope="col" class="px-6 py-3">Kelas</th>
                    <th scope="col" class="px-6 py-3">Mata Pelajaran</th>
                    <th scope="col" class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if($kelasData->isEmpty())
                    <tr class="bg-white border-b">
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data pembelajaran yang tersedia
                        </td>
                    </tr>
                @else
                @foreach($kelasData as $index => $kelas)
                    @foreach($kelas->mataPelajarans as $mapel)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $loop->parent->iteration }}</td>
                            <td class="px-6 py-4">{{ $kelas->nama_kelas }}</td>
                            <td class="px-6 py-4">{{ $mapel->nama_pelajaran }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                @if($mapel->lingkupMateris->every(function($lm) { return $lm->tujuanPembelajarans->isNotEmpty(); }))
                                    @if(!$mapel->nilais()->exists())
                                    <a href="{{ route('pengajar.score.input_score', $mapel->id) }}"
                                    class="text-green-600 hover:text-green-800">
                                            <img src="{{ asset('images/icons/edit.png') }}" alt="Input Icon" class="w-5 h-5">
                                        </a>
                                    @else
                                    <a href="{{ route('pengajar.score.preview_score', $mapel->id) }}" 
                                    class="text-blue-600 hover:text-blue-800">
                                            <img src="{{ asset('images/icons/detail.png') }}" alt="View Icon" class="w-5 h-5">
                                        </a>
                                    @endif
                                    @else
                                        <button type="button" 
                                                class="text-yellow-600 hover:text-yellow-800"
                                                onclick="alert('Harap isi Tujuan Pembelajaran untuk mata pelajaran ini terlebih dahulu.')">
                                            <img src="{{ asset('images/icons/warning.png') }}" alt="Warning Icon" class="w-5 h-5">
                                        </button>
                                    @endif

                                        <form action="{{ route('pengajar.subject.destroy', $mapel->id) }}" 
                                            method="POST" 
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <img src="{{ asset('images/icons/delete.png') }}" alt="Delete Icon" class="w-5 h-5">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('pembelajaranTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

// Debug function
function logData(data) {
    console.log('Data:', data);
}
</script>
@endsection