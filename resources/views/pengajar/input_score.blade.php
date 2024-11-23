@extends('layouts.pengajar.app')

@section('title', 'Input Nilai Siswa')

@section('content')
<div class="p-4 mt-16 bg-white shadow-md rounded-lg">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-green-700">{{ $subject['class'] }} - {{ $subject['name'] }}</h2>
        <div class="flex gap-4">
            <input 
                type="text" 
                id="search-bar" 
                placeholder="Cari nama siswa..." 
                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring focus:ring-green-200 focus:outline-none"
                onkeyup="filterTable()"
            >
            <button class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800" onclick="document.getElementById('saveForm').submit()">
                Simpan
            </button>
        </div>
    </div>

    <!-- Tabel Input Nilai -->
    <form id="saveForm" method="POST" action="{{ route('pengajar.save_scores', $subject['id']) }}">
        @csrf
        <div class="overflow-x-auto p-4">
            <table id="students-table" class="min-w-full text-sm text-left text-gray-500 border-collapse">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th rowspan="2" class="px-6 py-3">No</th>
                        <th rowspan="2" class="px-6 py-3">Nama Siswa</th>
                        <th colspan="6" class="px-6 py-3 text-center">Sumatif Tujuan Pembelajaran</th>
                        <th colspan="3" class="px-6 py-3 text-center">Sumatif Lingkup Materi</th>
                        <th rowspan="2" class="px-6 py-3">NA Sumatif TP</th>
                        <th rowspan="2" class="px-6 py-3">NA Sumatif LM</th>
                        <th rowspan="2" class="px-6 py-3">NA Sumatif Akhir Semester</th>
                        <th rowspan="2" class="px-6 py-3">Aksi</th>
                    </tr>
                    <tr>
                        @for ($i = 1; $i <= 6; $i++)
                        <th class="px-6 py-3 text-sm lowercase font-normal">tp {{ $i }}</th>
                        @endfor
                        @for ($j = 1; $j <= 3; $j++)
                        <th class="px-6 py-3 text-sm lowercase font-normal">materi {{ $j }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $key => $student)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $key + 1 }}</td>
                        <td class="px-6 py-4 student-name">{{ $student['name'] }}</td>
                        @for ($i = 1; $i <= 6; $i++)
                        <td class="px-6 py-4">
                            <input 
                                type="number" 
                                name="tp{{ $i }}[{{ $student['id'] }}]" 
                                class="w-full border border-gray-300 rounded-lg px-2 py-1"
                                value="{{ old('tp' . $i . '.' . $student['id'], '') }}"
                            >
                        </td>
                        @endfor
                        @for ($j = 1; $j <= 3; $j++)
                        <td class="px-6 py-4">
                            <input 
                                type="number" 
                                name="materi{{ $j }}[{{ $student['id'] }}]" 
                                class="w-full border border-gray-300 rounded-lg px-2 py-1"
                                value="{{ old('materi' . $j . '.' . $student['id'], '') }}"
                            >
                        </td>
                        @endfor
                        <td class="px-6 py-4">
                            <input 
                                type="number" 
                                name="na_sumatif_tp[{{ $student['id'] }}]" 
                                class="w-full border border-gray-300 rounded-lg px-2 py-1"
                                value="{{ old('na_sumatif_tp.' . $student['id'], '') }}"
                            >
                        </td>
                        <td class="px-6 py-4">
                            <input 
                                type="number" 
                                name="na_sumatif_lm[{{ $student['id'] }}]" 
                                class="w-full border border-gray-300 rounded-lg px-2 py-1"
                                value="{{ old('na_sumatif_lm.' . $student['id'], '') }}"
                            >
                        </td>
                        <td class="px-6 py-4">
                            <input 
                                type="number" 
                                name="na_sumatif_semester[{{ $student['id'] }}]" 
                                class="w-full border border-gray-300 rounded-lg px-2 py-1"
                                value="{{ old('na_sumatif_semester.' . $student['id'], '') }}"
                            >
                        </td>
                        <td class="px-6 py-4">
                            <button 
                                type="button" 
                                class="text-red-600 hover:text-red-800"
                                onclick="deleteRow(this)"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>
    function deleteRow(button) {
        const row = button.closest('tr');
        row.remove();
    }

    function filterTable() {
        const searchInput = document.getElementById("search-bar").value.toLowerCase();
        const table = document.getElementById("students-table");
        const rows = table.getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header
            const nameCell = rows[i].getElementsByClassName("student-name")[0];
            if (nameCell) {
                const name = nameCell.textContent || nameCell.innerText;
                if (name.toLowerCase().indexOf(searchInput) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    }
</script>
@endsection
