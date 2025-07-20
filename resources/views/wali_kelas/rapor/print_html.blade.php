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
            font-size: 12px;
            line-height: 1.3;
            background: white;
            color: black;
        }

        @page {
            size: A4;
            margin: 1cm;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
        }

        .container {
            max-width: 21cm;
            margin: 0 auto;
            padding: 15px;
            background: white;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            position: relative;
        }

        .header h1 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .header h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
            line-height: 1.2;
        }

        .header p {
            font-size: 9px;
            margin-bottom: 1px;
            line-height: 1.1;
        }

        .logo {
            position: absolute;
            right: 0;
            top: 0;
            width: 60px;
            height: 60px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            text-align: center;
        }

        /* Title */
        .title {
            text-align: center;
            margin: 15px 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
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
            padding: 4px 6px;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 11px;
        }

        .student-info td:first-child {
            width: 15%;
            font-weight: bold;
        }

        .student-info td:nth-child(2) {
            width: 2%;
            text-align: center;
        }

        .student-info td:nth-child(3) {
            width: 33%;
        }

        .student-info td:nth-child(4) {
            width: 15%;
            font-weight: bold;
        }

        .student-info td:nth-child(5) {
            width: 2%;
            text-align: center;
        }

        .student-info td:nth-child(6) {
            width: 33%;
        }

        /* Grades Table */
        .grades-section {
            margin-bottom: 15px;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .grades-table th,
        .grades-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
            font-size: 11px;
        }

        .grades-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .grades-table .number-col {
            width: 4%;
            text-align: center;
        }

        .grades-table .subject-col {
            width: 24%;
        }

        .grades-table .grade-col {
            width: 8%;
            text-align: center;
        }

        .grades-table .achievement-col {
            width: 64%;
            line-height: 1.3;
        }

        /* Extracurricular */
        .extracurricular-section {
            margin-bottom: 15px;
        }

        .extracurricular-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .extracurricular-table th,
        .extracurricular-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
            font-size: 11px;
        }

        .extracurricular-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .extracurricular-table .number-col {
            width: 4%;
            text-align: center;
        }

        .extracurricular-table .activity-col {
            width: 36%;
        }

        .extracurricular-table .description-col {
            width: 60%;
        }

        /* Notes */
        .notes-section {
            margin-bottom: 15px;
        }

        .notes-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .notes-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
            min-height: 50px;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Attendance */
        .attendance-section {
            margin-bottom: 15px;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .attendance-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 11px;
        }

        /* Signatures */
        .signatures {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 30%;
            font-size: 10px;
        }

        .signature-box p {
            margin-bottom: 40px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin: 40px 0 3px 0;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }

        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .print-button:hover {
            background: #0056b3;
        }

        .section-title {
            font-weight: bold;
            text-align: center;
            margin: 12px 0 8px 0;
            font-size: 11px;
        }

        /* Print-specific adjustments */
        @media print {
            .container {
                padding: 10px;
            }
            
            .header {
                margin-bottom: 10px;
                padding-bottom: 5px;
            }
            
            .grades-section,
            .extracurricular-section,
            .notes-section,
            .attendance-section {
                margin-bottom: 10px;
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
        📄 Cetak Rapor
    </button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                @if(isset($profilSekolah) && $profilSekolah->logo)
                    <img src="{{ asset('storage/' . $profilSekolah->logo) }}" alt="Logo" style="max-width: 50px; max-height: 50px;">
                @else
                    LOGO<br>SEKOLAH
                @endif
            </div>
            
            <h1>PEMERINTAH KOTA BANDUNG</h1>
            <h1>DINAS PENDIDIKAN KOTA BANDUNG</h1>
            <h2>SD IT AL-HIDAYAH LOGAM</h2>
            <p>Jl. Logam No.12 (Jl. Timah No.1)</p>
            <p>Telp. (022) 87507287</p>
        </div>

        <!-- Title -->
        <div class="title">
            RAPOR TENGAH SEMESTER I
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <table>
                <tr>
                    <td>Nama Siswa</td>
                    <td>:</td>
                    <td>{{ strtoupper($siswa->nama) }}</td>
                    <td>Kelas</td>
                    <td>:</td>
                    <td>{{ $siswa->kelas->nomor_kelas }} {{ $siswa->kelas->nama_kelas }}</td>
                </tr>
                <tr>
                    <td>NISN/NIS</td>
                    <td>:</td>
                    <td>{{ $siswa->nisn }}/{{ $siswa->nis }}</td>
                    <td>Tahun Pelajaran</td>
                    <td>:</td>
                    <td>{{ $tahunAjaran->tahun_ajaran ?? '2024/2025' }}</td>
                </tr>
            </table>
        </div>

        <!-- Main Subjects -->
        <div class="grades-section">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th class="number-col">No.</th>
                        <th class="subject-col">Mata Pelajaran</th>
                        <th class="grade-col">Nilai</th>
                        <th class="achievement-col">Capaian Kompetensi</th>
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
                            <td class="number-col">{{ $no++ }}</td>
                            <td class="subject-col">{{ $nilai->mataPelajaran->nama_pelajaran }}</td>
                            <td class="grade-col">{{ $nilai->nilai_akhir_rapor }}</td>
                            <td class="achievement-col">
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
        </div>

        <!-- Local Content (Muatan Lokal) -->
        <div class="section-title">MUATAN LOKAL</div>
        <div class="grades-section">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th class="number-col">No.</th>
                        <th class="subject-col">Muatan Lokal</th>
                        <th class="grade-col">Nilai</th>
                        <th class="achievement-col">Capaian Kompetensi</th>
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
                            <td class="number-col">{{ $no++ }}</td>
                            <td class="subject-col">{{ $nilai->mataPelajaran->nama_pelajaran }}</td>
                            <td class="grade-col">{{ $nilai->nilai_akhir_rapor }}</td>
                            <td class="achievement-col">
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
                    <tr>
                        <td class="number-col">{{ $i }}</td>
                        <td class="subject-col"></td>
                        <td class="grade-col"></td>
                        <td class="achievement-col"></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Extracurricular -->
        <div class="section-title">EKSTRAKURIKULER</div>
        <div class="extracurricular-section">
            <table class="extracurricular-table">
                <thead>
                    <tr>
                        <th class="number-col">No.</th>
                        <th class="activity-col">Kegiatan Ekstrakurikuler</th>
                        <th class="description-col">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($siswa->nilaiEkstrakurikuler as $nilaiEkskul)
                    <tr>
                        <td class="number-col">{{ $no++ }}</td>
                        <td class="activity-col">{{ $nilaiEkskul->ekstrakurikuler->nama_ekstrakurikuler }}</td>
                        <td class="description-col">{{ $nilaiEkskul->deskripsi ?? $nilaiEkskul->predikat }}</td>
                    </tr>
                    @endforeach
                    
                    {{-- Fill empty rows --}}
                    @for($i = $no; $i <= 5; $i++)
                    <tr>
                        <td class="number-col">{{ $i }}</td>
                        <td class="activity-col"></td>
                        <td class="description-col"></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Teacher Notes -->
        <div class="section-title">CATATAN GURU</div>
        <div class="notes-section">
            <table class="notes-table">
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

        <!-- Attendance -->
        <div class="section-title">KETIDAKHADIRAN</div>
        <div class="attendance-section">
            <table class="attendance-table">
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

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <p><strong>Mengetahui:</strong></p>
                <p><strong>Orang Tua/Wali,</strong></p>
                <div class="signature-line"></div>
                <p>____________________</p>
            </div>
            
            <div class="signature-box">
                <p>&nbsp;</p>
                <p><strong>Bandung, {{ now()->format('d F Y') }}</strong></p>
                <p><strong>Kepala Sekolah,</strong></p>
                <div class="signature-line"></div>
                <p><strong>{{ $profilSekolah->kepala_sekolah ?? 'M. Tsabit Mujahid, M.Pd.I.' }}</strong></p>
                <p>NUPTK {{ $profilSekolah->nip_kepala_sekolah ?? '4759763664130152' }}</p>
            </div>
            
            <div class="signature-box">
                <p>&nbsp;</p>
                <p><strong>Guru Kelas,</strong></p>
                <div class="signature-line"></div>
                <p><strong>{{ $waliKelas->nama }}</strong></p>
                <p>NUPTK {{ $profilSekolah->nip_wali_kelas ?? $waliKelas->nuptk ?? '3438761662220002' }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak otomatis oleh sistem pada {{ now()->format('d F Y H:i:s') }}. Tidak perlu tanda tangan basah.</p>
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