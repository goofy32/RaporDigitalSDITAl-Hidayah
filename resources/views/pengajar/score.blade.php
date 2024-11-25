@extends('layouts.pengajar.app')

@section('title', 'Data Pembelajaran')

@section('content')

<div class="p-4 bg-white mt-14 shadow-md rounded-lg">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Data Pembelajaran</h2>
        <div class="flex gap-4">
            <input 
                type="text" 
                id="searchInput"
                placeholder="Cari kelas atau mata pelajaran..." 
                class="border border-gray-300 rounded-lg px-4 py-2 w-64"
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
                                    <a href="{{ route('pengajar.input_score', $mapel->id) }}" 
                                       class="text-green-600 hover:text-green-800 flex items-center gap-2">
                                        <i class="fas fa-edit"></i>
                                        <span>Input Nilai</span>
                                    </a>
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