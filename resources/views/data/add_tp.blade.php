@extends('layouts.app')

@section('title', 'Tambah Tujuan Pembelajaran')

@section('content')
<div>
    <div class="p-4 bg-white mt-14 shadow-lg rounded-lg">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-green-700">Form Tambah Tujuan Pembelajaran untuk {{ $mataPelajaran->nama_pelajaran }}</h2>
            <div>
                <button onclick="window.history.back()" class="px-4 py-2 mr-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Kembali
                </button>
                <button onclick="saveData()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Simpan
                </button>
            </div>
        </div>
        
        <!-- Form -->
        <form id="addTPForm" class="space-y-6">
            <!-- Mata Pelajaran -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Mata Pelajaran</label>
                <p class="text-gray-700 font-semibold">{{ $mataPelajaran->nama_pelajaran }}</p>
                <input type="hidden" id="mata_pelajaran_id" value="{{ $mataPelajaran->id }}">
            </div>

            <!-- Lingkup Materi Dropdown -->
            <div>
                <label for="lingkup_materi" class="block mb-2 text-sm font-medium text-gray-900">Lingkup Materi</label>
                <select id="lingkup_materi" required
                    class="block w-full p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih Lingkup Materi</option>
                    @foreach($mataPelajaran->lingkupMateris as $lm)
                        <option value="{{ $lm->id }}">{{ $lm->judul_lingkup_materi }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Kode TP and Deskripsi TP Inputs -->
            <div id="tpContainer">
                <div class="flex items-center mb-2">
                    <input type="text" name="kode_tp[]" placeholder="Kode TP (contoh: TP1)" required
                        class="block w-1/3 p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 mr-2">
                    <input type="text" name="deskripsi_tp[]" placeholder="Deskripsi TP" required
                        class="block w-2/3 p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <button type="button" onclick="addTPRow()" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <!-- Plus Icon -->
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Tambah Button -->
            <button type="button" onclick="addRow()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Tambah
            </button>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg mt-6">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Lingkup Materi</th>
                        <th class="px-6 py-3">Kode TP</th>
                        <th class="px-6 py-3">Deskripsi TP</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tpTableBody">
                    <!-- Data will be added dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript Section -->
<script>
    const csrfToken = '{{ csrf_token() }}';
    const mataPelajaranId = '{{ $mataPelajaran->id }}';
    let tpData = []; // Array untuk menyimpan data TP sementara

    // Fungsi untuk menambahkan input Kode TP dan Deskripsi TP
    function addTPRow() {
        const container = document.getElementById('tpContainer');
        const div = document.createElement('div');
        div.className = 'flex items-center mb-2';

        div.innerHTML = `
            <input type="text" name="kode_tp[]" placeholder="Kode TP" required
                class="block w-1/3 p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 mr-2">
            <input type="text" name="deskripsi_tp[]" placeholder="Deskripsi TP" required
                class="block w-2/3 p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            <button type="button" onclick="removeTPRow(this)" class="ml-2 p-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <!-- Minus Icon -->
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 14a1 1 0 01-1-1V6a1 1 0 012 0v7a1 1 0 01-1 1z" clip-rule="evenodd"/>
                </svg>
            </button>
        `;

        container.appendChild(div);
    }

    function removeTPRow(button) {
        button.parentElement.remove();
    }

    // Fungsi untuk menambahkan data ke tabel
    function addRow() {
        const lingkupMateriId = document.getElementById('lingkup_materi').value;
        const kodeTPs = document.getElementsByName('kode_tp[]');
        const deskripsiTPs = document.getElementsByName('deskripsi_tp[]');

        if (!lingkupMateriId || kodeTPs.length === 0) {
            alert('Harap isi semua field!');
            return;
        }

        for (let i = 0; i < kodeTPs.length; i++) {
            const newRow = {
                id: tpData.length + 1,
                mataPelajaranId, // Menggunakan mataPelajaranId dari variabel JavaScript
                lingkupMateriId,
                lingkupMateriText: document.getElementById('lingkup_materi').options[document.getElementById('lingkup_materi').selectedIndex].text,
                kodeTP: kodeTPs[i].value,
                deskripsiTP: deskripsiTPs[i].value,
            };

            tpData.push(newRow);
        }

        renderTable();
        clearForm();
    }

    // Fungsi untuk menampilkan data di tabel
    function renderTable() {
        const tableBody = document.getElementById('tpTableBody');
        tableBody.innerHTML = ''; // Bersihkan tabel sebelum render ulang

        tpData.forEach((tp, index) => {
            const row = `
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4">${index + 1}</td>
                    <td class="px-6 py-4">${tp.lingkupMateriText}</td>
                    <td class="px-6 py-4">${tp.kodeTP}</td>
                    <td class="px-6 py-4">${tp.deskripsiTP}</td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="deleteRow(${index})" class="text-red-600 hover:underline">Hapus</button>
                    </td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    }

    function deleteRow(index) {
        tpData.splice(index, 1);
        renderTable();
    }

    function clearForm() {
        // Hapus semua input Kode TP dan Deskripsi TP kecuali satu
        const tpContainer = document.getElementById('tpContainer');
        tpContainer.innerHTML = `
            <div class="flex items-center mb-2">
                <input type="text" name="kode_tp[]" placeholder="Kode TP" required
                    class="block w-1/3 p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 mr-2">
                <input type="text" name="deskripsi_tp[]" placeholder="Deskripsi TP" required
                    class="block w-2/3 p-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                <button type="button" onclick="addTPRow()" class="ml-2 p-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <!-- Plus Icon -->
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        `;
    }

    function saveData() {
        if (tpData.length === 0) {
            alert('Tidak ada data untuk disimpan!');
            return;
        }

        // Tambahkan log untuk debugging
        console.log('tpData:', tpData);
        console.log('mataPelajaranId:', mataPelajaranId);

        // Kirim data ke server menggunakan AJAX
        fetch('{{ route('tujuan_pembelajaran.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ tpData: tpData, mataPelajaranId: mataPelajaranId }),
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Terjadi kesalahan');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Data berhasil disimpan!');
                window.location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan saat menyimpan data.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Terjadi kesalahan saat menyimpan data.');
        });
    }
</script>
@endsection
