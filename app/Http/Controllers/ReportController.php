<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportTemplate;
use App\Models\ReportPlaceholder;
use App\Models\Siswa;
use App\Services\RaporTemplateProcessor;
use Illuminate\Support\Facades\Storage;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Add this import for DB facade

class ReportController extends Controller
{
    // Modify the index method to pass school profile to the view
    public function index()
    {
        // Get all templates (both UTS and UAS) sorted by creation date
        $templates = ReportTemplate::orderBy('created_at', 'desc')
            ->get();
        
        $schoolProfile = ProfilSekolah::first();
        
        return view('admin.report.index', compact('templates', 'schoolProfile'));
    }
    // Modify the upload method to use school profile data
    /**
     * Upload a new template and validate placeholders.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $request->validate([
            'template' => 'required|file|mimes:docx',
            'type' => 'required|in:UTS,UAS',
            'tahun_ajaran' => 'required',
            'semester' => 'required|in:1,2',
        ]);
        
        try {
            $file = $request->file('template');
            $filename = $file->getClientOriginalName();
            $tempPath = $file->store('temp', 'public');
            $fullTempPath = storage_path('app/public/' . $tempPath);
            
            // Validate template placeholders
            $validationResult = $this->validateTemplate($fullTempPath, $request->type);
            
            if (!$validationResult['is_valid']) {
                // Delete the temporary file
                Storage::disk('public')->delete($tempPath);
                
                // Prepare error message
                $errorMessage = 'Template tidak valid: ';
                
                if (!empty($validationResult['missing_placeholders'])) {
                    $errorMessage .= 'Placeholder wajib yang tidak ditemukan: ' . 
                        implode(', ', $validationResult['missing_placeholders']);
                }
                
                if (!empty($validationResult['invalid_placeholders'])) {
                    $errorMessage .= (!empty($validationResult['missing_placeholders']) ? '. ' : '') . 
                        'Placeholder tidak valid: ' . implode(', ', $validationResult['invalid_placeholders']);
                }
                
                if (isset($validationResult['error'])) {
                    $errorMessage .= 'Error: ' . $validationResult['error'];
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }
            
            // Move from temp to final location
            $path = 'templates/' . time() . '_' . $filename;
            Storage::disk('public')->move($tempPath, $path);
            
            $template = ReportTemplate::create([
                'filename' => $filename,
                'path' => $path,
                'type' => $request->type,
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester' => $request->semester,
                'is_active' => false, // Default inactive
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diupload',
                'template' => $template
            ]);
        } catch (\Exception $e) {
            \Log::error('Upload template error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload template: ' . $e->getMessage()
            ], 500);
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

     /**
 * Download sample template with correct placeholders
 * 
 * @param Request $request
 * @return \Illuminate\Http\Response
 */
public function downloadSampleTemplate(Request $request)
{
    $type = $request->input('type', 'UTS');
    
    // Ensure valid type
    if (!in_array($type, ['UTS', 'UAS'])) {
        $type = 'UTS';
    }
    
    // Define the correct file path based on type
    $sampleFileName = $type === 'UTS' 
        ? 'sample_template_uts.docx'
        : 'sample_template_uas.docx';
        
    $filePath = storage_path('app/public/samples/' . $sampleFileName);
    
    // Check if the file exists
    if (!file_exists($filePath)) {
        // If the specific file doesn't exist, try to use a generic sample
        $filePath = storage_path('app/public/samples/template_rapor_sample.docx');
        
        // If even the generic sample doesn't exist, create it
        if (!file_exists($filePath)) {
            // Make sure the directory exists
            if (!is_dir(storage_path('app/public/samples'))) {
                mkdir(storage_path('app/public/samples'), 0755, true);
            }
            
            // Copy from the original template file if it exists
            if ($type === 'UTS') {
                // Copy from a known template or create minimal template
                $this->createSampleTemplate($filePath, $type);
            } else {
                // Create minimal UAS template
                $this->createSampleTemplate($filePath, $type);
            }
        }
    }
    
    // Generate a filename for download
    $downloadFilename = "template_{$type}_sample.docx";
    
    // Return the file for download
    return response()->download($filePath, $downloadFilename);
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

    protected function validateTemplate($filePath, $type = 'UTS')
    {
        try {
            $phpWord = new \PhpOffice\PhpWord\TemplateProcessor($filePath);
            $existingVariables = $phpWord->getVariables();
            
            // Get required placeholders for specified type
            $requiredPlaceholders = $this->getRequiredPlaceholders($type);
            
            // Get all valid placeholders
            $validPlaceholders = ReportPlaceholder::pluck('placeholder_key')->toArray();
            
            // Check for missing required placeholders
            $missingPlaceholders = array_diff($requiredPlaceholders, $existingVariables);
            
            // Check for unknown/invalid placeholders
            $invalidPlaceholders = array_diff($existingVariables, $validPlaceholders);
            
            return [
                'is_valid' => empty($missingPlaceholders),
                'missing_placeholders' => $missingPlaceholders,
                'invalid_placeholders' => $invalidPlaceholders,
            ];
        } catch (\Exception $e) {
            \Log::error('Template validation error:', [
                'error' => $e->getMessage(),
                'file_path' => $filePath
            ]);
            
            return [
                'is_valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }


/**
 * Create a basic sample template if none exists
 *
 * @param string $outputPath
 * @param string $type
 * @return void
 */
protected function createSampleTemplate($outputPath, $type = 'UTS')
{
    try {
        // Create a new Word document
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Add a section
        $section = $phpWord->addSection();
        
        // Add header with school name
        $header = $section->addHeader();
        $header->addText('TEMPLATE RAPOR ' . $type, ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        
        // Add student information
        $section->addText('IDENTITAS SISWA:', ['bold' => true, 'size' => 14]);
        $section->addText('Nama: ${nama_siswa}', ['size' => 12]);
        $section->addText('NISN: ${nisn}', ['size' => 12]);
        $section->addText('NIS: ${nis}', ['size' => 12]);
        $section->addText('Kelas: ${kelas}', ['size' => 12]);
        $section->addText('Tahun Ajaran: ${tahun_ajaran}', ['size' => 12]);
        
        $section->addTextBreak(1);
        
        // Add subject information
        $section->addText('NILAI MATA PELAJARAN:', ['bold' => true, 'size' => 14]);
        
        // Create table for subjects
        $table = $section->addTable();
        
        // Add header row
        $table->addRow();
        $table->addCell(2000)->addText('Mata Pelajaran', ['bold' => true]);
        $table->addCell(1000)->addText('Nilai', ['bold' => true]);
        $table->addCell(5000)->addText('Capaian Kompetensi', ['bold' => true]);
        
        // PAI
        $table->addRow();
        $table->addCell(2000)->addText('Pendidikan Agama Islam');
        $table->addCell(1000)->addText('${nilai_pai}');
        $table->addCell(5000)->addText('${capaian_pai}');
        
        // Matematika
        $table->addRow();
        $table->addCell(2000)->addText('Matematika');
        $table->addCell(1000)->addText('${nilai_matematika}');
        $table->addCell(5000)->addText('${capaian_matematika}');
        
        // Bahasa Indonesia
        $table->addRow();
        $table->addCell(2000)->addText('Bahasa Indonesia');
        $table->addCell(1000)->addText('${nilai_bahasa_indonesia}');
        $table->addCell(5000)->addText('${capaian_bahasa_indonesia}');
        
        $section->addTextBreak(1);
        
        // Add attendance information
        $section->addText('KEHADIRAN:', ['bold' => true, 'size' => 14]);
        $section->addText('Sakit: ${sakit} hari', ['size' => 12]);
        $section->addText('Izin: ${izin} hari', ['size' => 12]);
        $section->addText('Tanpa Keterangan: ${tanpa_keterangan} hari', ['size' => 12]);
        
        // Save to file
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($outputPath);
        
        return true;
    } catch (\Exception $e) {
        \Log::error('Error creating sample template:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return false;
    }
}
   
    /**
     * Get required placeholders based on template type
     * 
     * @param string $type
     * @return array
     */
    protected function getRequiredPlaceholders($type = 'UTS')
    {
        // Basic required placeholders for all template types
        $required = [
            'nama_siswa',
            'nisn',
            'nis',
            'kelas',
            'tahun_ajaran',
            'nilai_matematika',
            'sakit'
        ];
        
        // Add UTS specific placeholders
        if ($type === 'UTS') {
            // Currently no additional UTS-specific required placeholders
        }
        
        // Add UAS specific placeholders 
        if ($type === 'UAS') {
            // Additional required placeholders for UAS
            // $required[] = 'additional_uas_placeholder';
        }
        
        return $required;
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