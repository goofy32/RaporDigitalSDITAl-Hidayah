<!-- resources/views/data/teacher_data.blade.php -->
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
    <title>Detail Data Pengajar</title>
</head>

<body>
    <x-admin.topbar></x-admin.topbar>
    <x-admin.sidebar></x-admin.sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white mt-14">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-green-700">Detail Data Pengajar</h2>
                <div class="flex space-x-2">
                    <button class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600" onclick="window.history.back()">Kembali</button>
                    <button onclick="window.location.href='{{ route('teacher.edit', $teacher->id) }}'" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Edit
                    </button>
                </div>
            </div>

            <!-- Detail Pengajar -->
            <div class="flex space-x-8">
                <!-- Foto Placeholder -->
                <div class="flex items-start justify-center w-64 h-80 bg-gray-200 rounded-lg shadow-md overflow-hidden">
                @if($teacher->photo)
                    <img src="{{ asset('storage/' . $teacher->photo) }}" 
                        alt="Foto Pengajar" 
                        class="w-full h-full object-cover">
                @else
                    <div class="flex items-center justify-center w-full h-full">
                        <svg class="w-32 h-32 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
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
                                <td class="px-4 py-2">{{ $teacher->nuptk ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Nama</th>
                                <td class="px-4 py-2">{{ $teacher->nama ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Jenis Kelamin</th>
                                <td class="px-4 py-2">{{ $teacher->jenis_kelamin ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Tanggal Lahir</th>
                                <td class="px-4 py-2">
                                    @if($teacher->tanggal_lahir instanceof \Carbon\Carbon)
                                        {{ $teacher->tanggal_lahir->format('d-m-Y') }}
                                    @elseif(is_string($teacher->tanggal_lahir) && !empty($teacher->tanggal_lahir))
                                        {{ date('d-m-Y', strtotime($teacher->tanggal_lahir)) }}
                                    @else
                                        Belum Diisi
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">No Handphone</th>
                                <td class="px-4 py-2">{{ $teacher->no_handphone ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Email</th>
                                <td class="px-4 py-2">{{ $teacher->email ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Alamat</th>
                                <td class="px-4 py-2">{{ $teacher->alamat ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Jabatan</th>
                                <td class="px-4 py-2">
                                    @if($teacher->jabatan == 'guru_wali')
                                        Guru dan Wali Kelas
                                    @else
                                        Guru
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Kelas Mengajar</th>
                                <td class="px-4 py-2">
                                    @php
                                        // Get the classes where the teacher teaches (excluding wali kelas)
                                        $teachingClasses = collect();
                                        
                                        foreach($teacher->kelas as $kelas) {
                                            $classKey = $kelas->nomor_kelas . $kelas->nama_kelas;
                                            $isWaliKelas = $kelas->pivot->is_wali_kelas && $kelas->pivot->role === 'wali_kelas';
                                            
                                            // Only include in teaching classes if not wali kelas role
                                            if (!$isWaliKelas) {
                                                $teachingClasses->put($classKey, [
                                                    'nomor' => $kelas->nomor_kelas,
                                                    'nama' => $kelas->nama_kelas
                                                ]);
                                            }
                                        }
                                    @endphp

                                    @if($teachingClasses->count() > 0)
                                        <ul>
                                            @foreach($teachingClasses as $kelas)
                                                <li>{{ $kelas['nomor'] }} {{ $kelas['nama'] }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                            </tr>

                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Wali Kelas</th>
                                <td class="px-4 py-2">
                                    @php
                                        // Get explicitly the Wali Kelas relationship
                                        // This is the most reliable method as it uses the dedicated relationship
                                        $kelasWali = $teacher->kelasWali()->first();
                                    @endphp
                                    
                                    @if($kelasWali)
                                        Kelas {{ $kelasWali->nomor_kelas }} {{ $kelasWali->nama_kelas }}
                                    @else
                                        <span>Bukan Wali Kelas</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Username</th>
                                <td class="px-4 py-2">{{ $teacher->username ?? 'Belum Diisi' }}</td>
                            </tr>
                            <tr class="border-b">
                                <th class="px-4 py-2 font-medium text-gray-900">Password</th>
                                <td class="px-4 py-2">
                                    <div class="flex items-center space-x-2">
                                        <span id="passwordText">••••••••</span>
                                        <button type="button"
                                                onclick="togglePassword('{{ $teacher->id }}')"
                                                class="text-green-600 hover:text-green-800 inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Lihat Password
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
function togglePassword(teacherId) {
    if(confirm('Apakah Anda yakin ingin melihat password pengajar ini?')) {
        fetch(`/admin/pengajar/${teacherId}/password`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const passwordText = document.getElementById('passwordText');
            if (data.status === 'success') {
                if (passwordText.textContent === '••••••••') {
                    passwordText.textContent = data.password;
                } else {
                    passwordText.textContent = '••••••••';
                }
            } else {
                alert(data.message || 'Password tidak tersedia');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil password');
        });
    }
}
</script>
</body>
</html>