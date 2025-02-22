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
use App\Services\RaporTemplateProcessor;
use Barryvdh\DomPDF\Facade\Pdf;



class ReportController extends Controller
{
    public function index($type = 'UTS')
    {
        try {
            // 1. Validasi tipe rapor
            if (!in_array($type, ['UTS', 'UAS'])) {
                return redirect()->route('report.template.index', 'UTS')
                    ->with('error', 'Tipe rapor tidak valid');
            }
        
            // 2. Mengambil semua template berdasarkan tipe
            $templates = ReportTemplate::where('type', $type)
                ->orderBy('created_at', 'desc')
                ->get();
        
            // 3. Mengambil template yang aktif
            $activeTemplate = ReportTemplate::where([
                'type' => $type,
                'is_active' => true
            ])->first();
    
            // Debug logging
            \Log::info('Report Template Data:', [
                'type' => $type,
                'templates_count' => $templates->count(),
                'templates' => $templates->toArray(),
                'active_template' => $activeTemplate ? $activeTemplate->toArray() : null
            ]);
        
            // 4. Mengirim data ke view
            return view('admin.report.index', [
                'templates' => $templates,
                'type' => $type,
                'activeTemplate' => $activeTemplate
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in report template index:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.report.index', [
                'templates' => collect([]),
                'type' => $type,
                'activeTemplate' => null
            ])->with('error', 'Terjadi kesalahan saat memuat data template');
        }
    }
    
    
    public function getCurrentTemplate(Request $request)
    {
        try {
            $type = $request->type ?? 'UTS';
            
            \Log::info('getCurrentTemplate request:', [
                'type' => $type,
                'all_params' => $request->all()
            ]);
    
            $templates = ReportTemplate::where('type', $type)
                ->orderBy('created_at', 'desc')
                ->get();
    
            $activeTemplate = $templates->where('is_active', true)->first();
    
            \Log::info('getCurrentTemplate response:', [
                'templates_count' => $templates->count(),
                'templates' => $templates->toArray(),
                'active_template' => $activeTemplate ? $activeTemplate->toArray() : null
            ]);
    
            return response()->json([
                'success' => true,
                'templates' => $templates,
                'activeTemplate' => $activeTemplate
            ])->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Error in getCurrentTemplate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil template: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }


    
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'template' => 'required|file|mimes:docx',
                'type' => 'required|in:UTS,UAS',
                'tahun_ajaran' => 'required|string',
                // Ubah validasi semester untuk menerima nilai 1 atau 2
                'semester' => 'required|in:1,2' 
            ]);
    
            DB::beginTransaction();
    
            $file = $request->file('template');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Validate template
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
    
            // Save file to storage and make sure it's public
            $path = 'templates/rapor/' . $filename;
            Storage::disk('public')->put($path, file_get_contents($tempPath));
            
            // Create symbolic link if it doesn't exist
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
    
            // Save record to database
            $template = ReportTemplate::create([
                'filename' => $filename,
                'path' => $path,
                'type' => $request->type,
                'is_active' => false,
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester' => $request->semester // Nilai akan berupa 1 atau 2
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'template' => $template
            ]);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            // Tambahkan pesan khusus untuk error semester
            if (isset($e->errors()['semester'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nilai semester harus 1 (Ganjil) atau 2 (Genap)'
                ], 422);
            }
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
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

    public function downloadPdf(Siswa $siswa) {
        $pdf = PDF::loadView('rapor.pdf', compact('siswa'));
        return $pdf->download("rapor_{$siswa->nis}.pdf");
    }
    
    public function previewRapor($siswa_id) {
        $siswa = Siswa::with([
            'kelas',
            'nilais.mataPelajaran',
            'nilaiEkstrakurikuler.ekstrakurikuler',
            'absensi'
        ])->findOrFail($siswa_id);
    
        // Generate PDF menggunakan dompdf
        $pdf = PDF::loadView('rapor.pdf', compact('siswa'));
        
        return response()->json([
            'success' => true,
            'pdf' => base64_encode($pdf->output())
        ]);
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

    public function generateReport(Request $request, Siswa $siswa)
    {
        try {
            // Validasi akses wali kelas
            if (!$siswa->isInKelasWali(auth()->id())) {
                throw new \Exception('Anda tidak memiliki akses untuk generate rapor siswa ini');
            }
            if (!$siswa->nilais()->exists()) {
                throw new \Exception('Data nilai belum lengkap');
            }
            
            if (!$siswa->absensi) {
                throw new \Exception('Data kehadiran belum lengkap');
            }
    
            // Cek template aktif dengan log
            $template = ReportTemplate::where([
                'type' => $request->type ?? 'UTS',
                'is_active' => true
            ])->first();
    
            \Log::info('Template yang digunakan:', [
                'template' => $template ? $template->toArray() : null
            ]);
    
            if (!$template) {
                throw new \Exception('Template aktif tidak ditemukan');
            }
    
            // Create processor instance langsung di sini
            $processor = new RaporTemplateProcessor($template, $siswa, $request->type ?? 'UTS');
            $result = $processor->generate();
    
            return response()->download(
                storage_path('app/public/' . $result['path']), 
                $result['filename']
            );
    
        } catch (\Exception $e) {
            \Log::error('Error generating report:', [
                'error' => $e->getMessage(),
                'siswa_id' => $siswa->id,
                'type' => $request->type
            ]);
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    public function indexWaliKelas()
        {
            $guru = auth()->user();
            $kelas = $guru->kelasWali;
            
            if (!$kelas) {
                return redirect()->back()->with('error', 'Anda tidak memiliki kelas yang diwalikan');
            }
            
            $siswa = $kelas->siswas()
                ->with(['nilais.mataPelajaran', 'absensi'])
                ->get();
                
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

    public function generateBatchReport(Request $request)
    {
        try {
            $siswaIds = $request->input('siswa_ids', []);
            $type = $request->input('type', 'UTS');
            
            // Validasi siswa
            $guru = auth()->user();
            $kelas = $guru->kelasWali;
            
            if (!$kelas) {
                throw new \Exception('Anda tidak memiliki kelas yang diwalikan');
            }
            
            // Cek template aktif
            $template = ReportTemplate::where([
                'type' => $type,
                'is_active' => true
            ])->firstOrFail();
            
            // Generate rapor untuk setiap siswa
            $files = [];
            foreach ($siswaIds as $siswaId) {
                $siswa = Siswa::find($siswaId);
                if ($siswa && $siswa->kelas_id == $kelas->id) {
                    $processor = new RaporTemplateProcessor($template, $siswa, $type);
                    $result = $processor->generate();
                    $files[] = [
                        'path' => storage_path('app/public/' . $result['path']),
                        'name' => $result['filename']
                    ];
                }
            }
            
            if (empty($files)) {
                throw new \Exception('Tidak ada rapor yang dapat digenerate');
            }
            
            // Buat zip
            $zipName = "rapor_batch_{$kelas->nama_kelas}_{$type}_" . time() . ".zip";
            $zipPath = storage_path("app/public/generated/{$zipName}");
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($files as $file) {
                    $zip->addFile($file['path'], $file['name']);
                }
                $zip->close();
                
                // Hapus file individual setelah di-zip
                foreach ($files as $file) {
                    if (file_exists($file['path'])) {
                        unlink($file['path']);
                    }
                }
                
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            
            throw new \Exception('Gagal membuat file zip');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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
            
            // Ambil semua placeholder dari database
            $validPlaceholders = ReportPlaceholder::pluck('placeholder_key')
                ->toArray();
    
            // Ambil placeholder yang wajib
            $requiredPlaceholders = ReportPlaceholder::where('is_required', true)
                ->pluck('placeholder_key')
                ->toArray();
    
            // Cek placeholder yang tidak ada tapi wajib
            $missingPlaceholders = array_diff($requiredPlaceholders, $existingVariables);
            
            // Cek placeholder yang tidak valid
            $invalidPlaceholders = array_diff($existingVariables, $validPlaceholders);
    
            return [
                'is_valid' => empty($missingPlaceholders) && empty($invalidPlaceholders),
                'missing_placeholders' => array_values($missingPlaceholders),
                'invalid_placeholders' => array_values($invalidPlaceholders)
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
            'nama_siswa' => 'Muhammad Azzam',
            'nisn' => '0123456789',
            'nis' => '210001',
            'kelas' => '6A',
            'tahun_ajaran' => '2024/2025',
            
            // === MATA PELAJARAN ===
            // PAI
            'nilai_pai' => '90',
            'capaian_pai' => 'Sangat baik dalam memahami dan menerapkan nilai-nilai agama Islam dalam kehidupan sehari-hari',
            
            // PPKN
            'nilai_ppkn' => '88',
            'capaian_ppkn' => 'Menunjukkan pemahaman yang baik tentang nilai-nilai Pancasila dan kewarganegaraan',
            
            // Bahasa Indonesia
            'nilai_bahasa_indonesia' => '85',
            'capaian_bahasa_indonesia' => 'Mampu berkomunikasi dengan baik secara lisan dan tulisan dalam Bahasa Indonesia',
            
            // Matematika
            'nilai_matematika' => '92',
            'capaian_matematika' => 'Sangat baik dalam pemecahan masalah matematika dan penerapan konsep perhitungan',
            
            // PJOK
            'nilai_pjok' => '87',
            'capaian_pjok' => 'Aktif dalam kegiatan olahraga dan menunjukkan sportivitas yang baik',
            
            // Seni Musik
            'nilai_seni_musik' => '88',
            
            // Bahasa Inggris
            'nilai_bahasa_inggris' => '86',
            'capaian_bahasa_inggris' => 'Baik dalam memahami dan menggunakan Bahasa Inggris dasar',
            
            
            // === MUATAN LOKAL ===
            'nama_mulok1' => 'Tahfidz',
            'nama_mulok2' => 'Bahasa Arab',
            'nama_mulok3' => 'BTQ',
            'nama_mulok4' => 'Komputer', 
            'nama_mulok5' => 'Conversation',

            // Tahfidz
            'nilai_mulok1' => '89',
            'capaian_mulok1' => 'Hafalan sangat baik dan tajwid yang tepat',
            
            // Bahasa Arab
            'nilai_mulok2' => '85',
            'capaian_mulok2' => 'Mampu memahami kosakata dasar dan percakapan sederhana',
            
            // BTQ
            'nilai_mulok3' => '88',
            'capaian_mulok3' => 'Bacaan Al-Quran lancar dan sesuai tajwid',
            
            // Komputer
            'nilai_mulok4' => '90',
            'capaian_mulok4' => 'Sangat baik dalam mengoperasikan komputer dan aplikasi dasar',
            
            // Conversation
            'nilai_mulok5' => '87',
            'capaian_mulok5' => 'Aktif dalam percakapan Bahasa Inggris sederhana',
            
            // === EKSTRAKURIKULER ===
            'ekskul1_nama' => 'Pramuka',
            'ekskul1_keterangan' => 'Sangat aktif dan menunjukkan jiwa kepemimpinan',
            
            'ekskul2_nama' => 'Tahfidz',
            'ekskul2_keterangan' => 'Berhasil menghafal juz 30 dengan baik',
            
            'ekskul3_nama' => 'Futsal',
            'ekskul3_keterangan' => 'Menunjukkan kerja sama tim yang baik',
            
            'ekskul4_nama' => 'English Club',
            'ekskul4_keterangan' => 'Aktif dalam kegiatan percakapan Bahasa Inggris',
            
            'ekskul5_nama' => 'Seni Kaligrafi',
            'ekskul5_keterangan' => 'Mampu membuat kaligrafi dengan indah',
            
            'ekskul6_nama' => 'Robotika',
            'ekskul6_keterangan' => 'Menunjukkan minat dan kreativitas dalam pemrograman dasar',
            
            // === KEHADIRAN ===
            'sakit' => '2',
            'izin' => '1',
            'tanpa_keterangan' => '0',
            
            // === LAINNYA ===
            'catatan_guru' => 'Siswa menunjukkan perkembangan yang sangat baik dalam akademik maupun perilaku. Perlu ditingkatkan lagi dalam kegiatan diskusi kelompok.',
            'nomor_telepon' => '(021) 7123456'
        ];
    
        // Get available variables in template
        $variables = $template->getVariables();
        
        // Log untuk debugging
        \Log::info('Variables in template:', [
            'found_variables' => $variables
        ]);
    
        // Only set values for variables that exist in template
        foreach ($sampleData as $key => $value) {
            if (in_array($key, $variables)) {
                $template->setValue($key, $value);
            }
        }
        
        // Log placeholders yang tidak ada di sample data
        $missingPlaceholders = array_diff($variables, array_keys($sampleData));
        if (!empty($missingPlaceholders)) {
            \Log::warning('Placeholders without sample data:', [
                'missing' => $missingPlaceholders
            ]);
        }
    }

    public function placeholderGuide()
    {
        try {
            // Get placeholders grouped by category
            $placeholders = ReportPlaceholder::get()
                ->groupBy('category')
                ->map(function($group) {
                    return $group->map(function($item) {
                        return [
                            'key' => $item->placeholder_key,
                            'description' => $item->description,
                            'is_required' => $item->is_required
                        ];
                    });
                });
    
            // Log for debugging
            \Log::info('Placeholders data:', ['data' => $placeholders]);
    
            // Return view with collection
            return view('admin.report.placeholder_guide', [
                'placeholders' => $placeholders
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in placeholderGuide:', ['error' => $e->getMessage()]);
            return view('admin.report.placeholder_guide', [
                'placeholders' => collect([])
            ])->with('error', 'Terjadi kesalahan saat memuat panduan placeholder');
        }
    }
}