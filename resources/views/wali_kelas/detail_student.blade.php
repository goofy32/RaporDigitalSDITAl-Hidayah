@extends('layouts.wali_kelas.app')

@section('title', 'Detail Data Siswa')

@section('content')
<div class="p-4 bg-white rounded-lg shadow-sm  mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Detail Data Siswa</h2>
        <div class="flex gap-2">
            <a href="{{ route('wali_kelas.student.edit', 1) }}" 
                class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800 focus:ring-4 focus:ring-green-300">
                Edit
            </a>
            <button onclick="window.history.back()" 
                class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 focus:ring-4 focus:ring-gray-300">
                Kembali
            </button>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-8">
        <!-- Photo Section -->
        <div class="w-full md:w-1/4">
            <div class="bg-gray-100 rounded-lg p-4">
                <div class="flex items-center justify-center w-full h-48 bg-gray-200 rounded-lg">
                    <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                </div>
                <h3 class="text-center mt-4 font-semibold text-lg">Ahmad Rifai</h3>
            </div>
        </div>

        <!-- Details Section -->
        <div class="w-full md:w-3/4">
            <div class="grid grid-cols-1 gap-4">
                <div class="border rounded-lg overflow-hidden">
                    <table class="w-full">
                        <tbody>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50 w-1/3">NISN</th>
                                <td class="px-4 py-2">1234567890</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Nama</th>
                                <td class="px-4 py-2">Ahmad Rifai</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Kelas</th>
                                <td class="px-4 py-2">7A</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Tanggal Lahir</th>
                                <td class="px-4 py-2">15 Mei 2010</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Jenis Kelamin</th>
                                <td class="px-4 py-2">Laki-laki</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Agama</th>
                                <td class="px-4 py-2">Islam</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Alamat</th>
                                <td class="px-4 py-2">Jl. Pendidikan No. 123</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Nama Ayah</th>
                                <td class="px-4 py-2">Budi Santoso</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Pekerjaan Ayah</th>
                                <td class="px-4 py-2">Wiraswasta</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Nama Ibu</th>
                                <td class="px-4 py-2">Siti Aminah</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Pekerjaan Ibu</th>
                                <td class="px-4 py-2">Ibu Rumah Tangga</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Alamat Orang Tua</th>
                                <td class="px-4 py-2">Jl. Pendidikan No. 123</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Wali Siswa</th>
                                <td class="px-4 py-2">-</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Pekerjaan Wali</th>
                                <td class="px-4 py-2">-</td>
                            </tr>
                            <tr class="border-b">
                                <th class="text-left px-4 py-2 bg-gray-50">Alamat Wali</th>
                                <td class="px-4 py-2">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection