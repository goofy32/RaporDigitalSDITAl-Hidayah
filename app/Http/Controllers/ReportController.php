<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportTemplate;
use App\Models\ReportPlaceholder;
use App\Models\Siswa;
use App\Models\Notification;
use App\Services\RaporTemplateProcessor;
use Illuminate\Support\Facades\Storage;
use App\Models\ProfilSekolah;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Add this import for DB facade
use Barryvdh\DomPDF\Facade\PDF;
use App\Models\ReportGeneration;

class ReportController extends Controller
{
    // Modify the index method to pass school profile to the view
    public function index()
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        
        $templates = ReportTemplate::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->with('kelas')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $schoolProfile = \App\Models\ProfilSekolah::first();
        
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
            'kelas_id' => 'nullable|exists:kelas,id',
            'tahun_ajaran' => 'required',
            'semester' => 'required|in:1,2'
        ]);
    
        try {
            // Proses upload file
            $file = $request->file('template');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('templates', $fileName, 'public');
            
            // Validasi placeholder dalam template
            $templatePath = storage_path('app/public/' . $filePath);
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
            $variables = $templateProcessor->getVariables();
            
            // Validasi placeholder wajib
            $requiredPlaceholders = \App\Models\ReportPlaceholder::where('is_required', true)
                ->where(function($query) use ($request) {
                    if ($request->type === 'UTS') {
                        $query->where('category', '!=', 'uas_only');
                    } else {
                        $query->where('category', '!=', 'uts_only');
                    }
                })
                ->pluck('placeholder_key')
                ->toArray();
            
            $missingPlaceholders = array_diff($requiredPlaceholders, $variables);
            if (count($missingPlaceholders) > 0) {
                // Hapus file yang sudah diupload
                \Storage::disk('public')->delete($filePath);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Template tidak valid. Placeholder wajib yang tidak ditemukan: ' . implode(', ', $missingPlaceholders)
                ], 422);
            }
            
            // Simpan data dengan kelas_id
            $template = ReportTemplate::create([
                'filename' => $fileName,
                'path' => $filePath,
                'type' => $request->type,
                'kelas_id' => $request->kelas_id ?: null, 
                'is_active' => false, 
                'tahun_ajaran' => $request->tahun_ajaran,
                'semester' => $request->semester,
                'tahun_ajaran_id' => $request->tahun_ajaran_id
            ]);
    
            // Kirim notifikasi ke admin dan wali kelas terkait (jika ada)
            if ($request->kelas_id) {
                $kelas = \App\Models\Kelas::find($request->kelas_id);
                if ($kelas) {
                    $waliKelasId = $kelas->getWaliKelasId();
                    if ($waliKelasId) {
                        $notification = new Notification();
                        $notification->title = "Template Rapor {$request->type} Baru Tersedia";
                        $notification->content = "Template rapor {$request->type} baru untuk kelas {$kelas->nama_kelas} telah diunggah. " .
                                               "Template ini belum aktif. Hubungi admin untuk mengaktifkannya.";
                        $notification->target = 'specific';
                        $notification->specific_users = [$waliKelasId];
                        $notification->save();
                    }
                }
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diunggah'
            ]);
        } catch (\Exception $e) {
            // Jika terjadi error saat memproses template, hapus file yang sudah diupload
            if (isset($filePath) && \Storage::disk('public')->exists($filePath)) {
                \Storage::disk('public')->delete($filePath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function archiveByTahunAjaran(Request $request)
    {
        $tahunAjaranId = $request->input('tahun_ajaran_id', session('tahun_ajaran_id'));
        
        $reports = ReportGeneration::when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->with(['siswa', 'kelas', 'generator'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $tahunAjarans = TahunAjaran::orderBy('tanggal_mulai', 'desc')->get();
        
        return view('admin.report.history', compact('reports', 'tahunAjarans', 'tahunAjaranId'));
    }

    public function tutorialView()
    {
        return view('admin.report.tutorial');
    }

    // Di ReportController.php, tambahkan method ini:
    public function checkActiveTemplates(Request $request)
    {
        $kelasId = auth()->user()->kelasWali->id ?? null;
        
        if (!$kelasId) {
            return response()->json([
                'UTS_active' => false,
                'UAS_active' => false,
                'error' => 'Tidak ditemukan kelas yang diwalikan'
            ]);
        }
        
        // Cek template aktif untuk UTS di kelas spesifik
        $utsTemplate = ReportTemplate::where('type', 'UTS')
            ->where('kelas_id', $kelasId)
            ->where('is_active', true)
            ->exists();
            
        // Cek template aktif untuk UAS di kelas spesifik
        $uasTemplate = ReportTemplate::where('type', 'UAS')
            ->where('kelas_id', $kelasId)
            ->where('is_active', true)
            ->exists();
        
        return response()->json([
            'UTS_active' => $utsTemplate,
            'UAS_active' => $uasTemplate
        ]);
    }
    /**
     * Menampilkan history rapor
     * 
     * @return \Illuminate\View\View
     */
    public function history()
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Ambil data history rapor dari tabel report_generations
        $reports = ReportGeneration::with(['siswa', 'kelas', 'generator'])
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.report.history', compact('reports'));
    }
    /**
     * Download rapor dari history
     * 
     * @param ReportGeneration $report
     * @return \Illuminate\Http\Response
     */
    public function downloadHistory(ReportGeneration $report)
    {
        $path = storage_path('app/public/' . $report->generated_file);
        
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File rapor tidak ditemukan.');
        }
        
        $fileName = 'rapor_' . $report->siswa->nis . '_' . $report->type . '.docx';
        
        return response()->download($path, $fileName);
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


    public function previewData(ReportTemplate $template)
    {
        try {
            $filePath = storage_path('app/public/' . $template->path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File template tidak ditemukan'
                ], 404);
            }

            // Return raw file content for docx.js processing
            $content = file_get_contents($filePath);
            
            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'inline; filename="' . $template->filename . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat preview data: ' . $e->getMessage()
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
            // Cari siswa dengan relasi yang dibutuhkan
            $siswa = Siswa::with([
                'kelas',
                'nilais.mataPelajaran',
                'nilaiEkstrakurikuler.ekstrakurikuler',
                'absensi'
            ])->findOrFail($siswa_id);
            
            // Render view ke HTML
            $html = view('wali_kelas.rapor.preview', compact('siswa'))->render();
            
            // Kembalikan sebagai JSON response
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error in previewRapor: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Kirim respon error
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
    
            // Create a temporary public URL that Office Viewer can access
            $tempPublicPath = 'temp_previews/' . $template->id . '_' . time() . '_' . basename($template->path);
            Storage::disk('public')->put($tempPublicPath, file_get_contents($filePath));
            
            // Return the temporary public URL for the docx file
            $publicUrl = Storage::disk('public')->url($tempPublicPath);
            
            return response()->file(storage_path('app/public/' . $tempPublicPath), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'inline; filename="' . $template->filename . '"'
            ]);
            
            // Optional: Schedule cleanup of temp file
            // You might want to add a job to clean up these temporary files
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat preview: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateReport(Request $request, Siswa $siswa)
    {
        $type = $request->input('type', 'UTS');
        $action = $request->input('action', 'download');
        $tahunAjaranId = $request->input('tahun_ajaran_id', session('tahun_ajaran_id')); // Ambil dari request atau session
        
        try {
            // Dapatkan template yang sesuai untuk kelas siswa dengan filter tahun ajaran
            $template = $this->getTemplateForSiswa($siswa, $type, $tahunAjaranId); // Tambahkan parameter tahun ajaran
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada template aktif untuk jenis rapor ' . $type . ' pada kelas ini di tahun ajaran yang dipilih.'
                ], 404);
            }
            
            // Cek kelengkapan data
            $hasData = $siswa->hasCompleteData($type, $tahunAjaranId); // Tambahkan parameter tahun ajaran
            $bypassValidation = $request->input('bypass_validation', false);
            
            if (!$hasData && !$bypassValidation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa belum lengkap untuk menghasilkan rapor di tahun ajaran ini.',
                    'error_type' => 'data_incomplete'
                ], 422);
            }
            
            // Proses generate rapor
            $processor = new \App\Services\RaporTemplateProcessor($template, $siswa, $type, $tahunAjaranId); // Sertakan tahun ajaran
            $result = $processor->generate($bypassValidation);
            
            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Gagal generate rapor');
            }
            
            // Simpan history generate dengan tahun ajaran
            $this->saveGenerationHistory($siswa, $template, $type, $tahunAjaranId);
            
            // Jika hanya preview, kembalikan URL
            if ($action == 'preview') {
                return response()->json([
                    'success' => true,
                    'file_url' => asset('storage/' . $result['path']),
                    'filename' => $result['filename']
                ]);
            }
            
            // Download file
            $fullPath = storage_path('app/public/' . $result['path']);
            
            return response()->download($fullPath, $result['filename']);
        } 
        catch (\App\Exceptions\RaporException $e) {
            \Log::error('RaporException in generateReport: ' . $e->getMessage(), [
                'siswa_id' => $siswa->id,
                'type' => $type,
                'tahun_ajaran_id' => $tahunAjaranId,
                'error_type' => $e->getErrorType(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => $e->getErrorType()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Error in generateReport: ' . $e->getMessage(), [
                'siswa_id' => $siswa->id,
                'type' => $type,
                'tahun_ajaran_id' => $tahunAjaranId,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat generate rapor: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function saveGenerationHistory(Siswa $siswa, ReportTemplate $template, $type, $tahunAjaranId = null)
    {
        try {
            \App\Models\ReportGeneration::create([
                'siswa_id' => $siswa->id,
                'kelas_id' => $siswa->kelas_id,
                'report_template_id' => $template->id,
                'generated_file' => null, // File tidak disimpan secara permanen
                'type' => $type,
                'tahun_ajaran' => $template->tahun_ajaran,
                'semester' => $template->semester,
                'tahun_ajaran_id' => $tahunAjaranId ?: session('tahun_ajaran_id'), // Tambahkan tahun ajaran ID
                'generated_at' => now(),
                'generated_by' => auth()->id() ?? auth()->guard('guru')->id()
            ]);
        } catch (\Exception $e) {
            // Hanya log error, tidak mempengaruhi proses utama
            \Log::error('Error saving generation history: ' . $e->getMessage(), [
                'siswa_id' => $siswa->id,
                'template_id' => $template->id,
                'tahun_ajaran_id' => $tahunAjaranId
            ]);
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
            // Cek apakah template ini sudah aktif
            if ($template->is_active) {
                // Jika sudah aktif, berarti ini adalah request untuk menonaktifkan
                $template->update(['is_active' => false]);
                return response()->json([
                    'success' => true,
                    'message' => 'Template berhasil dinonaktifkan',
                    'status' => 'inactive'
                ]);
            }
    
            // Jika belum aktif, maka akan diaktifkan
            // Pertama, nonaktifkan semua template dengan tipe dan kelas yang sama
            ReportTemplate::where('type', $template->type)
                ->where('kelas_id', $template->kelas_id)
                ->update(['is_active' => false]);
            
            // Kemudian, aktifkan template yang dipilih
            $template->update(['is_active' => true]);
            
            // Kirim notifikasi ke wali kelas terkait
            if ($template->kelas_id) {
                $kelas = \App\Models\Kelas::find($template->kelas_id);
                if ($kelas) {
                    $waliKelasId = $kelas->getWaliKelasId();
                    if ($waliKelasId) {
                        $notification = new Notification();
                        $notification->title = "Template Rapor {$template->type} Diaktifkan";
                        $notification->content = "Template rapor {$template->type} untuk kelas {$kelas->nama_kelas} telah diaktifkan. " .
                                               "Anda sekarang dapat menghasilkan rapor untuk siswa-siswa di kelas Anda.";
                        $notification->target = 'specific';
                        $notification->specific_users = [$waliKelasId];
                        $notification->save();
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diaktifkan',
                'status' => 'active'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error activating template: ' . $e->getMessage(), [
                'template_id' => $template->id,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status template: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate batch report for multiple students
     */
    public function generateBatchReport(Request $request)
    {
        try {
            $siswaIds = $request->input('siswa_ids', []);
            $type = $request->input('type', 'UTS');
            $tahunAjaranId = session('tahun_ajaran_id');
            
            // Validasi siswa
            $guru = auth()->guard('guru')->user();
            $kelas = $guru->kelasWali;
            
            if (!$kelas) {
                throw new \Exception('Anda tidak memiliki kelas yang diwalikan');
            }
            
            // Validasi siswa IDs - pastikan semua siswa di kelas ini
            if (empty($siswaIds)) {
                throw new \Exception('Tidak ada siswa yang dipilih');
            }
            
            // Verifikasi semua siswa valid dan berada di kelas wali
            $siswaList = Siswa::whereIn('id', $siswaIds)
                ->where('kelas_id', $kelas->id)
                ->get();
                
            if ($siswaList->count() !== count($siswaIds)) {
                throw new \Exception('Beberapa siswa tidak ditemukan atau bukan dari kelas Anda');
            }
            
            // Cek template aktif dengan filter tahun ajaran
            $template = ReportTemplate::where([
                'type' => $type,
                'is_active' => true,
                'kelas_id' => $kelas->id,
            ])->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })->first();
            
            if (!$template) {
                // Cek template global
                $template = ReportTemplate::where([
                    'type' => $type,
                    'is_active' => true,
                ])->whereNull('kelas_id')
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })->first();
                
                if (!$template) {
                    throw new \Exception("Tidak ada template {$type} aktif untuk kelas ini di tahun ajaran yang dipilih");
                }
            }
            
            // Array untuk menyimpan hasil proses
            $successSiswa = [];
            $errorSiswa = [];
            $files = [];
            
            // Lakukan proses generate secara batched untuk menghindari timeout
            foreach ($siswaList as $index => $siswa) {
                try {
                    // Validasi data siswa lengkap
                    $semester = $type === 'UTS' ? 1 : 2;
                    
                    // Cek nilai dengan filter tahun ajaran
                    $hasNilai = $siswa->nilais()
                        ->whereHas('mataPelajaran', function($q) use ($semester) {
                            $q->where('semester', $semester);
                        })
                        ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                            return $query->where('tahun_ajaran_id', $tahunAjaranId);
                        })
                        ->where('nilai_akhir_rapor', '!=', null)
                        ->exists();
                        
                    // Cek kehadiran untuk semester yang sesuai dengan filter tahun ajaran
                    $hasAbsensi = $siswa->absensi()
                        ->where('semester', $semester)
                        ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                            return $query->where('tahun_ajaran_id', $tahunAjaranId);
                        })
                        ->exists();
                        
                    if (!$hasNilai || !$hasAbsensi) {
                        throw new \Exception("Data nilai atau kehadiran belum lengkap");
                    }
                    
                    // Generate rapor
                    $processor = new \App\Services\RaporTemplateProcessor($template, $siswa, $type, $tahunAjaranId);
                    $result = $processor->generate();
                    
                    $files[] = [
                        'path' => storage_path('app/public/' . $result['path']),
                        'name' => $result['filename']
                    ];
                    
                    // Simpan history generate
                    \App\Models\ReportGeneration::create([
                        'siswa_id' => $siswa->id,
                        'kelas_id' => $siswa->kelas_id,
                        'report_template_id' => $template->id,
                        'generated_file' => $result['path'],
                        'type' => $type,
                        'tahun_ajaran' => $template->tahun_ajaran,
                        'semester' => $template->semester,
                        'tahun_ajaran_id' => $tahunAjaranId,
                        'generated_at' => now(),
                        'generated_by' => $guru->id
                    ]);
                    
                    // Update tracking
                    $successSiswa[] = [
                        'id' => $siswa->id,
                        'name' => $siswa->nama,
                        'filename' => $result['filename']
                    ];
                    
                } catch (\Exception $e) {
                    // Log error
                    \Log::error("Error generating report for siswa {$siswa->id} ({$siswa->nama}): " . $e->getMessage());
                    
                    // Update tracking
                    $errorSiswa[] = [
                        'id' => $siswa->id,
                        'name' => $siswa->nama,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            // Cek jika tidak ada rapor yang berhasil dibuat
            if (empty($files)) {
                throw new \Exception('Tidak ada rapor yang dapat digenerate. ' . implode("\n", array_column($errorSiswa, 'error')));
            }
            
            // Buat zip file
            $zipName = "rapor_batch_{$kelas->nama_kelas}_{$type}_" . time() . ".zip";
            $zipPath = storage_path("app/public/generated/{$zipName}");
            
            // Pastikan direktori ada
            if (!file_exists(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }
            
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                // Tambahkan report summary
                $summaryContent = "# Laporan Generate Batch Rapor\n\n";
                $summaryContent .= "Tanggal: " . date('Y-m-d H:i:s') . "\n";
                $summaryContent .= "Kelas: {$kelas->nama_kelas}\n";
                $summaryContent .= "Tipe Rapor: $type\n";
                $summaryContent .= "Tahun Ajaran: " . ($template->tahunAjaran ? $template->tahunAjaran->tahun_ajaran : $template->tahun_ajaran) . "\n\n";
                
                $summaryContent .= "## Ringkasan\n";
                $summaryContent .= "Total Siswa: " . count($siswaIds) . "\n";
                $summaryContent .= "Berhasil: " . count($successSiswa) . "\n";
                $summaryContent .= "Gagal: " . count($errorSiswa) . "\n\n";
                
                if (!empty($successSiswa)) {
                    $summaryContent .= "## Siswa Berhasil\n";
                    foreach ($successSiswa as $index => $siswa) {
                        $summaryContent .= ($index + 1) . ". {$siswa['name']} - {$siswa['filename']}\n";
                    }
                    $summaryContent .= "\n";
                }
                
                if (!empty($errorSiswa)) {
                    $summaryContent .= "## Siswa Gagal\n";
                    foreach ($errorSiswa as $index => $siswa) {
                        $summaryContent .= ($index + 1) . ". {$siswa['name']} - {$siswa['error']}\n";
                    }
                }
                
                // Tulis file summary
                $summaryPath = storage_path("app/public/generated/summary_{$kelas->nama_kelas}_{$type}_" . time() . ".md");
                file_put_contents($summaryPath, $summaryContent);
                
                // Tambahkan file summary ke zip
                $zip->addFile($summaryPath, "RINGKASAN_RAPOR.md");
                
                // Tambahkan semua file ke zip
                foreach ($files as $file) {
                    if (file_exists($file['path'])) {
                        $zip->addFile($file['path'], $file['name']);
                    } else {
                        \Log::warning("File not found: {$file['path']}");
                    }
                }
                
                $zip->close();
                
                // Hapus file individual dan summary setelah di-zip
                foreach ($files as $file) {
                    if (file_exists($file['path'])) {
                        @unlink($file['path']);
                    }
                }
                @unlink($summaryPath);
                
                // Buat notifikasi sukses untuk wali kelas
                $notification = new Notification();
                $notification->title = "Batch Rapor {$type} Kelas {$kelas->nama_kelas} Siap Diunduh";
                $notification->content = "Generate batch rapor {$type} untuk kelas {$kelas->nama_kelas} telah selesai. " . 
                                "Berhasil: " . count($successSiswa) . " siswa, " . 
                                "Gagal: " . count($errorSiswa) . " siswa. " .
                                "Silahkan unduh file ZIP dari halaman history rapor.";
                $notification->target = 'specific';
                $notification->specific_users = [$guru->id];
                $notification->save();
                
                // Return download response
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            
            throw new \Exception('Gagal membuat file zip');
            
        } catch (\Exception $e) {
            \Log::error("Batch generate report error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Buat notifikasi error khusus untuk wali kelas
            if (isset($guru) && $guru) {
                $notification = new Notification();
                $notification->title = "Gagal Generate Batch Rapor {$type}";
                $notification->content = "Terjadi kesalahan saat membuat batch rapor {$type}: " . $e->getMessage() . 
                                        ". Silahkan coba lagi atau hubungi admin jika masalah berlanjut.";
                $notification->target = 'specific';
                $notification->specific_users = [$guru->id];
                $notification->save();
            }
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getTemplateForSiswa(Siswa $siswa, $type)
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // First look for class-specific template
        $template = ReportTemplate::where('type', $type)
            ->where('kelas_id', $siswa->kelas_id)
            ->where('is_active', true)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->first();
        
        // If not found, look for global template
        if (!$template) {
            $template = ReportTemplate::where('type', $type)
                ->whereNull('kelas_id')
                ->where('is_active', true)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->first();
        }
        
        return $template;
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
            
            // Placeholder yang harus ada untuk UTS
            $utsRequiredPlaceholders = [
                'nama_siswa', 
                'nisn', 
                'nis', 
                'kelas',
                'tahun_ajaran',
                'sakit'
            ];
            
            // Placeholder yang harus ada untuk UAS
            $uasRequiredPlaceholders = [
                'nama_siswa', 
                'nisn', 
                'nis', 
                'kelas',
                'tahun_ajaran',
                'sakit',
                'nama_sekolah',
                'alamat_sekolah'
                // Tambahkan placeholder lain yang harus ada di UAS
            ];
            
            // Pilih daftar placeholder yang diperlukan berdasarkan jenis template
            $requiredPlaceholders = ($type === 'UTS') ? $utsRequiredPlaceholders : $uasRequiredPlaceholders;
            
            // Tambahkan validator untuk memastikan template tidak memiliki placeholder dari jenis lain
            // Misalnya, placeholder UAS tidak boleh ada di template UTS
            $uasSpecificPlaceholders = ['tempat_lahir', 'jenis_kelamin', 'agama', 'alamat_wali'];
            if ($type === 'UTS' && count(array_intersect($existingVariables, $uasSpecificPlaceholders)) > 0) {
                return [
                    'is_valid' => false,
                    'message' => 'Template UTS tidak boleh mengandung placeholder UAS'
                ];
            }
            
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