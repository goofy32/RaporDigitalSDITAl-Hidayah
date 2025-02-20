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
                'type' => 'required|in:UTS,UAS'
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
                'tahun_ajaran' => session('tahun_ajaran'),
                'semester' => session('semester')
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'template' => $template
            ]);
    
        } catch (ValidationException $e) {
            DB::rollBack();
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