<?php

namespace App\Http\Controllers;

use App\Models\FormatRapor;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use Spatie\PdfToImage\Pdf;


class ReportFormatController extends Controller
{
    protected $validPlaceholders = [
        // Informasi Siswa
        '${nama_siswa}',
        '${nisn}',
        '${nis}',
        '${kelas}',
        '${tahun_pelajaran}',
        '${semester}',
        
        // Informasi Sekolah
        '${nama_sekolah}',
        '${alamat_sekolah}',
        '${telepon}',
        '${website}',
        '${email}',
        
        // Ketidakhadiran
        '${sakit}',
        '${izin}',
        '${tanpa_keterangan}',
        
        // Nilai Pelajaran (Dinamis)
        '${mapel_*_nama}',
        '${mapel_*_nilai}',
        '${mapel_*_capaian}',
        
        // Ekstrakurikuler (Dinamis)
        '${ekskul_*_nama}',
        '${ekskul_*_keterangan}',
        
        // Catatan
        '${catatan_guru}',
        
        // Tanda Tangan
        '${kepala_sekolah}',
        '${nip_kepala_sekolah}',
        '${wali_kelas}',
        '${nip_wali_kelas}'
    ];

    public function index($type = 'UTS')
    {
        $formats = FormatRapor::where('type', $type)
                             ->orderBy('created_at', 'desc')
                             ->get();
        
        return view('admin.report_format.index', [
            'type' => $type,
            'formats' => $formats
        ]);
    }
    public function preview(FormatRapor $format)
    {
        try {
            if (!Storage::disk('public')->exists($format->preview_path)) {
                throw new \Exception('Preview tidak tersedia');
            }

            return view('admin.report_preview', [
                'format' => $format
            ]);
        } catch (\Exception $e) {
            Log::error('Preview error: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat preview: ' . $e->getMessage());
        }
    }
    protected function enhanceHtmlStyling($htmlContent)
    {
        // Tambahkan CSS untuk memperbaiki tampilan
        $additionalStyles = '
            <style>
                .document-preview {
                    font-family: "Times New Roman", Times, serif;
                    line-height: 1.6;
                    background: white;
                    padding: 40px;
                    max-width: 210mm; /* A4 width */
                    margin: 0 auto;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .document-preview table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 15px 0;
                }
                .document-preview td, .document-preview th {
                    border: 1px solid #ddd;
                    padding: 8px;
                    vertical-align: top;
                }
                .document-preview p {
                    margin: 10px 0;
                }
                .document-preview h1, .document-preview h2, 
                .document-preview h3, .document-preview h4 {
                    margin: 20px 0 10px;
                    font-weight: bold;
                }
                .document-preview ul, .document-preview ol {
                    margin: 10px 0;
                    padding-left: 20px;
                }
                @media print {
                    .document-preview {
                        box-shadow: none;
                        padding: 0;
                    }
                }
            </style>
        ';

        // Masukkan CSS ke dalam head tag
        $htmlContent = str_replace('</head>', $additionalStyles . '</head>', $htmlContent);
        
        // Bungkus konten dengan div yang memiliki class document-preview
        $htmlContent = str_replace('<body>', '<body><div class="document-preview">', $htmlContent);
        $htmlContent = str_replace('</body>', '</div></body>', $htmlContent);

        return $htmlContent;
    }
    public function upload(Request $request)
    {
        $request->validate([
            'template' => 'required|file|mimes:docx|max:5120',
            'pdf_file' => 'required|file|mimes:pdf|max:5120',
            'type' => 'required|in:UTS,UAS',
            'title' => 'required|string|max:255',
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/']
        ]);
    
        try {
            // Proses DOCX untuk validasi placeholder
            $docxFile = $request->file('template');
            $template = new TemplateProcessor($docxFile->getRealPath());
            
            // Get dan validasi placeholders
            $placeholders = $template->getVariables();
            
            if (empty($placeholders)) {
                return redirect()->back()->with('error', 'Template tidak memiliki placeholder yang valid');
            }
    
            $invalidPlaceholders = $this->getInvalidPlaceholders($placeholders);
            if (!empty($invalidPlaceholders)) {
                return redirect()->back()
                    ->with('error', 'Template memiliki placeholder yang tidak valid: ' . implode(', ', $invalidPlaceholders));
            }
    
            // Simpan kedua file
            $filename = Str::slug($request->title) . '_' . time();
            $docxPath = $docxFile->storeAs('templates/rapor', $filename . '.docx', 'public');
            $pdfPath = $request->file('pdf_file')->storeAs('templates/rapor', $filename . '.pdf', 'public');
    
            // Create format record dengan placeholders sebagai JSON string
            FormatRapor::create([
                'type' => $request->type,
                'title' => $request->title,
                'template_path' => $docxPath,
                'pdf_path' => $pdfPath,
                'tahun_ajaran' => $request->tahun_ajaran,
                'placeholders' => json_encode(array_values($placeholders)), // Encode sebagai JSON
                'is_active' => false
            ]);
    
            return redirect()->back()->with('success', 'Format rapor berhasil diupload');
        } catch (\Exception $e) {
            Log::error('Upload failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal mengupload format: ' . $e->getMessage());
        }
    }
    
