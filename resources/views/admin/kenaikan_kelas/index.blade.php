@extends('layouts.app')

@section('title', 'Kenaikan Kelas dan Kelulusan')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Kenaikan Kelas dan Kelulusan</h2>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
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

    @if(session('warning'))
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
        <p>{{ session('warning') }}</p>
    </div>
    @endif

    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Informasi Tahun Ajaran</h3>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex-1">
                <h4 class="font-medium text-green-800">Tahun Ajaran Aktif</h4>
                <p class="text-lg font-semibold">{{ $tahunAjaranAktif->tahun_ajaran }}</p>
                <p>Semester {{ $tahunAjaranAktif->semester }} ({{ $tahunAjaranAktif->semester == 1 ? 'Ganjil' : 'Genap' }})</p>
            </div>
            
            @if(isset($tahunAjaranBaru))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex-1">
                <h4 class="font-medium text-green-800">Tahun Ajaran Tujuan</h4>
                <p class="text-lg font-semibold">{{ $tahunAjaranBaru->tahun_ajaran }}</p>
                <p>Semester {{ $tahunAjaranBaru->semester }} ({{ $tahunAjaranBaru->semester == 1 ? 'Ganjil' : 'Genap' }})</p>
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex-1">
                <h4 class="font-medium text-yellow-800">Tahun Ajaran Tujuan</h4>
                <p class="text-gray-700">Belum ada tahun ajaran baru. Silakan buat tahun ajaran baru terlebih dahulu.</p>
                <a href="{{ route('tahun.ajaran.create') }}" class="text-green-600 hover:underline mt-2 inline-block">Buat Tahun Ajaran Baru</a>
            </div>
            @endif
        </div>
    </div>

    @if(isset($tahunAjaranBaru) && $kelasBaru->isNotEmpty())
    <div class="mt-6 bg-green-50 p-4 rounded-lg border border-green-200">
        <h3 class="text-lg font-semibold text-green-800 mb-2">Proses Kenaikan Kelas Massal</h3>
        <p class="text-green-700 mb-4">Proses ini akan memindahkan semua siswa dari tahun ajaran {{ $tahunAjaranAktif->tahun_ajaran }} ke kelas dengan tingkat yang lebih tinggi di tahun ajaran {{ $tahunAjaranBaru->tahun_ajaran }}.</p>
        
        <div class="flex items-center">
            <button type="button" 
                    @click="$dispatch('open-confirm-modal')"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                Proses Kenaikan Kelas Otomatis
            </button>
            <span class="ml-2 text-sm text-green-600">*Siswa kelas akhir (Kelas 6) akan ditandai lulus</span>
        </div>
        
        <!-- Modal Konfirmasi -->
        <div x-data="{ open: false }" 
            @open-confirm-modal.window="open = true"
            x-show="open" 
            x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-bold mb-4">Konfirmasi Kenaikan Kelas Massal</h3>
                <p class="mb-4">Anda akan memproses kenaikan kelas untuk seluruh siswa. Proses ini akan:</p>
                <ul class="list-disc pl-5 mb-4 text-sm">
                    <li>Memindahkan siswa kelas 1-5 ke kelas yang lebih tinggi</li>
                    <li>Menandai siswa kelas 6 sebagai lulus</li>
                    <li>Menyesuaikan penempatan kelas berdasarkan kapasitas</li>
                </ul>
                <p class="mb-4 text-yellow-600 font-medium">Apakah Anda yakin ingin melanjutkan?</p>
                <div class="flex justify-end">
                    <button @click="open = false" class="px-3 py-1 bg-gray-200 text-gray-800 rounded-md mr-2">Batal</button>
                    <form action="{{ route('admin.kenaikan-kelas.process-mass') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded-md">Proses Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($tahunAjaranBaru))
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Pilih Kelas</h3>
        <p class="text-gray-600 mb-4">Pilih kelas yang akan diproses untuk kenaikan kelas atau kelulusan.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($kelasAktif as $kelas)
            <a href="{{ route('admin.kenaikan-kelas.show-siswa', $kelas->id) }}" 
               class="block p-4 bg-white border rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out">
                <h4 class="font-medium text-lg">Kelas {{ $kelas->nomor_kelas }} {{ $kelas->nama_kelas }}</h4>
                <p class="text-gray-600">{{ $kelas->siswas->where('status', 'aktif')->count() }} Siswa</p>
                <p class="text-gray-500 text-sm">Wali Kelas: {{ $kelas->waliKelasName }}</p>
                <div class="mt-2">
                    <span class="inline-block px-2 py-1 text-xs {{ $kelas->nomor_kelas == 6 ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }} rounded-full">
                        {{ $kelas->nomor_kelas == 6 ? 'Proses Kelulusan' : 'Proses Kenaikan Kelas' }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Petunjuk Proses Kenaikan Kelas dan Kelulusan</h3>
        <ol class="list-decimal pl-5 space-y-2">
            <li>Pastikan tahun ajaran baru sudah dibuat dan ada kelas tujuan di tahun ajaran baru.</li>
            <li>Pilih kelas yang akan diproses dari daftar kelas di atas.</li>
            <li>Pada halaman detail kelas, Anda dapat memilih siswa dan menentukan kenaikan kelas atau kelulusan.</li>
            <li>Siswa yang sudah dipindahkan ke kelas di tahun ajaran baru tidak akan muncul lagi dalam daftar.</li>
        </ol>
    </div>
</div>
@endsection
