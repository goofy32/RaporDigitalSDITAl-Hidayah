@extends('layouts.pengajar.app')

@section('title', 'Set KKM dan Bobot Nilai')

@section('content')
<div class="p-4 bg-white mt-14 rounded-lg">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-green-700 mb-2">Set KKM dan Bobot Nilai</h2>
        <p class="text-gray-600">{{ $mataPelajaran->nama_pelajaran }} - Kelas {{ $mataPelajaran->kelas->nomor_kelas }} {{ $mataPelajaran->kelas->nama_kelas }}</p>
    </div>

    <form action="{{ route('pengajar.kkm.update', $mataPelajaran->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-blue-100 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-blue-800 mb-2">Informasi Bobot Nilai Rapor</h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-blue-300 bg-white">
                    <thead class="bg-blue-200">
                        <tr>
                            <th class="border border-blue-300 px-4 py-2">Kategori Penilaian</th>
                            <th class="border border-blue-300 px-4 py-2">Singkatan</th>
                            <th class="border border-blue-300 px-4 py-2">Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-blue-300 px-4 py-2">SUMATIF TUJUAN PEMBELAJARAN</td>
                            <td class="border border-blue-300 px-4 py-2 text-center">S. TP</td>
                            <td class="border border-blue-300 px-4 py-2 text-center">
                                <input type="number" name="bobot_tp" id="bobot_tp" step="0.01" min="0" max="10" 
                                    value="{{ old('bobot_tp', $kkmSetting->bobot_tp) }}" 
                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-center">
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-blue-300 px-4 py-2">SUMATIF LINGKUP MATERI</td>
                            <td class="border border-blue-300 px-4 py-2 text-center">S. LM</td>
                            <td class="border border-blue-300 px-4 py-2 text-center">
                                <input type="number" name="bobot_lm" id="bobot_lm" step="0.01" min="0" max="10" 
                                    value="{{ old('bobot_lm', $kkmSetting->bobot_lm) }}" 
                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-center">
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-blue-300 px-4 py-2">SUMATIF AKHIR SEMESTER</td>
                            <td class="border border-blue-300 px-4 py-2 text-center">S. AS</td>
                            <td class="border border-blue-300 px-4 py-2 text-center">
                                <input type="number" name="bobot_as" id="bobot_as" step="0.01" min="0" max="10" 
                                    value="{{ old('bobot_as', $kkmSetting->bobot_as) }}" 
                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-center">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm text-yellow-800">
                    NA RAPOR = ({{ $kkmSetting->bobot_tp }}*S. TP + {{ $kkmSetting->bobot_lm }}*S. LM + {{ $kkmSetting->bobot_as }}*S. AS)/{{ $kkmSetting->bobot_tp + $kkmSetting->bobot_lm + $kkmSetting->bobot_as }}
                </p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nilai_kkm" class="block text-sm font-medium text-gray-700">Nilai KKM</label>
                <div class="mt-1">
                    <input type="number" name="nilai_kkm" id="nilai_kkm" step="0.01" min="0" max="100" 
                        value="{{ old('nilai_kkm', $kkmSetting->nilai_kkm) }}" 
                        class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
                <p class="mt-1 text-sm text-gray-500">Nilai minimum yang harus dicapai siswa</p>
                
                @error('nilai_kkm')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan (opsional)</label>
            <div class="mt-1">
                <textarea name="keterangan" id="keterangan" rows="3" 
                    class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('keterangan', $kkmSetting->keterangan) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('pengajar.subject.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bobotTpInput = document.getElementById('bobot_tp');
    const bobotLmInput = document.getElementById('bobot_lm');
    const bobotAsInput = document.getElementById('bobot_as');
    
    function updateFormula() {
        const bobotTp = parseFloat(bobotTpInput.value) || 0;
        const bobotLm = parseFloat(bobotLmInput.value) || 0;
        const bobotAs = parseFloat(bobotAsInput.value) || 0;
        const totalBobot = bobotTp + bobotLm + bobotAs;
        
        const formula = document.querySelector('.text-yellow-800');
        if (formula) {
            formula.textContent = `NA RAPOR = (${bobotTp}*S. TP + ${bobotLm}*S. LM + ${bobotAs}*S. AS)/${totalBobot > 0 ? totalBobot : 1}`;
        }
    }
    
    bobotTpInput.addEventListener('input', updateFormula);
    bobotLmInput.addEventListener('input', updateFormula);
    bobotAsInput.addEventListener('input', updateFormula);
});
</script>
@endpush