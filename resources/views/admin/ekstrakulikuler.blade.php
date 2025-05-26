<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Data Ekstrakulikuler</title>
</head>

<body>

    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Data Ekstrakulikuler</h2>
            </div>

            <!-- Tombol Tambah Data -->
            <div class="flex justify-start mb-4">
                <a href="{{ route('ekstra.create') }}" class="flex items-center justify-center text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                    <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                    </svg>
                    Tambah Data
                </a>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <form action="{{ route('ekstra.index') }}" method="GET" class="w-full" data-turbo="false">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2"
                        placeholder="Cari nama ekstrakurikuler atau pembina...">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

            <!-- Tabel Data Siswa -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">NO</th>
                            <th scope="col" class="px-6 py-3">Nama Ekstrakulikuler</th>
                            <th scope="col" class="px-6 py-3">Pembina</th>
                            <th scope="col" class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Di dalam tbody tabel -->
                        @forelse($ekstrakurikulers as $index => $ekstra)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">{{ $ekstra->nama_ekstrakurikuler }}</td>
                            <td class="px-6 py-4">{{ $ekstra->pembina }}</td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('ekstra.edit', $ekstra->id) }}" class="text-green-600 hover:text-green-800 transition-colors duration-200" title="Ubah Data">
                                        <img src="{{ asset('images/icons/edit.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                                    </a>
                                    <form action="{{ route('ekstra.destroy', $ekstra->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors duration-200" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" title="Hapus Data">
                                                <img src="{{ asset('images/icons/delete.png') }}" alt="Extracurricular Icon" class="w-5 h-5">
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="bg-white border-b">
                            <td colspan="4" class="px-6 py-4 text-center">Tidak ada data ekstrakurikuler</td>
                        </tr>
                        @endforelse
                        
                    </tbody>
                </table>
            </div>
            <div>
                {{ $ekstrakurikulers->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

</body>

</html>
