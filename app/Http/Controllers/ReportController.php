<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use App\Models\ReportPlaceholder;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\Absensi;
use App\Models\NilaiEkstrakurikuler;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index($type = 'UTS')
    {
        if (!in_array($type, ['UTS', 'UAS'])) {
            return redirect()->route('report.template.index', 'UTS')
                ->with('error', 'Tipe rapor tidak valid');
        }

        $templates = ReportTemplate::where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.report.index', compact('templates', 'type'));
    }

    public function getCurrentTemplate(Request $request)
    {
        try {
            $template = ReportTemplate::where('type', $request->type)
                ->where('is_active', true)
                ->first();

            return response()->json([
                'success' => true,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'template' => 'required|file|mimes:docx',
            'type' => 'required|in:UTS,UAS'
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('template');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Validasi template
            $tempPath = $file->getRealPath();
            $validation = $this->validateTemplate($tempPath);
            
            if (!$validation['is_valid']) {
                $message = "Template tidak valid:\n";
                if (!empty($validation['missing_placeholders'])) {
                    $message .= "Placeholder yang wajib tapi tidak ada:\n- " . 
                        implode("\n- ", $validation['missing_placeholders']) . "\n";
                }
                if (!empty($validation['invalid_placeholders'])) {
                    $message .= "Placeholder yang tidak dikenal:\n- " . 
                        implode("\n- ", $validation['invalid_placeholders']);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }

            // Simpan file
            $path = $file->storeAs('templates/rapor', $filename, 'public');

            // Simpan record ke database
            $template = ReportTemplate::create([
                'filename' => $filename,
                'path' => $path,
                'type' => $request->type,
                'is_active' => false,
                'tahun_ajaran' => session('tahun_ajaran'),
                'semester' => session('semester')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'template' => $template
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadSampleTemplate()
    {
        $path = storage_path('app/public/samples/template_rapor_sample.docx');
        return response()->download($path);
    }
    public function previewWaliKelas(Siswa $siswa)
    {
        $nilaiRapor = $siswa->nilaiRapor()
            ->with('mataPelajaran')
            ->get();
        
        return view('wali_kelas.rapor.preview', compact('siswa', 'nilaiRapor'));
    }

    public function preview(ReportTemplate $template)
    {
        try {
            $filePath = storage_path('app/public/' . $template->path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File template tidak ditemukan'
                ], 404);
            }

            // Load template
            $phpWord = new TemplateProcessor($filePath);
            
            // Isi dengan data sample
            $this->fillTemplateSampleData($phpWord);
            
            // Generate preview file
            $previewPath = storage_path('app/public/preview_' . $template->filename);
            $phpWord->saveAs($previewPath);

            return response()->download($previewPath)->deleteFileAfterSend();

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat preview: ' . $e->getMessage()
            ], 500);
        }
    }

    public function indexWaliKelas()
    {
        $siswa = auth()->user()->kelasWali->siswas;
        return view('wali_kelas.rapor.index', compact('siswa'));
    }

    public function activate(ReportTemplate $template)
    {
        try {
            DB::beginTransaction();

            // Nonaktifkan semua template dengan tipe yang sama
            ReportTemplate::where('type', $template->type)
                ->where('id', '!=', $template->id)
                ->update(['is_active' => false]);

            // Aktifkan template yang dipilih
            $template->update(['is_active' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diaktifkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ReportTemplate $template)
    {
        try {
            DB::beginTransaction();

            // Hapus file
            if (Storage::disk('public')->exists($template->path)) {
                Storage::disk('public')->delete($template->path);
            }

            // Hapus record
            $template->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus template: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function validateTemplate($filePath)
    {
        try {
            $phpWord = new TemplateProcessor($filePath);
            $existingVariables = $phpWord->getVariables();
            
            // Load required placeholders
            $requiredPlaceholders = ReportPlaceholder::where('is_required', true)
                ->pluck('placeholder_key')
                ->toArray();

            // Get all valid placeholders
            $validPlaceholders = ReportPlaceholder::pluck('placeholder_key')
                ->toArray();

            // Check missing required placeholders
            $missingPlaceholders = array_diff($requiredPlaceholders, $existingVariables);
            
            // Check invalid placeholders
            $invalidPlaceholders = array_diff($existingVariables, $validPlaceholders);

            return [
                'is_valid' => empty($missingPlaceholders) && empty($invalidPlaceholders),
                'missing_placeholders' => $missingPlaceholders,
                'invalid_placeholders' => $invalidPlaceholders
            ];

        } catch (\Exception $e) {
            return [
                'is_valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function fillTemplateSampleData($template)
    {
        $sampleData = [
            // Data Siswa
            'nama_siswa' => 'John Doe',
            'nisn' => '1234567890',
            'nis' => '987654321',
            'kelas' => '6A',
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '15 Juni 2012',
            
            // Data Orang Tua
            'nama_ayah' => 'James Doe',
            'nama_ibu' => 'Jane Doe',
            'pekerjaan_ayah' => 'Wiraswasta',
            'pekerjaan_ibu' => 'Guru',
            
            // Nilai Akademik (contoh untuk beberapa mata pelajaran)
            'nilai_matematika_tp1' => '85',
            'nilai_matematika_tp2' => '88',
            'nilai_matematika_akhir' => '87',
            'predikat_matematika' => 'A',
            'capaian_matematika' => 'Sangat baik dalam pemahaman konsep matematika',
            
            // Kehadiran
            'sakit' => '2',
            'izin' => '1',
            'tanpa_keterangan' => '0',
            
            // Dan seterusnya...
        ];

        foreach ($sampleData as $key => $value) {
            if ($template->valueExists($key)) {
                $template->setValue($key, $value);
            }
        }
    }

    public function placeholderGuide()
    {
        $placeholders = [
            'siswa' => [
                ['key' => 'nama_siswa', 'description' => 'Nama lengkap siswa'],
                ['key' => 'nis', 'description' => 'Nomor Induk Siswa'],
                ['key' => 'nisn', 'description' => 'NISN'],
                ['key' => 'kelas', 'description' => 'Nama kelas'],
                ['key' => 'jenis_kelamin', 'description' => 'Jenis kelamin siswa'],
                ['key' => 'tempat_lahir', 'description' => 'Tempat lahir siswa'],
                ['key' => 'tanggal_lahir', 'description' => 'Tanggal lahir siswa']
            ],
            'orangtua' => [
                ['key' => 'nama_ayah', 'description' => 'Nama ayah siswa'],
                ['key' => 'nama_ibu', 'description' => 'Nama ibu siswa'],
                ['key' => 'pekerjaan_ayah', 'description' => 'Pekerjaan ayah'],
                ['key' => 'pekerjaan_ibu', 'description' => 'Pekerjaan ibu'],
                ['key' => 'alamat_ortu', 'description' => 'Alamat orang tua'],
                ['key' => 'wali_siswa', 'description' => 'Nama wali siswa (jika ada)'],
                ['key' => 'pekerjaan_wali', 'description' => 'Pekerjaan wali (jika ada)']
            ],
            'nilai' => [
                ['key' => 'nilai_matematika_tp1', 'description' => 'Nilai TP 1 Matematika'],
                ['key' => 'nilai_matematika_tp2', 'description' => 'Nilai TP 2 Matematika'],
                ['key' => 'nilai_matematika_akhir', 'description' => 'Nilai Akhir Matematika'],
                ['key' => 'predikat_matematika', 'description' => 'Predikat Nilai Matematika'],
                ['key' => 'capaian_matematika', 'description' => 'Deskripsi Capaian Matematika'],
                // Tambahkan mata pelajaran lain
            ],
            'ekskul' => [
                ['key' => 'ekskul1_nama', 'description' => 'Nama Ekstrakurikuler 1'],
                ['key' => 'ekskul1_nilai', 'description' => 'Nilai Ekstrakurikuler 1'],
                ['key' => 'ekskul1_deskripsi', 'description' => 'Deskripsi Ekstrakurikuler 1'],
                ['key' => 'ekskul2_nama', 'description' => 'Nama Ekstrakurikuler 2'],
                ['key' => 'ekskul2_nilai', 'description' => 'Nilai Ekstrakurikuler 2'],
                ['key' => 'ekskul2_deskripsi', 'description' => 'Deskripsi Ekstrakurikuler 2']
            ],
            'kehadiran' => [
                ['key' => 'sakit', 'description' => 'Jumlah hari tidak hadir karena sakit'],
                ['key' => 'izin', 'description' => 'Jumlah hari tidak hadir karena izin'],
                ['key' => 'tanpa_keterangan', 'description' => 'Jumlah hari tidak hadir tanpa keterangan'],
                ['key' => 'total_absen', 'description' => 'Total ketidakhadiran']
            ],
            'sekolah' => [
                ['key' => 'nama_sekolah', 'description' => 'Nama sekolah'],
                ['key' => 'kepala_sekolah', 'description' => 'Nama kepala sekolah'],
                ['key' => 'nip_kepsek', 'description' => 'NIP kepala sekolah'],
                ['key' => 'wali_kelas', 'description' => 'Nama wali kelas'],
                ['key' => 'nip_wali', 'description' => 'NIP wali kelas'],
                ['key' => 'tahun_ajaran', 'description' => 'Tahun ajaran'],
                ['key' => 'semester', 'description' => 'Semester']
            ]
        ];

        \Log::info('Placeholders data:', $placeholders);

    
        return view('admin.report.placeholder_guide', [
            'placeholders' => $placeholders
        ]);
    }
}