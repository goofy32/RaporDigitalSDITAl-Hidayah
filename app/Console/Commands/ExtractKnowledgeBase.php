<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractKnowledgeBase extends Command
{
    protected $signature = 'gemini:extract-knowledge';
    protected $description = 'Extract knowledge base from PDF content for Gemini AI';

    public function handle()
    {
        $this->info('Extracting knowledge base from PDF content...');

        // Konten yang sudah diekstrak dari PDF
        $knowledgeContent = $this->getPdfContent();

        // Simpan ke storage
        $knowledgeDir = storage_path('app/knowledge');
        if (!is_dir($knowledgeDir)) {
            mkdir($knowledgeDir, 0755, true);
        }

        $knowledgeFile = $knowledgeDir . '/rapor_sdit_guide.txt';
        file_put_contents($knowledgeFile, $knowledgeContent);

        $this->info('Knowledge base saved to: ' . $knowledgeFile);
        $this->info('File size: ' . number_format(strlen($knowledgeContent)) . ' characters');

        return Command::SUCCESS;
    }

    private function getPdfContent()
    {
        return "
=== PANDUAN LENGKAP SISTEM RAPOR SDIT AL-HIDAYAH ===

## NAVIGASI SISTEM & AKSES

### Login ke Sistem:
1. **Admin Login:**
   - URL: [domain]/login
   - Gunakan: Email + Password
   - Setelah login → otomatis ke Dashboard Admin

2. **Guru Login:**
   - URL: [domain]/login  
   - Gunakan: Username + Password (BUKAN email!)
   - Setelah login → pilih role:
     * Guru Pengajar → Dashboard Pengajar
     * Wali Kelas → Dashboard Wali Kelas

### Struktur Menu Utama:

**Menu Admin:**
- Dashboard
- Tahun Ajaran
- Profile Sekolah
- Kelas
- Pengajar
- Siswa
- Pelajaran
- Prestasi
- Ekstrakurikuler
- Format Rapor
- Riwayat Cetak Rapor
- Kenaikan Kelas

**Menu Guru Pengajar:**
- Dashboard
- Mata Pelajaran
- Input Nilai
- Notifikasi
- Profile

**Menu Wali Kelas:**
- Dashboard
- Siswa
- Input Nilai
- Absensi
- Ekstrakurikuler
- Rapor
- Notifikasi
- Profile

## TAHUN AJARAN - PANDUAN LENGKAP

### Masalah Duplikat Tahun Ajaran:

**Penyebab & Solusi:**
1. **Format Tahun Ajaran Salah**
   - ❌ Salah: 2024-2025, 2024 2025, 24/25
   - ✅ Benar: 2024/2025
   - **Solusi:** Pastikan format YYYY/YYYY dengan slash (/)

2. **Tahun Ajaran Sudah Ada**
   - Cara Cek: Admin → Tahun Ajaran → Klik \"Tampilkan Arsip\"
   - Jika Sudah Ada:
     * Aktif: Gunakan yang sudah ada
     * Tidak Aktif: Klik checkbox untuk mengaktifkan
     * Diarsipkan: Klik tombol \"Pulihkan\"

3. **Semester Berbeda**
   - Sistem membuat entry terpisah untuk Semester 1 (Ganjil) dan Semester 2 (Genap)
   - Contoh: 2024/2025 - Ganjil dan 2024/2025 - Genap
   - Ini NORMAL dan bukan duplikat

### Cara Membuat Tahun Ajaran Baru:

**Opsi A: Membuat dari Nol**
1. Admin → Tahun Ajaran → Tambah Tahun Ajaran
2. Isi form:
   - Tahun Ajaran: 2024/2025 (format YYYY/YYYY)
   - Semester: Otomatis Semester 1 (Ganjil)
   - Tanggal Mulai & Selesai
   - Deskripsi (opsional)
3. Klik \"Simpan Tahun Ajaran\"

**Opsi B: Salin dari Tahun Sebelumnya (Recommended)**
1. Admin → Tahun Ajaran
2. Klik \"Salin ke Tahun Ajaran Baru\" di tahun yang ingin disalin
3. Isi tahun ajaran baru (contoh: 2025/2026)
4. Pilih data yang ingin disalin:
   - Kelas (termasuk wali kelas)
   - Mata Pelajaran (termasuk kurikulum)
   - Template Rapor
   - Ekstrakurikuler
   - KKM & Bobot Nilai
5. Pengaturan khusus:
   - Tingkatkan nomor kelas (+1)
   - Pertahankan guru
   - Buat kelas 1 baru untuk siswa baru

## SETUP AWAL SISTEM (WAJIB!)

### Urutan Setup untuk Admin Baru:

1. **Profile Sekolah (WAJIB)**
   - Navigasi: Admin → Profile Sekolah
   - Mengapa Wajib: Tanpa ini, menu lain tidak bisa diakses
   - Data yang Harus Diisi:
     * Nama Sekolah
     * NPSN
     * Alamat Lengkap
     * Kepala Sekolah
     * Tempat & Tanggal Terbit Rapor
     * Logo Sekolah (opsional)

2. **Tahun Ajaran (WAJIB)**
   - Navigasi: Admin → Tahun Ajaran → Tambah Tahun Ajaran
   - Buat tahun ajaran untuk periode saat ini
   - Aktifkan tahun ajaran tersebut

3. **Kelas**
   - Navigasi: Admin → Kelas → Tambah Data
   - Buat kelas: 1A, 1B, 2A, 2B, dst
   - Format: Nomor Kelas (1-6) + Nama Kelas (A, B, C)

4. **Guru**
   - Navigasi: Admin → Pengajar → Tambah Data
   - Input data guru lengkap
   - Penting: Username untuk login guru
   - Set role: Guru atau Guru+Wali Kelas

5. **Siswa**
   - Navigasi: Admin → Siswa → Tambah Data atau Upload Excel
   - Input siswa dan tempatkan ke kelas
   - Bisa upload massal dengan Excel

6. **Mata Pelajaran**
   - Navigasi: Admin → Pelajaran → Tambah Data
   - Buat mata pelajaran per kelas
   - Assign guru pengampu

## WORKFLOW PENGGUNAAN SEHARI-HARI

### Admin Workflow:

**Awal Semester:**
1. Cek Tahun Ajaran Aktif
2. Setup Template Rapor → Upload template UTS dan UAS
3. Pengaturan Nilai → Set KKM dan bobot nilai (TP, LM, AS)

**Selama Semester:**
- Monitor progress nilai di Dashboard
- Cek riwayat cetak rapor
- Kelola data master jika ada perubahan

**Akhir Semester:**
- Cek kelengkapan nilai semua siswa
- Proses kenaikan kelas
- Archive tahun ajaran jika selesai

### Guru Pengajar Workflow:

**Setup Awal:**
1. Login dengan Username + Password
2. Pilih Role: \"Guru Pengajar\"
3. Setup Mata Pelajaran → Buat Lingkup Materi dan Tujuan Pembelajaran

**Input Nilai:**
1. Pilih Mata Pelajaran
2. Input Nilai → Pilih Siswa
3. Isi Nilai: S.TP, S.LM, S.AS
4. Simpan → Nilai otomatis dihitung

### Wali Kelas Workflow:

**Selain Input Nilai:**
1. Kelola Siswa → Update data siswa
2. Input Absensi → Isi: Sakit, Izin, Tanpa Keterangan
3. Input Ekstrakurikuler → Isi nilai/deskripsi kegiatan
4. Generate Rapor → Pilih jenis UTS atau UAS

## TROUBLESHOOTING UMUM

### Masalah Login:
- **\"Kredensial tidak valid\"**
  * Admin: Gunakan EMAIL + Password
  * Guru: Gunakan USERNAME + Password (bukan email!)
  * Cek: Caps Lock, spasi extra

- **\"Tidak bisa pilih role\"**
  * Penyebab: Guru tidak punya assignment kelas
  * Solusi: Minta admin assign ke kelas atau mata pelajaran

### Masalah Template Rapor:
- **\"Template tidak ditemukan\"**
  * Cek: Admin → Format Rapor
  * Pastikan: Ada template aktif untuk jenis rapor (UTS/UAS)

- **\"Placeholder tidak ditemukan\"**
  * Format: Harus \${nama_placeholder}
  * Jangan: Spasi dalam placeholder
  * Contoh Benar: \${nama_siswa}, \${nilai_matematika}

### Masalah Nilai:
- **\"Total bobot tidak 100%\"**
  * Lokasi: Admin → Profile → Pengaturan Rapor
  * Aturan: Bobot TP + LM + AS = 100%
  * Default: 25% + 25% + 50% = 100%

- **\"Nilai tidak muncul di rapor\"**
  * Cek: Nilai sudah disimpan
  * Cek: KKM sudah diset
  * Cek: Mata pelajaran sudah assign ke guru

### Masalah Kenaikan Kelas:
- **\"Tidak ada kelas tujuan\"**
  * Solusi: Buat kelas untuk tingkat berikutnya
  * Contoh: Siswa kelas 1 naik → harus ada kelas 2

### Masalah Umum:
- **Menu tidak muncul (abu-abu)**
  * Penyebab: Setup dasar belum lengkap
  * Solusi: Lengkapi Profile Sekolah + Tahun Ajaran

- **Data tidak tersimpan**
  * Cek: Koneksi internet
  * Cek: Session masih aktif
  * Solusi: Refresh dan login ulang

## TIPS & BEST PRACTICES

### Untuk Admin:
- **Backup Rutin:** Export data siswa, backup template rapor
- **Monitoring:** Cek Dashboard untuk progress nilai
- **Review:** Audit log untuk keamanan

### Untuk Guru:
- **Input Nilai:** Input secara bertahap, jangan tunggu akhir semester
- **Koordinasi:** Dengan wali kelas untuk data lengkap
- **Keamanan:** Logout setelah selesai, jangan share password

### Untuk Wali Kelas:
- **Sebelum Generate Rapor:**
  * Semua nilai mata pelajaran sudah diinput
  * Data absensi sudah lengkap
  * Nilai ekstrakurikuler sudah diisi
  * Template rapor sudah aktif

## KONTEKS KHUSUS SDIT

### Struktur Sekolah:
- Tingkat: Kelas 1-6 (Sekolah Dasar)
- Rombel: Biasanya A, B, C per tingkat
- Guru: Bisa guru kelas + guru mata pelajaran
- Kurikulum: Mengikuti standar pendidikan nasional

### Sistem Penilaian:
- Fase: Sesuai kurikulum (Fase A, B, C)
- Semester: Ganjil (1) dan Genap (2)
- Komponen: Sumatif TP, LM, dan AS
- KKM: Bisa berbeda per mata pelajaran

### Rapor:
- Format: Mengikuti standar rapor SD
- Data: Lengkap dengan biodata dan nilai
- Output: File Word yang bisa diedit/print
        ";
    }
}