<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Data Siswa</title>
</head>

<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Data Siswa</h2>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-4">
                    <a href="{{ route('student.create') }}" class="flex items-center text-white bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg">
                        Tambah Data
                    </a>
                    <button id="uploadButton" data-turbo-permanent class="flex items-center text-white bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-lg">
                        Upload Excel
                    </button>
                    <a href="{{ route('student.template') }}"
                    class="flex items-center justify-center text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2">
                     <svg class="h-3.5 w-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                          xmlns="http://www.w3.org/2000/svg">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                               d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                     </svg>
                     Download Template
                 </a>
                </div>
            </div>

            <!-- Tabel Data Siswa -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3">No</th>
                            <th class="px-6 py-3">NIS</th>
                            <th class="px-6 py-3">NISN</th>
                            <th class="px-6 py-3">Nama Siswa</th>
                            <th class="px-6 py-3">Kelas</th>
                            <th class="px-6 py-3">Tanggal Lahir</th>
                            <th class="px-6 py-3">Jenis Kelamin</th>
                            <th class="px-6 py-3">Agama</th>
                            <th class="px-6 py-3">Alamat</th>
                            <th class="px-6 py-3">Nama Ayah</th>
                            <th class="px-6 py-3">Nama Ibu</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                            <th class="px-6 py-3">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $index => $student)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $students->firstItem() + $index }}</td>
                            <td class="px-6 py-4">{{ $student->nis }}</td>
                            <td class="px-6 py-4">{{ $student->nisn }}</td>
                            <td class="px-6 py-4">{{ $student->nama }}</td>
                            <td class="px-6 py-4">{{ $student->kelas->nama_kelas }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($student->tanggal_lahir)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">{{ $student->jenis_kelamin }}</td>
                            <td class="px-6 py-4">{{ $student->agama }}</td>
                            <td class="px-6 py-4">{{ $student->alamat }}</td>
                            <td class="px-6 py-4">{{ $student->nama_ayah }}</td>
                            <td class="px-6 py-4">{{ $student->nama_ibu }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('student.show', $student->id) }}" class="text-blue-600 hover:text-blue-900">Lihat</a>
                                    <a href="{{ route('student.edit', $student->id) }}" class="text-green-600 hover:text-green-900">Edit</a>
                                    <form action="{{ route('student.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <img src="{{ asset('storage/photos/'.$student->photo) }}" alt="Foto {{ $student->nama }}" class="w-10 h-10 rounded-full object-cover">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Upload -->

    
    <div id="uploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Upload Data Siswa</h3>
                <form action="{{ route('student.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <div class="mt-2">
                        <input type="file" name="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" accept=".xlsx,.xls">
                        <p class="mt-1 text-sm text-gray-500">File Excel (.xlsx, .xls)</p>
                    </div>
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="flex justify-between mt-4">
                        <button type="button" id="closeModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Tutup</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        @if(is_array(session('error')))
            <ul>
                @foreach(session('error') as $errorMessage)
                    <li>{{ $errorMessage }}</li>
                @endforeach
            </ul>
        @else
            <span class="block sm:inline">{{ session('error') }}</span>
        @endif
    </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <script>
    document.addEventListener('turbo:load', function () {
        const uploadButton = document.querySelector('#uploadButton');
        const uploadModal = document.querySelector('#uploadModal');
        const closeModal = document.querySelector('#closeModal');

        // Pastikan elemen ditemukan sebelum menambahkan event listener
        if (uploadButton) {
            uploadButton.addEventListener('click', () => {
                if (uploadModal) {
                    uploadModal.classList.remove('hidden');
                }
            });
        }

        if (closeModal) {
            closeModal.addEventListener('click', () => {
                if (uploadModal) {
                    uploadModal.classList.add('hidden');
                }
            });
        }

        if (uploadModal) {
            uploadModal.addEventListener('click', (e) => {
                if (e.target === uploadModal) {
                    uploadModal.classList.add('hidden');
                }
            });
        }
    });
    </script>
    <script src="../path/to/flowbite/dist/flowbite.min.js"></script>

</body>

</html>
