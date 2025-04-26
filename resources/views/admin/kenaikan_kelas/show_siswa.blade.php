@extends('layouts.app')

@section('title', 'Proses Kenaikan Kelas')

@section('content')
<div x-data="{
    selectedSiswa: [],
    selectAll: false,
    toggleSelectAll() {
        this.selectAll = !this.selectAll;
        this.selectedSiswa = this.selectAll ? 
            Array.from(document.querySelectorAll('input[name=\'siswa_ids[]\']')).map(el => el.value) : [];
    }
}" class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Proses {{ $isKelasAkhir ? 'Kelulusan' : 'Kenaikan Kelas' }}</h2>
        <a href="{{ route('admin.kenaikan-kelas.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Kelas {{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}</h3>
        <p class="text-gray-600">Wali Kelas: {{ $kelas->waliKelasName }}</p>
        <p class="text-gray-600">Jumlah Siswa: {{ $siswaList->count() }}</p>
    </div>

    @if($siswaList->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-yellow-800">Tidak ada siswa yang perlu diproses di kelas ini. Semua siswa mungkin sudah dipindahkan atau diluluskan.</p>
    </div>
    @else
    
    @if(!$isKelasAkhir && $kelasTujuan->isEmpty())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800">Tidak ada kelas tujuan yang tersedia di tahun ajaran baru. Pastikan kelas untuk tingkat berikutnya sudah dibuat.</p>
        <a href="{{ route('kelas.create') }}" class="text-blue-600 hover:underline mt-2 inline-block">Buat Kelas Baru</a>
    </div>
    @else
    
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <input id="select-all" type="checkbox" x-model="selectAll" @click="toggleSelectAll" class="h-4 w-4 text-blue-600 focus:ring-blue-500">
            <label for="select-all" class="ml-2 block text-sm text-gray-900">Pilih Semua Siswa</label>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pilih</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NIS</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                        <th class="py-3 px-4 border-b text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jenis Kelamin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($siswaList as $siswa)
                    <tr>
                        <td class="py-3 px-4 border-b">
                            <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" 
                                  x-model="selectedSiswa" class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="py-3 px-4 border-b">{{ $siswa->nis }}</td>
                        <td class="py-3 px-4 border-b">{{ $siswa->nama }}</td>
                        <td class="py-3 px-4 border-b">{{ $siswa->jenis_kelamin }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6" x-show="selectedSiswa.length > 0">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Proses Siswa Terpilih</h3>
        <p class="mb-3">Anda telah memilih <span x-text="selectedSiswa.length" class="font-semibold"></span> siswa.</p>
        
        @if($isKelasAkhir)
        <!-- Form untuk Kelulusan -->
        <form action="{{ route('admin.kenaikan-kelas.process-kelulusan') }}" method="POST" class="space-y-4">
            @csrf
            <template x-for="id in selectedSiswa">
                <input type="hidden" name="siswa_ids[]" :value="id">
            </template>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Status --</option>
                    <option value="lulus">Lulus</option>
                    <option value="pindah">Pindah</option>
                    <option value="dropout">Dropout</option>
                </select>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Proses Kelulusan
                </button>
            </div>
        </form>
        @else
        <!-- Form untuk Kenaikan Kelas -->
        <div class="space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Form Naik Kelas -->
                <form action="{{ route('admin.kenaikan-kelas.process-kenaikan') }}" method="POST" class="flex-1 bg-white p-4 rounded-lg border border-gray-200">
                    @csrf
                    <h4 class="text-md font-semibold text-green-700 mb-3">Naik Kelas</h4>
                    <template x-for="id in selectedSiswa">
                        <input type="hidden" name="siswa_ids[]" :value="id">
                    </template>
                    
                    <div class="mb-4">
                        <label for="kelas_tujuan_id" class="block text-sm font-medium text-gray-700">Kelas Tujuan</label>
                        <select name="kelas_tujuan_id" id="kelas_tujuan_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            @foreach($kelasTujuan as $target)
                            <option value="{{ $target->id }}">Kelas {{ $target->nomor_kelas }} {{ $target->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Proses Naik Kelas
                        </button>
                    </div>
                </form>
                
                <!-- Form Tinggal Kelas -->
                <form action="{{ route('admin.kenaikan-kelas.process-tinggal') }}" method="POST" class="flex-1 bg-white p-4 rounded-lg border border-gray-200">
                    @csrf
                    <h4 class="text-md font-semibold text-yellow-700 mb-3">Tinggal Kelas</h4>
                    <template x-for="id in selectedSiswa">
                        <input type="hidden" name="siswa_ids[]" :value="id">
                    </template>
                    
                    <div class="mb-4">
                        <label for="kelas_tinggal_id" class="block text-sm font-medium text-gray-700">Kelas Tujuan (Tingkat yang Sama)</label>
                        <select name="kelas_tujuan_id" id="kelas_tinggal_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                            <option value="">-- Pilih Kelas Tinggal --</option>
                            @foreach(\App\Models\Kelas::where('tahun_ajaran_id', $tahunAjaranBaru->id)
                                    ->where('nomor_kelas', $kelas->nomor_kelas)
                                    ->orderBy('nama_kelas')
                                    ->get() as $target)
                            <option value="{{ $target->id }}">Kelas {{ $target->nomor_kelas }} {{ $target->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            Proses Tinggal Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    @endif
    @endif
</div>
@endsection