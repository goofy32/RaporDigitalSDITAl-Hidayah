<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    private $importErrors = [];

    public function model(array $row)
    {
        // Tambahkan pengecekan dan validasi tambahan
        if (!$this->validateRow($row)) {
            return null;
        }

        try {

            Log::info('Processing row:', $row);

            $kelasParts = explode(' - ', $row['kelas']);
            $nomor_kelas = trim($kelasParts[0]);
            $nama_kelas = trim($kelasParts[1]);
            $wali_kelas = $row['wali_kelas'];
    
            $kelas = Kelas::firstOrCreate(
                ['nomor_kelas' => $nomor_kelas, 'nama_kelas' => $nama_kelas],
                ['wali_kelas' => $wali_kelas]
            );


            // Generate nama foto unik jika kosong
            $photoName = $row['photo'] ?? Str::slug($row['nama']) . '_' . uniqid() . '.png';

            // Proses data siswa
            return new Siswa([
                'nis' => $row['nis'],
                'nisn' => $row['nisn'],
                'nama' => $row['nama'],
                'tanggal_lahir' => $this->convertTanggalLahir($row['tanggal_lahir']),
                'jenis_kelamin' => $row['jenis_kelamin'],
                'agama' => $row['agama'],
                'alamat' => $row['alamat'],
                'kelas_id' => $kelas->id,
                'nama_ayah' => $row['nama_ayah'],
                'nama_ibu' => $row['nama_ibu'],
                'pekerjaan_ayah' => $row['pekerjaan_ayah'],
                'pekerjaan_ibu' => $row['pekerjaan_ibu'],
                'alamat_orangtua' => $row['alamat_orangtua'],
                'photo' => $photoName,
                'wali_kelas' => $wali_kelas,
            ]);

            Log::info('Siswa to be saved:', $siswa->toArray());

            return $siswa;
        } catch (\Exception $e) {
            Log::error('Import Model Error: ' . $e->getMessage());
            Log::error('Row data: ' . json_encode($row));
            Log::error('Trace: ' . $e->getTraceAsString());
    
            $this->importErrors[] = "Kesalahan pada baris: " . $e->getMessage();
            return null;
        }
    }

    private function validateRow(array $row): bool
    {
        $requiredFields = [
            'nis', 'nisn', 'nama', 'kelas', 'tanggal_lahir', 
            'jenis_kelamin', 'agama', 'alamat', 
            'nama_ayah', 'nama_ibu', 'pekerjaan_ayah', 
            'pekerjaan_ibu', 'alamat_orangtua'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($row[$field]) || empty(trim($row[$field]))) {
                $this->importErrors[] = "Kolom {$field} kosong atau tidak valid";
                return false;
            }
        }

        // Validasi format kelas
        if (!preg_match('/^\d+ - [a-zA-Z\s]+$/', $row['kelas'])) {
            $this->importErrors[] = "Format kelas tidak valid: " . $row['kelas'];
            return false;
        }

        return true;
    }

    protected function convertTanggalLahir($tanggal)
    {
        try {
            if (is_numeric($tanggal)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->importErrors[] = "Format tanggal lahir tidak valid: $tanggal";
            return now(); // Fallback ke tanggal hari ini
        }
    }

    public function rules(): array
    {
        return [
            'kelas' => 'required|regex:/^\d+ - [a-zA-Z\s]+$/',
            'nis' => 'required|unique:siswas,nis',
            'nisn' => 'required|unique:siswas,nisn',
            'nama' => 'required',
            'tanggal_lahir' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required',
            'alamat' => 'required',
            'nama_ayah' => 'required',
            'nama_ibu' => 'required',
            'pekerjaan_ayah' => 'required',
            'pekerjaan_ibu' => 'required',
            'alamat_orangtua' => 'required',
            'wali_kelas' => 'required',
        ];
    }

    public function getErrors()
    {
        return $this->importErrors;
    }
}