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
    private $rowCount = 0;

    private function isEmptyRow(array $row): bool
    {
        return count(array_filter($row, function($value) {
            return !empty($value) || $value === '0';
        })) === 0;
    }

    public function model(array $row)
    {
        try {
            // 1. Log data mentah yang diterima
            \Log::info('Attempting to process row:', $row);
            
            // 2. Validasi data
            if (!isset($row['nis']) || !isset($row['nisn']) || !isset($row['nama']) || !isset($row['kelas'])) {
                \Log::warning('Required fields missing:', array_keys($row));
                return null;
            }
    
            // 3. Proses kelas
            $kelasParts = explode(' - ', $row['kelas']);
            if (count($kelasParts) !== 2) {
                \Log::error('Invalid kelas format', ['kelas' => $row['kelas']]);
                return null;
            }
    
            $kelas = Kelas::firstOrCreate(
                [
                    'nomor_kelas' => trim($kelasParts[0]),
                    'nama_kelas' => trim($kelasParts[1])
                ]
            );
    
            // 4. Log info kelas
            \Log::info('Kelas created/found:', [
                'id' => $kelas->id,
                'nomor' => $kelas->nomor_kelas,
                'nama' => $kelas->nama_kelas
            ]);
    
            // 5. Buat siswa
            $siswa = new Siswa([
                'nis' => $row['nis'],
                'nisn' => $row['nisn'],
                'nama' => $row['nama'],
                'tanggal_lahir' => \Carbon\Carbon::parse($row['tanggal_lahir'])->format('Y-m-d'),
                'jenis_kelamin' => $row['jenis_kelamin'],
                'agama' => $row['agama'],
                'alamat' => $row['alamat'],
                'kelas_id' => $kelas->id,
                'nama_ayah' => $row['nama_ayah'],
                'nama_ibu' => $row['nama_ibu'],
                'pekerjaan_ayah' => $row['pekerjaan_ayah'],
                'pekerjaan_ibu' => $row['pekerjaan_ibu'],
                'alamat_orangtua' => $row['alamat_orangtua'],
                'photo' => $row['photo'] ?? null
            ]);
    
            // 6. Log data siswa sebelum disimpan
            \Log::info('Attempting to save student:', $siswa->toArray());
    
            // 7. Simpan dan log hasilnya
            if ($siswa->save()) {
                \Log::info('Student saved successfully:', [
                    'id' => $siswa->id,
                    'nis' => $siswa->nis
                ]);
            } else {
                \Log::error('Failed to save student');
            }
    
            $this->rowCount++;
            return $siswa;
    
        } catch (\Exception $e) {
            \Log::error('Exception in model():', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'row' => $row
            ]);
            return null;
        }
    }
    
    public function headingRow(): int
    {
        return 1; // Row pertama adalah header
    }
    
    public function rules(): array
    {
        return [
            '*.nis' => ['required', 'distinct'],
            '*.nisn' => ['required', 'distinct'],
            '*.nama' => ['required'],
            '*.kelas' => ['required'],
            '*.tanggal_lahir' => ['required'],
            '*.jenis_kelamin' => ['required'],
            '*.agama' => ['required'],
            '*.alamat' => ['required']
        ];
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    public function getErrors()
    {
        return $this->importErrors;
    }
}