<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Tengah Semester I - {{ $siswa->nama }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            background: white;
            color: black;
            padding: 15px;
        }

        @page {
            size: A4;
            margin: 1cm;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
        }

        /* Header with Logo */
        .header {
            position: relative;
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
        }

        .header .logo {
            position: absolute;
            right: 0;
            top: 0;
            width: 80px;
            height: 80px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .header .logo img {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }

        .header .logo-placeholder {
            font-size: 8px;
            text-align: center;
            font-weight: bold;
            color: #666;
        }

        .header .school-info {
            margin-right: 90px;
        }

        .header h1 {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 1px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .header p {
            font-size: 9px;
            margin-bottom: 1px;
        }

        /* Title */
        .title {
            text-align: center;
            margin: 15px 0 20px 0;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Student Info */
        .student-info {
            margin-bottom: 15px;
        }

        .student-info table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .student-info td {
            padding: 4px 8px;
            border: 1px solid #000;
            font-size: 10px;
            vertical-align: middle;
        }

        .student-info .label {
            width: 15%;
            font-weight: bold;
            background: #f5f5f5;
        }

        .student-info .colon {
            width: 2%;
            text-align: center;
            background: #f5f5f5;
        }

        .student-info .value {
            width: 33%;
            font-weight: bold;
        }

        /* Main Table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 15px;
        }

        .main-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            vertical-align: middle;
        }

        .main-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9px;
            vertical-align: top;
        }

        .main-table .col-no {
            width: 3%;
            text-align: center;
            font-weight: bold;
        }

        .main-table .col-subject {
            width: 22%;
            font-weight: bold;
        }

        .main-table .col-grade {
            width: 6%;
            text-align: center;
            font-weight: bold;
        }

        .main-table .col-achievement {
            width: 69%;
            text-align: justify;
            line-height: 1.3;
        }

        /* Section Headers */
        .section-header {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin: 15px 0 8px 0;
            text-transform: uppercase;
        }

        /* Muatan Lokal Table */
        .muatan-lokal-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 15px;
        }

        .muatan-lokal-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            vertical-align: middle;
        }

        .muatan-lokal-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9px;
            vertical-align: top;
        }

        .muatan-lokal-table .col-no {
            width: 3%;
            text-align: center;
            font-weight: bold;
        }

        .muatan-lokal-table .col-subject {
            width: 22%;
            font-weight: bold;
        }

        .muatan-lokal-table .col-grade {
            width: 6%;
            text-align: center;
            font-weight: bold;
        }

        .muatan-lokal-table .col-achievement {
            width: 69%;
            text-align: justify;
            line-height: 1.3;
        }

        /* Ekstrakurikuler Table */
        .ekstrakurikuler-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 15px;
        }

        .ekstrakurikuler-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            vertical-align: middle;
        }

        .ekstrakurikuler-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9px;
            vertical-align: top;
        }

        .ekstrakurikuler-table .col-no {
            width: 3%;
            text-align: center;
            font-weight: bold;
        }

        .ekstrakurikuler-table .col-activity {
            width: 35%;
            font-weight: bold;
        }

        .ekstrakurikuler-table .col-description {
            width: 62%;
        }

        /* Catatan Guru */
        .catatan-section {
            margin-bottom: 15px;
        }

        .catatan-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .catatan-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 9px;
            line-height: 1.4;
            text-align: justify;
            min-height: 50px;
            vertical-align: top;
        }

        /* Ketidakhadiran */
        .ketidakhadiran-section {
            margin-bottom: 20px;
        }

        .ketidakhadiran-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .ketidakhadiran-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 9px;
            font-weight: normal;
        }

        /* Signatures */
        .signatures {
            margin-top: 20px;
        }

        .signatures table {
            width: 100%;
            border-collapse: collapse;
        }

        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 5px;
            font-size: 9px;
        }

        .signatures .sig-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .signatures .sig-location {
            font-weight: bold;
            text-align: right;
            margin-bottom: 10px;
        }

        .signatures .sig-space {
            height: 50px;
            margin: 10px 0;
        }

        .signatures .sig-name {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 3px;
        }

        .signatures .sig-nip {
            font-size: 8px;
        }

        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .print-button:hover {
            background: #0056b3;
        }

        /* Empty rows */
        .empty-row td {
            height: 18px;
        }

        /* Date location */
        .date-location {
            text-align: right;
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        @media print {
            body {
                font-size: 9px;
            }
            
            .container {
                padding: 0;
            }
            
            .header {
                margin-bottom: 10px;
                padding-bottom: 5px;
            }
            
            .title {
                margin: 10px 0 15px 0;
            }
            
            .signatures {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button (hidden when printing) -->
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Cetak Rapor
    </button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                @if(isset($profilSekolah) && $profilSekolah->logo)
                    <img src="{{ asset('storage/' . $profilSekolah->logo) }}" alt="Logo Sekolah">
                @else
                    <div class="logo-placeholder">
                        LOGO<br>SEKOLAH
                    </div>
                @endif
            </div>
            
            <div class="school-info">
                <h1>Pemerintah Kota Bandung</h1>
                <h1>Dinas Pendidikan Kota Bandung</h1>
                <h2>SD IT Al-Hidayah Logam</h2>
                <p>Jl. Logam No.12 (Jl. Timah No.1)</p>
                <p>Telp. (022) 87507287</p>
            </div>
        </div>

        <!-- Title -->
        <div class="title">
            Rapor Tengah Semester I
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <table>
                <tr>
                    <td class="label">Nama Siswa</td>
                    <td class="colon">:</td>
                    <td class="value">{{ strtoupper($siswa->nama) }}</td>
                    <td class="label">Kelas</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $siswa->kelas->nomor_kelas }} {{ $siswa->kelas->nama_kelas }}</td>
                </tr>
                <tr>
                    <td class="label">NISN/NIS</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $siswa->nisn }}/{{ $siswa->nis }}</td>
                    <td class="label">Tahun Pelajaran</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $tahunAjaran->tahun_ajaran ?? '2024/2025' }}</td>
                </tr>
            </table>
        </div>

        <!-- Main Subjects Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-subject">Mata Pelajaran</th>
                    <th class="col-grade">Nilai</th>
                    <th class="col-achievement">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $no = 1; 
                    $mainSubjects = $siswa->nilais->filter(function($nilai) {
                        return $nilai->mataPelajaran && 
                               !$nilai->mataPelajaran->is_muatan_lokal && 
                               $nilai->nilai_akhir_rapor !== null;
                    });
                @endphp
                
                @foreach($mainSubjects as $nilai)
                    <tr>
                        <td class="col-no">{{ $no++ }}</td>
                        <td class="col-subject">{{ $nilai->mataPelajaran->nama_pelajaran }}</td>
                        <td class="col-grade">{{ $nilai->nilai_akhir_rapor }}</td>
                        <td class="col-achievement">
                            @php
                                $customCatatan = $nilai->mataPelajaran->getCatatanForSiswa($siswa->id, 'umum');
                                if ($customCatatan && $customCatatan->catatan) {
                                    echo $customCatatan->catatan;
                                } else {
                                    // Auto generate based on grade
                                    $nilaiAngka = $nilai->nilai_akhir_rapor;
                                    if ($nilaiAngka >= 88) {
                                        echo $siswa->nama . " menunjukkan pemahaman yang sangat baik dalam " . $nilai->mataPelajaran->nama_pelajaran . ". " . $siswa->nama . " mampu memahami konsep, menerapkan, dan menganalisis dengan sangat baik.";
                                    } elseif ($nilaiAngka >= 74) {
                                        echo $siswa->nama . " menunjukkan pemahaman yang baik dalam " . $nilai->mataPelajaran->nama_pelajaran . ". " . $siswa->nama . " mampu memahami konsep dan menerapkannya dengan baik.";
                                    } elseif ($nilaiAngka >= 60) {
                                        echo $siswa->nama . " menunjukkan pemahaman yang cukup dalam " . $nilai->mataPelajaran->nama_pelajaran . ". " . $siswa->nama . " sudah mampu memahami konsep dasar dengan baik.";
                                    } else {
                                        echo $siswa->nama . " perlu bimbingan dalam " . $nilai->mataPelajaran->nama_pelajaran . ". " . $siswa->nama . " disarankan untuk mengulang pembelajaran materi dasar dengan bimbingan guru.";
                                    }
                                }
                            @endphp
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Muatan Lokal Section -->
        <div class="section-header">Muatan Lokal</div>
        <table class="muatan-lokal-table">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-subject">Muatan Lokal</th>
                    <th class="col-grade">Nilai</th>
                    <th class="col-achievement">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $no = 1; 
                    $muatanLokal = $siswa->nilais->filter(function($nilai) {
                        return $nilai->mataPelajaran && 
                               $nilai->mataPelajaran->is_muatan_lokal && 
                               $nilai->nilai_akhir_rapor !== null;
                    });
                @endphp
                
                @foreach($muatanLokal as $nilai)
                    <tr>
                        <td class="col-no">{{ $no++ }}</td>
                        <td class="col-subject">{{ $nilai->mataPelajaran->nama_pelajaran }}</td>
                        <td class="col-grade">{{ $nilai->nilai_akhir_rapor }}</td>
                        <td class="col-achievement">
                            @php
                                $customCatatan = $nilai->mataPelajaran->getCatatanForSiswa($siswa->id, 'umum');
                                if ($customCatatan && $customCatatan->catatan) {
                                    echo $customCatatan->catatan;
                                } else {
                                    // Simple auto generate for muatan lokal
                                    $nilaiAngka = $nilai->nilai_akhir_rapor;
                                    if ($nilaiAngka >= 88) {
                                        echo $siswa->nama . " menunjukkan pemahaman yang sangat baik dalam " . $nilai->mataPelajaran->nama_pelajaran . ".";
                                    } elseif ($nilaiAngka >= 74) {
                                        echo $siswa->nama . " menunjukkan pemahaman yang baik dalam " . $nilai->mataPelajaran->nama_pelajaran . ".";
                                    } elseif ($nilaiAngka >= 60) {
                                        echo $siswa->nama . " menunjukkan pemahaman yang cukup dalam " . $nilai->mataPelajaran->nama_pelajaran . ".";
                                    } else {
                                        echo $siswa->nama . " perlu bimbingan dalam " . $nilai->mataPelajaran->nama_pelajaran . ".";
                                    }
                                }
                            @endphp
                        </td>
                    </tr>
                @endforeach
                
                {{-- Fill empty rows if needed --}}
                @for($i = $no; $i <= 5; $i++)
                <tr class="empty-row">
                    <td class="col-no">{{ $i }}</td>
                    <td class="col-subject"></td>
                    <td class="col-grade"></td>
                    <td class="col-achievement"></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Ekstrakurikuler Section -->
        <div class="section-header">Ekstrakurikuler</div>
        <table class="ekstrakurikuler-table">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-activity">Kegiatan Ekstrakurikuler</th>
                    <th class="col-description">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($siswa->nilaiEkstrakurikuler as $nilaiEkskul)
                <tr>
                    <td class="col-no">{{ $no++ }}</td>
                    <td class="col-activity">{{ $nilaiEkskul->ekstrakurikuler->nama_ekstrakurikuler }}</td>
                    <td class="col-description">{{ $nilaiEkskul->deskripsi ?? $nilaiEkskul->predikat }}</td>
                </tr>
                @endforeach
                
                {{-- Fill empty rows --}}
                @for($i = $no; $i <= 5; $i++)
                <tr class="empty-row">
                    <td class="col-no">{{ $i }}</td>
                    <td class="col-activity"></td>
                    <td class="col-description"></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Catatan Guru Section -->
        <div class="section-header">Catatan Guru</div>
        <div class="catatan-section">
            <table class="catatan-table">
                <tr>
                    <td>
                        @php
                            $catatanGuru = $siswa->getCatatanForCurrentSemester('umum');
                            if ($catatanGuru && $catatanGuru->catatan) {
                                echo $catatanGuru->catatan;
                            } else {
                                echo 'Alhamdulillah ananda ' . $siswa->nama . ' dalam semester ' . ($semester ?? 1) . ' ini sudah mengikuti pembelajaran dengan baik secara keseluruhan, untuk ananda ' . $siswa->nama . ' lebih percaya diri lagi dalam segala hal ya, ibu yakin ' . $siswa->nama . ' bisa dan banyak belajar di rumah ya, kemudian tingkatkan lagi prestasinya di semester berikutnya ya, tidak lupa untuk sholat lima waktunya.';
                            }
                        @endphp
                    </td>
                </tr>
            </table>
        </div>

        <!-- Ketidakhadiran Section -->
        <div class="section-header">Ketidakhadiran</div>
        <div class="ketidakhadiran-section">
            <table class="ketidakhadiran-table">
                <tr>
                    <td>Sakit : {{ $siswa->absensi->sakit ?? 0 }} Hari</td>
                </tr>
                <tr>
                    <td>Izin : {{ $siswa->absensi->izin ?? 0 }} Hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan : {{ $siswa->absensi->tanpa_keterangan ?? 0 }} Hari</td>
                </tr>
            </table>
        </div>

        <!-- Date and Signatures -->
        <div class="date-location">
            Bandung, {{ now()->format('d F Y') }}
        </div>

        <div class="signatures">
            <table>
                <tr>
                    <td>
                        <div class="sig-title">Mengetahui :</div>
                        <div class="sig-title">Orang Tua/Wali,</div>
                        <div class="sig-space"></div>
                        <div class="sig-name">____________________</div>
                    </td>
                    <td>
                        <div class="sig-title">Kepala Sekolah</div>
                        <div class="sig-space"></div>
                        <div class="sig-name">{{ $profilSekolah->kepala_sekolah ?? 'M. Tsabit Mujahid, M.Pd.I.' }}</div>
                        <div class="sig-nip">NUPTK {{ $profilSekolah->nip_kepala_sekolah ?? '4759763664130152' }}</div>
                    </td>
                    <td>
                        <div class="sig-title">Guru Kelas,</div>
                        <div class="sig-space"></div>
                        <div class="sig-name">{{ $waliKelas->nama }}</div>
                        <div class="sig-nip">NUPTK {{ $profilSekolah->nip_wali_kelas ?? $waliKelas->nuptk ?? '3438761662220002' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.addEventListener('load', function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                window.print();
            }, 1000);
        });

        // Handle print dialog close
        window.addEventListener('afterprint', function() {
            // Optional: redirect back to rapor index after printing
            if (confirm('Tutup halaman ini dan kembali ke daftar siswa?')) {
                window.close();
                // If window.close() doesn't work (some browsers block it), redirect
                setTimeout(function() {
                    window.location.href = '{{ route("wali_kelas.rapor.print_index") }}';
                }, 100);
            }
        });

        // Handle escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (confirm('Tutup halaman ini?')) {
                    window.close();
                    setTimeout(function() {
                        window.location.href = '{{ route("wali_kelas.rapor.print_index") }}';
                    }, 100);
                }
            }
        });
    </script>
</body>
</html> 