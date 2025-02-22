<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Tambah Data Mata Pelajaran</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-6 bg-white mt-14">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Form Tambah Data Mata Pelajaran</h2>
                <div>
                    <button onclick="window.history.back()" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Kembali
                    </button>
                    <button type="submit" form="addSubjectForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Simpan
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form id="addSubjectForm" action="{{ route('subject.store') }}" method="POST"  @submit="handleSubmit" x-data="formProtection" class="space-y-6">
                @csrf

                <!-- Mata Pelajaran -->
                <div>
                    <label for="mata_pelajaran" class="block mb-2 text-sm font-medium text-gray-900">Mata Pelajaran</label>
                    <input type="text" id="mata_pelajaran" name="mata_pelajaran" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Kelas Dropdown -->
                <div>
                    <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
                    <select id="kelas" name="kelas" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->nomor_kelas }} - {{ $class->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Siswa Dropdown -->
                <div>
                    <label for="semester" class="block mb-2 text-sm font-medium text-gray-900">Semester</label>
                    <select id="semester" name="semester" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Semester</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                    </select>
                </div>

                <!-- Guru Pengampu Dropdown -->
                <div>
                    <label for="guru_pengampu" class="block mb-2 text-sm font-medium text-gray-900">Guru Pengampu</label>
                    <select id="guru_pengampu" name="guru_pengampu" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Guru</option>
                        <!-- Loop through teachers -->
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->nama }}</option> // Menggunakan nama bukan name
                    @endforeach
                    </select>
                </div>

                <!-- Lingkup Materi -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900">Lingkup Materi</label>
                    <div id="lingkupMateriContainer">
                        <div class="flex items-center mb-2">
                            <input type="text" name="lingkup_materi[]" required
                                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <button type="button" onclick="addLingkupMateri()" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Flowbite JS (if needed) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>


    <!-- JavaScript for dynamic Lingkup Materi -->
    <script>
        function addLingkupMateri() {
            const container = document.getElementById('lingkupMateriContainer');
            const div = document.createElement('div');
            div.className = 'flex items-center mb-2';
            
            div.innerHTML = `
                <input type="text" name="lingkup_materi[]" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                <button type="button" onclick="removeLingkupMateri(this)" class="ml-2 p-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            `;
            
            container.appendChild(div);
        }
        
        function removeLingkupMateri(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>