    public function validatePlaceholders(Request $request)
    {
        $request->validate([
            'template' => 'required|file|mimes:docx'
        ]);
    
        try {
            $file = $request->file('template');
            $template = new TemplateProcessor($file->getRealPath());
            
            $placeholders = $template->getVariables();
            $invalidPlaceholders = $this->getInvalidPlaceholders($placeholders);
            
            return response()->json([
                'valid' => empty($invalidPlaceholders),
                'invalid_placeholders' => $invalidPlaceholders,
                'available_placeholders' => $this->validPlaceholders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }


    protected function getInvalidPlaceholders($placeholders)
    {
        $invalidPlaceholders = [];
        foreach ($placeholders as $placeholder) {
            // Remove ${} from placeholder for validation
            $cleanPlaceholder = trim($placeholder, '${}');
            if (!$this->isValidPlaceholder($cleanPlaceholder)) {
                $invalidPlaceholders[] = $placeholder;
            }
        }
        return $invalidPlaceholders;
    }
    

    protected function isValidPlaceholder($placeholder)
    {
        $staticPlaceholders = [
            'nama_siswa', 'nisn', 'nis', 'kelas', 'tahun_pelajaran',
            'semester', 'nama_sekolah', 'alamat_sekolah', 'telepon',
            'website', 'email', 'sakit', 'izin', 'tanpa_keterangan',
            'catatan_guru', 'kepala_sekolah', 'nip_kepala_sekolah',
            'wali_kelas', 'nip_wali_kelas'
        ];
    
        // Check static placeholders
        if (in_array($placeholder, $staticPlaceholders)) {
            return true;
        }
    
        // Check dynamic placeholders
        $dynamicPatterns = [
            '/^mapel_\d+_(nama|nilai|capaian)$/',
            '/^ekskul_\d+_(nama|keterangan)$/'
        ];
    
        foreach ($dynamicPatterns as $pattern) {
            if (preg_match($pattern, $placeholder)) {
                return true;
            }
        }
    
        return false;
    }

    

    protected function generatePreview($templatePath, $filename)
    {
        try {
            $previewDir = storage_path('app/public/previews/rapor');
            if (!file_exists($previewDir)) {
                mkdir($previewDir, 0777, true);
            }
            
            // Simpan preview path sebagai string, bukan array
            $previewPath = 'previews/rapor/' . $filename;
            return $previewPath;
    
        } catch (\Exception $e) {
            Log::error('Preview generation error: ' . $e->getMessage());
            return null;
        }
    }

    public function activate(FormatRapor $format)
    {
        // Deactivate other formats of same type
        FormatRapor::where('type', $format->type)
                  ->where('id', '!=', $format->id)
                  ->update(['is_active' => false]);
        
        $format->update(['is_active' => true]);
        
        return redirect()->back()->with('success', 'Format rapor berhasil diaktifkan');
    }

    public function destroy(FormatRapor $format)
    {
        Storage::delete($format->template_path);
        if ($format->preview_path) {
            Storage::delete($format->preview_path);
        }
        
        $format->delete();
        
        return redirect()->back()->with('success', 'Format rapor berhasil dihapus');
    }

    
}