@extends('layouts.wali_kelas.app')

@section('title', 'Data Absensi')

@section('content')
<div>
    <div class="p-4 bg-white rounded-lg shadow-sm">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Data Absensi</h2>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('wali_kelas.absence.create') }}" 
                    class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                    </svg>
                    Tambah Absensi
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="w-full md:w-1/4">
                <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select id="month" name="month" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    <option value="">Pilih Bulan</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-1/4">
                <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select id="year" name="year" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    @foreach(range(date('Y')-1, date('Y')+1) as $year)
                        <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">NIS</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Keterangan</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{ $absence->student->nis }}</td>
                        <td class="px-6 py-4">{{ $absence->student->nama }}</td>
                        <td class="px-6 py-4">{{ $absence->tanggal->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $absence->status === 'Hadir' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $absence->status === 'Izin' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $absence->status === 'Sakit' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $absence->status === 'Alpha' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $absence->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $absence->keterangan }}</td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('wali_kelas.absence.edit', $absence->id) }}" 
                                    class="text-yellow-600 hover:text-yellow-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('wali_kelas.absence.destroy', $absence->id) }}" 
                                    method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center">Tidak ada data absensi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $absences->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle filter changes
    document.querySelectorAll('select[name="month"], select[name="year"]').forEach(select => {
        select.addEventListener('change', () => {
            const month = document.querySelector('select[name="month"]').value;
            const year = document.querySelector('select[name="year"]').value;
            window.location.href = `{{ route('wali_kelas.absence.index') }}?month=${month}&year=${year}`;
        });
    });
</script>
@endpush
@endsection