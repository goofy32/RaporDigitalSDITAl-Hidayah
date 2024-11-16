<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <form id="addSubjectForm" action="{{ route('subject.store') }}" method="POST" class="space-y-6">
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
                        <!-- Loop through classes -->
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Siswa Dropdown -->
                <div>
                    <label for="siswa" class="block mb-2 text-sm font-medium text-gray-900">Siswa</label>
                    <select id="siswa" name="siswa" required
                        class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Siswa</option>
                        <!-- Loop through students -->
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
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
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Lingkup Materi -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900">Lingkup Materi</label>
                    <div id="lingkupMateriContainer">
                        <!-- First input field -->
                        <div class="flex items-center mb-2">
                            <input type="text" name="lingkup_materi[]" required
                                class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <button type="button" id="addLingkupMateri" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <!-- Plus Icon -->
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
    <script src="../path/to/flowbite/dist/flowbite.min.js"></script>

    <!-- JavaScript for dynamic Lingkup Materi -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lingkupMateriContainer = document.getElementById('lingkupMateriContainer');
            const addLingkupMateriButton = document.getElementById('addLingkupMateri');

            addLingkupMateriButton.addEventListener('click', function () {
                const newField = document.createElement('div');
                newField.classList.add('flex', 'items-center', 'mb-2');

                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'lingkup_materi[]';
                input.required = true;
                input.classList.add('block', 'w-full', 'p-2.5', 'bg-gray-50', 'border', 'border-gray-300', 'rounded-lg', 'focus:ring-green-500', 'focus:border-green-500');

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.classList.add('ml-2', 'p-2', 'bg-red-600', 'text-white', 'rounded-lg', 'hover:bg-red-700');

                // Trash Icon
                removeButton.innerHTML = `
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 3a1 1 0 00-.894.553L4.382 5H2a1 1 0 100 2h1.293l1.614 9.447A2 2 0 007.862 18h4.276a2 2 0 001.955-1.553L15.707 7H17a1 1 0 100-2h-2.382l-.724-1.447A1 1 0 0011 3H6zm1.236 5h5.528l-.614 7H7.85l-.614-7z" clip-rule="evenodd"/>
                    </svg>
                `;

                // Remove field on click
                removeButton.addEventListener('click', function () {
                    lingkupMateriContainer.removeChild(newField);
                });

                newField.appendChild(input);
                newField.appendChild(removeButton);

                lingkupMateriContainer.appendChild(newField);
            });
        });
    </script>
</body>
</html>
