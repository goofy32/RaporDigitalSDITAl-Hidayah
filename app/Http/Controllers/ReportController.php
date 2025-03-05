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
use Barryvdh\DomPDF\Facade\PDF;

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

    // Di ReportController.php, tambahkan method ini:
    public function checkActiveTemplates()
    {
        // Cek template UTS aktif
        $utsActive = ReportTemplate::where([
            'type' => 'UTS',
            'is_active' => true
        ])->exists();
        
        // Cek template UAS aktif
        $uasActive = ReportTemplate::where([
            'type' => 'UAS',
            'is_active' => true
        ])->exists();
        
        return response()->json([
            'UTS_active' => $utsActive,
            'UAS_active' => $uasActive
        ]);
    }

    /**
     * Buat template sampel dinamis dengan placeholder terbaru
     * 
     * @param string $outputPath Path untuk menyimpan file
     * @param string $type Tipe template (UTS/UAS)
     * @return bool
     */
    protected function createDynamicSampleTemplate($outputPath, $type = 'UTS')
    {
        try {
            // Create a new Word document
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            
            // Add styles
            $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 16], ['alignment' => 'center']);
            $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14], ['alignment' => 'center']);
            
            // Add a section
            $section = $phpWord->addSection();
            
            // Add header with school info
            $header = $section->addHeader();
            $header->addText('PEMERINTAH KABUPATEN', ['bold' => true], ['alignment' => 'center']);
            $header->addText('KOORDINATOR WILAYAH DIKPORA KECAMATAN', ['bold' => true], ['alignment' => 'center']);
            $header->addText('SD IT AL-HIDAYAH LOGAM', ['bold' => true, 'size' => 14], ['alignment' => 'center']);
            $header->addText('Telp. ${nomor_telepon}', [], ['alignment' => 'center']);
            
            // Add title
            $section->addTitle($type === 'UTS' ? 'RAPOR TENGAH SEMESTER 1' : 'RAPOR AKHIR SEMESTER', 1);
            
            // Add student info
            $tableStyle = ['borderSize' => 6, 'borderColor' => '000000'];
            $cellStyle = ['valign' => 'center'];
            
            $infoTable = $section->addTable($tableStyle);
            
            $infoTable->addRow();
            $infoTable->addCell(2000, $cellStyle)->addText('Nama Siswa');
            $infoTable->addCell(500, $cellStyle)->addText(':');
            $infoTable->addCell(3000, $cellStyle)->addText('${nama_siswa}');
            $infoTable->addCell(1500, $cellStyle)->addText('Kelas');
            $infoTable->addCell(500, $cellStyle)->addText(':');
            $infoTable->addCell(1500, $cellStyle)->addText('${kelas}');
            
            $infoTable->addRow();
            $infoTable->addCell(2000, $cellStyle)->addText('NISN/NIS');
            $infoTable->addCell(500, $cellStyle)->addText(':');
            $infoTable->addCell(3000, $cellStyle)->addText('${nisn}/${nis}');
            $infoTable->addCell(1500, $cellStyle)->addText('Tahun Pelajaran');
            $infoTable->addCell(500, $cellStyle)->addText(':');
            $infoTable->addCell(1500, $cellStyle)->addText('${tahun_ajaran}');
            
            $section->addTextBreak(1);
            
            // Add mata pelajaran table
            $section->addText('DAFTAR NILAI MATA PELAJARAN', ['bold' => true], ['alignment' => 'center']);
            
            $mapelTable = $section->addTable($tableStyle);
            
            // Table header
            $mapelTable->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
            $mapelTable->addCell(600, ['bgColor' => 'D3D3D3'])->addText('No.', ['bold' => true], ['alignment' => 'center']);
            $mapelTable->addCell(3000, ['bgColor' => 'D3D3D3'])->addText('Mata Pelajaran', ['bold' => true], ['alignment' => 'center']);
            $mapelTable->addCell(1000, ['bgColor' => 'D3D3D3'])->addText('Nilai', ['bold' => true], ['alignment' => 'center']);
            $mapelTable->addCell(5000, ['bgColor' => 'D3D3D3'])->addText('Capaian Kompetensi', ['bold' => true], ['alignment' => 'center']);
            
            // Add mata pelajaran rows (dynamic placeholders)
            for ($i = 1; $i <= 7; $i++) {
                $mapelTable->addRow();
                $mapelTable->addCell(600)->addText($i, [], ['alignment' => 'center']);
                $mapelTable->addCell(3000)->addText('${nama_matapelajaran' . $i . '}');
                $mapelTable->addCell(1000)->addText('${nilai_matapelajaran' . $i . '}', [], ['alignment' => 'center']);
                $mapelTable->addCell(5000)->addText('${capaian_matapelajaran' . $i . '}');
            }
            
            $section->addTextBreak(1);
            
            // Add muatan lokal table
            $section->addText('MUATAN LOKAL', ['bold' => true], ['alignment' => 'center']);
            
            $mulokTable = $section->addTable($tableStyle);
            
            // Table header
            $mulokTable->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
            $mulokTable->addCell(600, ['bgColor' => 'D3D3D3'])->addText('No.', ['bold' => true], ['alignment' => 'center']);
            $mulokTable->addCell(3000, ['bgColor' => 'D3D3D3'])->addText('Muatan Lokal', ['bold' => true], ['alignment' => 'center']);
            $mulokTable->addCell(1000, ['bgColor' => 'D3D3D3'])->addText('Nilai', ['bold' => true], ['alignment' => 'center']);
            $mulokTable->addCell(5000, ['bgColor' => 'D3D3D3'])->addText('Capaian Kompetensi', ['bold' => true], ['alignment' => 'center']);
            
            // Add muatan lokal rows
            for ($i = 1; $i <= 5; $i++) {
                $mulokTable->addRow();
                $mulokTable->addCell(600)->addText($i, [], ['alignment' => 'center']);
                $mulokTable->addCell(3000)->addText('${nama_mulok' . $i . '}');
                $mulokTable->addCell(1000)->addText('${nilai_mulok' . $i . '}', [], ['alignment' => 'center']);
                $mulokTable->addCell(5000)->addText('${capaian_mulok' . $i . '}');
            }
            
            $section->addTextBreak(1);
            
            // Add ekstrakurikuler table
            $section->addText('EKSTRAKURIKULER', ['bold' => true], ['alignment' => 'center']);
            
            $ekskulTable = $section->addTable($tableStyle);
            
            // Table header
            $ekskulTable->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
            $ekskulTable->addCell(600, ['bgColor' => 'D3D3D3'])->addText('No.', ['bold' => true], ['alignment' => 'center']);
            $ekskulTable->addCell(3000, ['bgColor' => 'D3D3D3'])->addText('Kegiatan Ekstrakurikuler', ['bold' => true], ['alignment' => 'center']);
            $ekskulTable->addCell(6000, ['bgColor' => 'D3D3D3'])->addText('Keterangan', ['bold' => true], ['alignment' => 'center']);
            
            // Add ekstrakurikuler rows
            for ($i = 1; $i <= 5; $i++) {
                $ekskulTable->addRow();
                $ekskulTable->addCell(600)->addText($i, [], ['alignment' => 'center']);
                $ekskulTable->addCell(3000)->addText('${ekskul' . $i . '_nama}');
                $ekskulTable->addCell(6000)->addText('${ekskul' . $i . '_keterangan}');
            }
            
            $section->addTextBreak(1);
            
            // Add catatan guru
            $section->addText('CATATAN GURU', ['bold' => true], ['alignment' => 'center']);
            
            $catatanTable = $section->addTable($tableStyle);
            $catatanTable->addRow(800);
            $catatanTable->addCell(9600)->addText('${catatan_guru}');
            
            $section->addTextBreak(1);
            
            // Add ketidakhadiran
            $section->addText('KETIDAKHADIRAN', ['bold' => true], ['alignment' => 'center']);
            
            $kehadiranTable = $section->addTable($tableStyle);
            
            $kehadiranTable->addRow();
            $kehadiranTable->addCell(9600)->addText('Sakit : ${sakit} Hari');
            
            $kehadiranTable->addRow();
            $kehadiranTable->addCell(9600)->addText('Izin : ${izin} Hari');
            
            $kehadiranTable->addRow();
            $kehadiranTable->addCell(9600)->addText('Tanpa Keterangan : ${tanpa_keterangan} Hari');
            
            $section->addTextBreak(2);
            
            // Add signature section
            $signatureTable = $section->addTable();
            $signatureTable->addRow();
            $signatureTable->addCell(3000)->addText('Mengetahui:', ['bold' => true], ['alignment' => 'center']);
            $signatureTable->addCell(3000);
            $signatureTable->addCell(3000);
            
            $signatureTable->addRow();
            $signatureTable->addCell(3000)->addText('Orang Tua/Wali,', ['bold' => true], ['alignment' => 'center']);
            $signatureTable->addCell(3000)->addText('Kepala Sekolah,', ['bold' => true], ['alignment' => 'center']);
            $signatureTable->addCell(3000)->addText('Wali Kelas,', ['bold' => true], ['alignment' => 'center']);
            
            $signatureTable->addRow(1000); // Space for signature
            $signatureTable->addCell(3000);
            $signatureTable->addCell(3000);
            $signatureTable->addCell(3000);
            
            $signatureTable->addRow();
            $signatureTable->addCell(3000)->addText('____________________');
            $signatureTable->addCell(3000)->addText('${kepala_sekolah}');
            $signatureTable->addCell(3000)->addText('${wali_kelas}');
            
            $signatureTable->addRow();
            $signatureTable->addCell(3000);
            $signatureTable->addCell(3000)->addText('NIP. ${nip_kepala_sekolah}');
            $signatureTable->addCell(3000)->addText('NIP. ${nip_wali_kelas}');
            
            // Save to file
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            
            // Ensure the directory exists
            $dir = dirname($outputPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $objWriter->save($outputPath);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error creating dynamic sample template:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
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
        
        // Use dynamic sample template
        $sampleFileName = $type === 'UTS' 
            ? 'dynamic_template_uts.docx'
            : 'dynamic_template_uas.docx';
            
        $filePath = storage_path('app/public/samples/' . $sampleFileName);
        
        // If the file doesn't exist, create it
        if (!file_exists($filePath)) {
            $this->createDynamicSampleTemplate($filePath, $type);
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
        try {
            $siswa = Siswa::with([
                'kelas',
                'nilais.mataPelajaran',
                'nilaiEkstrakurikuler.ekstrakurikuler',
                'absensi'
            ])->findOrFail($siswa_id);
        
            // Generate preview HTML dulu
            $html = view('rapor.preview', compact('siswa'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in preview rapor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat preview rapor: ' . $e->getMessage()
            ], 500);
        }
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
            // Dapatkan data guru dan siswa
            $guru = auth()->user();
            $guruId = $guru->id;
            $siswaKelasId = $siswa->kelas_id;
            
            // Log untuk debugging
            \Log::info('Generating report', [
                'guru_id' => $guruId,
                'siswa_id' => $siswa->id,
                'siswa_kelas_id' => $siswaKelasId
            ]);
            
            // Cek akses dengan query langsung ke tabel pivot
            $isWaliKelas = \DB::table('guru_kelas')
                ->where('guru_id', $guruId)
                ->where('kelas_id', $siswaKelasId)
                ->where('is_wali_kelas', 1)
                ->where('role', 'wali_kelas')
                ->exists();
                
            \Log::info('Wali kelas check result', ['is_wali_kelas' => $isWaliKelas]);
            
            // Validasi akses
            if (!$isWaliKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk generate rapor siswa ini'
                ], 403);
            }
    
            // Cek template aktif
            $template = \App\Models\ReportTemplate::where([
                'type' => $request->type ?? 'UTS',
                'is_active' => true
            ])->first();
    
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template aktif tidak ditemukan. Hubungi admin untuk mengaktifkan template.'
                ], 400);
            }
    
            // Generate rapor
            $processor = new \App\Services\RaporTemplateProcessor($template, $siswa, $request->type ?? 'UTS');
            $result = $processor->generate();
    
            return response()->download(
                storage_path('app/public/' . $result['path']), 
                $result['filename']
            );
        } catch (\Exception $e) {
            \Log::error('Error generating report:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'siswa_id' => $siswa->id,
                'type' => $request->type
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate rapor: ' . $e->getMessage()
            ], 500);
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
            
            // Get all valid placeholders from database
            $validPlaceholders = ReportPlaceholder::pluck('placeholder_key')->toArray();
            
            // Add dynamic placeholder patterns for regex validation
            $dynamicPatterns = [
                'nama_matapelajaran\d+',
                'nilai_matapelajaran\d+',
                'capaian_matapelajaran\d+',
                'nama_mulok\d+',
                'nilai_mulok\d+',
                'capaian_mulok\d+',
                'ekskul\d+_nama',
                'ekskul\d+_keterangan'
            ];
            
            // Minimum required placeholders (minimal harus ada beberapa placeholder dasar)
            $requiredPlaceholders = [
                'nama_siswa', 
                'nisn', 
                'nis', 
                'kelas',
                'tahun_ajaran'
            ];
            
            // Check if required placeholders exist
            $missingPlaceholders = [];
            foreach ($requiredPlaceholders as $required) {
                if (!in_array($required, $existingVariables)) {
                    $missingPlaceholders[] = $required;
                }
            }
            
            // Check if each template variable is valid
            $invalidPlaceholders = [];
            foreach ($existingVariables as $var) {
                $isValid = in_array($var, $validPlaceholders);
                
                if (!$isValid) {
                    // Check if it matches a dynamic pattern
                    $isDynamic = false;
                    foreach ($dynamicPatterns as $pattern) {
                        if (preg_match('/^' . $pattern . '$/', $var)) {
                            $isDynamic = true;
                            break;
                        }
                    }
                    
                    if (!$isDynamic) {
                        $invalidPlaceholders[] = $var;
                    }
                }
            }
            
            return [
                'is_valid' => empty($missingPlaceholders) && empty($invalidPlaceholders),
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