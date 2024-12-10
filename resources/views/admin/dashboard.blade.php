<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <title>Dashboard</title>
</head>
<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-2 sm:ml-64">
        <div class="p-2 mt-14">
            <!-- Main Content Container -->
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Statistics Grid - Takes 2/3 of the space -->
                <div class="lg:w-2/3">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Siswa Card -->
                        <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-4">
                                <p class="text-2xl font-bold text-green-600">240</p>
                                <p class="text-sm text-green-600">Siswa</p>
                            </div>
                        </div>
                        <!-- Guru Card -->
                        <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-4">
                                <p class="text-2xl font-bold text-green-600">15</p>
                                <p class="text-sm text-green-600">Guru</p>
                            </div>
                        </div>
                        <!-- Mata Pelajaran Card -->
                        <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-4">
                                <p class="text-2xl font-bold text-green-600">10</p>
                                <p class="text-sm text-green-600">Mata Pelajaran</p>
                            </div>
                        </div>
                        <!-- Kelas Card -->
                        <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-4">
                                <p class="text-2xl font-bold text-green-600">12</p>
                                <p class="text-sm text-green-600">Kelas</p>
                            </div>
                        </div>
                        <!-- Ekstrakurikuler Card -->
                        <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-4">
                                <p class="text-2xl font-bold text-green-600">4</p>
                                <p class="text-sm text-green-600">Ekstrakurikuler</p>
                            </div>
                        </div>
                        <!-- Progres Rapor Card -->
                        <div class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                            <div class="p-4">
                                <p class="text-2xl font-bold text-green-600">55%</p>
                                <p class="text-sm text-green-600">Progres Rapor</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Section - Takes 1/3 of the space -->
                <div class="lg:w-1/3">
                    <div class="bg-green-600 text-white px-3 py-1.5 rounded-lg inline-block mb-3">
                        <span class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            Informasi
                        </span>
                    </div>

                    <!-- Information Items -->
                    <div class="relative pl-6 border-l-2 border-gray-200">
                        <!-- Timeline Item 1 -->
                        <div class="mb-4 relative">
                            <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="bg-white rounded-lg border shadow-sm p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-sm font-medium">Lestari, S.Kom</h3>
                                        <p class="text-xs text-gray-600">Tolong segera input data rapor!</p>
                                    </div>
                                    <button class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Item 2 -->
                        <div class="mb-4 relative">
                            <div class="absolute -left-8 w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="bg-white rounded-lg border shadow-sm p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-sm font-medium">Lestari, S.Kom</h3>
                                        <p class="text-xs text-gray-600">Tolong segera input data rapor!</p>
                                    </div>
                                    <button class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Information Modal -->
    <div id="addInfoModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-xl font-semibold">
                        Tambah Informasi
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex items-center justify-center" data-modal-hide="addInfoModal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4">
                    <form>
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Judul informasi</label>
                            <input type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" placeholder="Masukkan judul informasi">
                        </div>
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Informasi untuk</label>
                            <select class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                                <option selected>-- Pilih --</option>
                                <option value="all">Semua</option>
                                <option value="guru">Guru</option>
                                <option value="wali_kelas">Wali Kelas</option>
                                <option value="siswa">Siswa</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Isi</label>
                            <textarea class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" rows="4" placeholder="Masukkan isi informasi"></textarea>
                        </div>
                        <button type="submit" class="w-full text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
</body>
</html>