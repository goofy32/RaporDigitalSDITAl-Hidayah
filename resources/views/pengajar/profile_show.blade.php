@extends('layouts.pengajar.app')

@section('title', 'Profile Pengajar')

@section('content')
<div class="p-4 bg-white mt-14">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">Profile Pengajar</h2>
    </div>

    <!-- Detail Profile -->
    <div class="flex space-x-8">
        <!-- Foto Profile -->
        <div class="flex items-start justify-center w-48 h-full bg-gray-200 rounded-lg shadow-md">
            @if(Auth::guard('guru')->user()->photo)
                <img src="{{ asset('storage/' . Auth::guard('guru')->user()->photo) }}" 
                     alt="Foto Profile" 
                     class="w-full h-auto object-cover rounded-lg">
            @else
                <div class="flex items-center justify-center w-full h-full bg-gray-200">
                    <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </div>
            @endif
        </div>

        <!-- Informasi Detail -->
        <div class="w-full">
            <table class="w-full text-sm text-left text-gray-500">
                <tbody>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">NIP</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->nuptk ?? 'Belum Diisi' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">Nama</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->nama ?? 'Belum Diisi' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">Jenis Kelamin</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->jenis_kelamin ?? 'Belum Diisi' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">Tanggal Lahir</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->tanggal_lahir ?? 'Belum Diisi' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">No Handphone</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->no_handphone ?? 'Belum Diisi' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">Email</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->email ?? 'Belum Diisi' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">Alamat</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->alamat ?? 'Belum Diisi' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">Jabatan</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->jabatan ?? 'Belum Diisi' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">Kelas Mengajar</th>
                        <td class="px-4 py-2">
                            {{ Auth::guard('guru')->user()->kelasPengajar->nomor_kelas ?? '-' }} - 
                            {{ Auth::guard('guru')->user()->kelasPengajar->nama_kelas ?? 'Belum Diisi' }}
                        </td>
                    </tr>
                    <tr class="border-b">
                        <th class="px-4 py-2 font-medium text-gray-900">Username</th>
                        <td class="px-4 py-2">{{ Auth::guard('guru')->user()->username ?? 'Belum Diisi' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection