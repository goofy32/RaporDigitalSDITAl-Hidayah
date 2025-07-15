<?php

namespace App\Http\Controllers;

use App\Models\GeminiChat;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Guru;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatController extends Controller
{
    private $systemContext;

    public function __construct()
    {
        // Load knowledge base dari PDF atau file teks
        $this->systemContext = $this->loadKnowledgeBase();
    }

    private function getUserId()
    {
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->id();
        } elseif (Auth::guard('guru')->check()) {
            return Auth::guard('guru')->id();
        }
        return null;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->message;
        
        Log::info('User message received: ' . $userMessage);
        Log::info('Knowledge base length: ' . strlen($this->systemContext));
        
        $apiKey = env('GEMINI_API_KEY');
        
        if (!$apiKey) {
            Log::error('GEMINI_API_KEY not found in .env');
            return response()->json([
                'success' => false,
                'message' => 'API key tidak ditemukan. Periksa konfigurasi.'
            ], 500);
        }

        // Analisis intent dan ambil data
        $intent = $this->analyzeUserIntent($userMessage);
        
        if ($intent === 'knowledge_base') {
            $databaseData = ['message' => 'Menggunakan knowledge base untuk panduan sistem'];
        } else {
            $databaseData = $this->fetchNilaiAnalysisData($intent, $userMessage);
        }

        // Buat contextual prompt
        $contextualPrompt = $this->buildNilaiAnalysisPrompt($userMessage, $databaseData, $intent);
        
        // Log prompt yang dikirim
        Log::info('Contextual prompt: ' . substr($contextualPrompt, 0, 500) . '...');
        
        // HANYA SATU KALI: Panggil sendWithRetry (sudah include retry logic)
        $apiResponse = $this->sendWithRetry($contextualPrompt, $userMessage);
        
        if (!$apiResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => $apiResponse['message'] ?? 'Terjadi kesalahan saat memproses permintaan'
            ], $apiResponse['status'] ?? 500);
        }

        // Log final response
        Log::info('Final AI response: ' . $apiResponse['data']);

        // Simpan chat ke database
        $chat = GeminiChat::create([
            'user_id' => $this->getUserId(),
            'message' => $userMessage,
            'response' => $apiResponse['data']
        ]);

        return response()->json([
            'success' => true,
            'response' => $apiResponse['data'],
            'chat' => $chat,
            'model_used' => $apiResponse['model_used'] ?? 'unknown',
            'fallback' => $apiResponse['fallback'] ?? false
        ]);
    }

    private function sendWithRetry($contextualPrompt, $userMessage)
    {
        $apiKey = env('GEMINI_API_KEY');
        
        // STRATEGI MULTI-MODEL: Coba model berbeda jika ada masalah
        $models = [
            'gemini-1.5-flash-latest',
            'gemini-1.5-flash-8b-latest',
            'gemini-1.5-pro-latest'
        ];
        
        $maxRetries = 3;
        $baseDelay = 2; // detik
        
        foreach ($models as $modelIndex => $model) {
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    Log::info("Attempt {$attempt} with model: {$model}");
                    
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                    
                    $response = Http::timeout(30)->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $contextualPrompt
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.3,
                            'maxOutputTokens' => $model === 'gemini-1.5-pro-latest' ? 2000 : 1000,
                            'topP' => 0.8,
                            'topK' => 40,
                        ]
                    ]);

                    Log::info("Response status: {$response->status()} for model: {$model}");
                    
                    // SUCCESS CASE
                    if ($response->successful()) {
                        $data = $response->json();
                        $aiResponse = $this->extractResponse($data);
                        
                        if ($aiResponse) {
                            $cleanResponse = $this->cleanResponse($aiResponse);
                            Log::info("Success with model {$model} on attempt {$attempt}");
                            
                            return [
                                'success' => true,
                                'data' => $cleanResponse,
                                'model_used' => $model,
                                'attempt' => $attempt
                            ];
                        }
                    }
                    
                    // HANDLE SPECIFIC ERRORS
                    $errorData = $response->json();
                    $errorCode = $response->status();
                    $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
                    
                    Log::warning("Error {$errorCode} with {$model}: {$errorMessage}");
                    
                    // 503 Service Unavailable - Retry dengan delay
                    if ($errorCode === 503) {
                        if ($attempt < $maxRetries) {
                            $delay = $baseDelay * pow(2, $attempt - 1); // Exponential backoff
                            Log::info("Model {$model} overloaded, retrying in {$delay}s...");
                            sleep($delay);
                            continue;
                        }
                        // Jika sudah max retry, coba model berikutnya
                        Log::info("Max retries reached for {$model}, trying next model");
                        break;
                    }
                    
                    // 429 Rate Limit - Retry dengan delay lebih lama
                    if ($errorCode === 429) {
                        if ($attempt < $maxRetries) {
                            $delay = 10 * $attempt; // 10, 20, 30 detik
                            Log::info("Rate limited, waiting {$delay}s...");
                            sleep($delay);
                            continue;
                        }
                        break;
                    }
                    
                    // 400 Bad Request - Jangan retry, langsung coba model lain
                    if ($errorCode === 400) {
                        Log::warning("Bad request for {$model}, trying next model");
                        break;
                    }
                    
                    // Other errors - Try next attempt
                    if ($attempt < $maxRetries) {
                        sleep($baseDelay);
                        continue;
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Exception with {$model} attempt {$attempt}: " . $e->getMessage());
                    
                    if ($attempt < $maxRetries) {
                        sleep($baseDelay);
                        continue;
                    }
                }
            }
        }
        
        // FALLBACK: Jika semua model gagal
        return $this->getFallbackResponse($userMessage);
    }

    private function analyzeUserIntent($message)
    {
        $message = strtolower(trim($message));
        
        // Cek Knowledge Base intent dulu
        if ($this->isKnowledgeBaseQuestion($message)) {
            return 'knowledge_base';
        }
        
        // Lalu cek Nilai Analysis intent
        return $this->analyzeNilaiIntent($message);
    }

    private function isKnowledgeBaseQuestion($message)
    {
        $knowledgeKeywords = [
            'cara', 'bagaimana', 'login', 'error', 'masalah', 'setup', 
            'tahun ajaran', 'template', 'troubleshoot', 'tidak bisa',
            'panduan', 'help', 'bantuan', 'menu', 'akses', 'duplikat'
        ];
        
        $knowledgePatterns = [
            '/cara.*login/', '/bagaimana.*setup/', '/error.*/', 
            '/tidak.*bisa/', '/masalah.*/', '/panduan.*/',
            '/duplikat.*tahun/', '/template.*rapor/'
        ];
        
        foreach ($knowledgeKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        foreach ($knowledgePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function testKnowledgeBase()
    {
        $knowledgeFile = storage_path('app/knowledge/rapor_sdit_guide.txt');
        
        return response()->json([
            'file_exists' => file_exists($knowledgeFile),
            'file_path' => $knowledgeFile,
            'file_size' => file_exists($knowledgeFile) ? filesize($knowledgeFile) : 0,
            'content_length' => strlen($this->systemContext),
            'first_100_chars' => substr($this->systemContext, 0, 100),
            'api_key_exists' => !empty(env('GEMINI_API_KEY')),
            'api_key_length' => strlen(env('GEMINI_API_KEY') ?? ''),
        ]);
    }

    public function debugTest()
    {
        try {
            // Test basic logging
            Log::info('=== GEMINI DEBUG TEST START ===');
            Log::info('Current time: ' . now());
            Log::info('User ID: ' . (Auth::id() ?? 'Not authenticated'));
            Log::info('Knowledge base loaded: ' . (strlen($this->systemContext) > 0 ? 'YES' : 'NO'));
            Log::info('Knowledge base length: ' . strlen($this->systemContext));
            Log::info('API Key exists: ' . (!empty(env('GEMINI_API_KEY')) ? 'YES' : 'NO'));
            Log::info('=== GEMINI DEBUG TEST END ===');
            
            return response()->json([
                'success' => true,
                'message' => 'Debug test completed. Check logs at: ' . storage_path('logs'),
                'log_files' => glob(storage_path('logs/*.log')),
                'current_time' => now(),
                'knowledge_base_length' => strlen($this->systemContext)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    private function loadKnowledgeBase()
    {
        // Baca konten dari file knowledge base
        $knowledgeFile = storage_path('app/knowledge/rapor_sdit_guide.txt');
        
        if (file_exists($knowledgeFile)) {
            return file_get_contents($knowledgeFile);
        }

        // Fallback ke knowledge base default
        return $this->getDefaultKnowledgeBase();
    }

    private function buildContextualPrompt($userMessage)
    {
        $systemPrompt = "Anda adalah asisten AI yang membantu pengguna sistem RAPOR SDIT Al-Hidayah. Anda memiliki pengetahuan lengkap tentang sistem ini berdasarkan panduan berikut:

=== KNOWLEDGE BASE SISTEM RAPOR SDIT ===
{$this->systemContext}
=== END KNOWLEDGE BASE ===

INSTRUKSI PENTING:
1. Jawab pertanyaan berdasarkan knowledge base di atas
2. Jika pertanyaan tidak terkait sistem RAPOR SDIT, arahkan kembali ke topik sistem
3. Berikan jawaban yang spesifik, praktis, dan mudah dipahami
4. Gunakan bahasa Indonesia yang formal namun ramah
5. Jika ada langkah-langkah, berikan dalam format yang terstruktur
6. Jika ada error atau masalah, berikan solusi yang jelas

PERTANYAAN USER: {$userMessage}

JAWABAN:";

        return $systemPrompt;
    }

    private function cleanResponse($response)
    {
        // Hapus marker atau prefix yang tidak diperlukan
        $cleaned = preg_replace('/^(JAWABAN:\s*|Jawaban:\s*)/i', '', $response);
        $cleaned = trim($cleaned);
        
        return $cleaned;
    }

    private function getDefaultKnowledgeBase()
    {
        return "
PANDUAN SISTEM RAPOR SDIT AL-HIDAYAH

NAVIGASI SISTEM & AKSES:
1. Admin Login: [domain]/login dengan Email + Password
2. Guru Login: [domain]/login dengan Username + Password
3. Setelah login guru, pilih role: Guru Pengajar atau Wali Kelas

SETUP AWAL SISTEM (WAJIB):
1. Profile Sekolah - Data nama sekolah, NPSN, alamat, kepala sekolah
2. Tahun Ajaran - Buat dan aktifkan tahun ajaran (format: YYYY/YYYY)
3. Kelas - Buat kelas 1A, 1B, 2A, 2B, dst
4. Guru - Input data guru dengan username untuk login
5. Siswa - Input siswa manual atau upload Excel
6. Mata Pelajaran - Buat mata pelajaran per kelas dan assign guru

TROUBLESHOOTING UMUM:
- Error login: Admin gunakan email+password, Guru gunakan username+password
- Duplikat tahun ajaran: Gunakan format YYYY/YYYY dengan slash (/)
- Menu tidak muncul: Lengkapi Profile Sekolah + Tahun Ajaran
- Template rapor error: Gunakan placeholder format \${nama_placeholder}

WORKFLOW PENGGUNAAN:
- Admin: Setup data master → Monitor progress → Kelola template rapor
- Guru Pengajar: Input nilai siswa → Setup mata pelajaran
- Wali Kelas: Input nilai + absensi + ekstrakurikuler → Generate rapor
        ";
    }

    public function getHistory()
    {
        $userId = null;
        
        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('guru')->check()) {
            $userId = Auth::guard('guru')->id();
        }
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $chats = GeminiChat::where('user_id', $userId)
                        ->orderBy('created_at', 'desc')
                        ->take(10)
                        ->get();
        
        return response()->json([
            'success' => true,
            'chats' => $chats
        ]);
    }

    // Method untuk update knowledge base
    public function updateKnowledgeBase(Request $request)
    {
        $request->validate([
            'knowledge_content' => 'required|string',
        ]);

        $knowledgeDir = storage_path('app/knowledge');
        if (!is_dir($knowledgeDir)) {
            mkdir($knowledgeDir, 0755, true);
        }

        $knowledgeFile = $knowledgeDir . '/rapor_sdit_guide.txt';
        file_put_contents($knowledgeFile, $request->knowledge_content);

        return response()->json([
            'success' => true,
            'message' => 'Knowledge base berhasil diperbarui'
        ]);
    }
    private function extractResponse($data)
    {
        Log::info('Extracting response from data: ' . json_encode($data));
        
        // Handle blocked responses
        if (isset($data['candidates'][0]['finishReason']) && 
            $data['candidates'][0]['finishReason'] === 'SAFETY') {
            return 'Maaf, respons diblokir karena alasan keamanan. Silakan coba pertanyaan yang berbeda.';
        }
        
        // Handle normal responses
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }
        
        // Alternative response structure
        if (isset($data['candidates'][0]['text'])) {
            return $data['candidates'][0]['text'];
        }
        
        // Direct text field
        if (isset($data['text'])) {
            return $data['text'];
        }
        
        // If candidates exist but no text
        if (isset($data['candidates']) && count($data['candidates']) > 0) {
            $candidate = $data['candidates'][0];
            Log::info('Candidate structure: ' . json_encode($candidate));
            
            // Check for safety block
            if (isset($candidate['finishReason'])) {
                return 'Respons tidak dapat diproses. Alasan: ' . $candidate['finishReason'];
            }
        }
        
        Log::error('Could not extract response from: ' . json_encode($data));
        return null;
    }

    private function analyzeNilaiIntent($message)
    {
        $message = strtolower(trim($message));
        
        $nilaiPatterns = [
            'comprehensive_overview' => [
                'keywords' => ['overview', 'keseluruhan', 'lengkap', 'komprehensif', 'menyeluruh', 'semua'],
                'patterns' => [
                    '/overview.*lengkap/', '/analisis.*menyeluruh/', '/gambaran.*keseluruhan/',
                    '/statistik.*lengkap/', '/data.*lengkap/', '/laporan.*lengkap/'
                ]
            ],
            'nilai_overview' => [
                'keywords' => ['ringkasan', 'gambaran', 'statistik', 'rata.*rata.*keseluruhan'],
                'patterns' => ['/gambaran.*nilai/', '/overview.*akademik/', '/ringkasan.*performa/', '/statistik.*nilai/']
            ],
            'siswa_belum_dinilai' => [
                'keywords' => ['belum', 'kosong', 'missing', 'tidak ada', 'belum diisi'],
                'patterns' => [
                    '/siswa.*belum.*nilai/', '/belum.*diisi/', '/nilai.*kosong/', 
                    '/missing.*nilai/', '/siswa.*tidak.*ada.*nilai/', '/progress.*input/',
                    '/kelengkapan.*nilai/'
                ]
            ],
            'mapel_tersulit' => [
                'keywords' => ['tersulit', 'sulit', 'susah', 'rendah', 'lemah'],
                'patterns' => [
                    '/mata.*pelajaran.*sulit/', '/mapel.*tersulit/', '/pelajaran.*susah/',
                    '/mapel.*lemah/', '/mata.*pelajaran.*rendah/'
                ]
            ],
            'nilai_tertinggi_terendah' => [
                'keywords' => ['tertinggi', 'terendah', 'maksimal', 'minimal', 'terbaik', 'terburuk'],
                'patterns' => [
                    '/nilai.*tertinggi/', '/nilai.*terendah/', '/siswa.*terbaik/',
                    '/nilai.*maksimal/', '/nilai.*minimal/'
                ]
            ],
            'guru_progress' => [
                'keywords' => ['guru.*belum', 'guru.*sudah', 'progress.*guru', 'guru.*input'],
                'patterns' => [
                    '/guru.*belum.*input/', '/guru.*sudah.*input/', '/progress.*guru/',
                    '/guru.*selesai/', '/guru.*lambat/'
                ]
            ],
            'kelas_progress' => [
                'keywords' => ['kelas.*progress', 'kelas.*selesai', 'kelas.*belum', 'progress.*kelas'],
                'patterns' => [
                    '/progress.*kelas/', '/kelas.*sudah/', '/kelas.*belum/',
                    '/performa.*kelas/', '/kelas.*selesai/'
                ]
            ],
            'rapor_progress' => [
                'keywords' => ['rapor', 'siap.*rapor', 'kesiapan', 'rapor.*progress'],
                'patterns' => [
                    '/progress.*rapor/', '/siap.*rapor/', '/kesiapan.*rapor/',
                    '/rapor.*selesai/', '/rapor.*belum/'
                ]
            ],
            'siswa_lemah' => [
                'keywords' => ['lemah', 'rendah', 'kurang', 'perlu.*perhatian', 'di.*bawah.*kkm'],
                'patterns' => ['/siswa.*lemah/', '/nilai.*rendah/', '/di bawah.*kkm/', '/perlu.*bantuan/']
            ],
            'siswa_terbaik' => [
                'keywords' => ['terbaik', 'tinggi', 'unggul', 'prestasi', 'ranking'],
                'patterns' => ['/siswa.*terbaik/', '/nilai.*tinggi/', '/prestasi.*baik/', '/ranking.*atas/']
            ],
            'mata_pelajaran_analisis' => [
                'keywords' => ['mapel', 'mata.*pelajaran', 'pelajaran', 'sulit', 'mudah'],
                'patterns' => ['/mapel.*sulit/', '/mata.*pelajaran.*lemah/', '/pelajaran.*mudah/']
            ],
            'kelas_perbandingan' => [
                'keywords' => ['kelas', 'bandingkan', 'perbandingan', 'vs'],
                'patterns' => ['/kelas.*vs/', '/bandingkan.*kelas/', '/performa.*kelas/']
            ],
            'trend_nilai' => [
                'keywords' => ['trend', 'perkembangan', 'naik', 'turun', 'progress'],
                'patterns' => ['/trend.*nilai/', '/perkembangan.*akademik/', '/progress.*nilai/']
            ],
            'progress_input_nilai' => [
                'keywords' => ['progress', 'kelengkapan', 'selesai', 'sudah', 'status', 'persen'],
                'patterns' => [
                    '/progress.*input/', '/kelengkapan.*nilai/', '/status.*nilai/',
                    '/sudah.*selesai/', '/berapa.*persen/'
                ]
            ]
        ];
        
        foreach ($nilaiPatterns as $intent => $config) {
            foreach ($config['keywords'] as $keyword) {
                if (preg_match('/' . $keyword . '/', $message)) {
                    foreach ($config['patterns'] as $pattern) {
                        if (preg_match($pattern, $message)) {
                            return $intent;
                        }
                    }
                    return $intent;
                }
            }
        }
        
        return 'general_nilai';
    }

    private function fetchNilaiAnalysisData($intent, $userMessage)
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        $userRole = $this->getUserRole();
        $data = [];
        
        try {
            switch ($intent) {
                case 'comprehensive_overview':
                    $data = $this->getComprehensiveAcademicOverview($tahunAjaranId, $userRole);
                    break;
                    
                case 'nilai_overview':
                    $data = $this->getNilaiOverview($tahunAjaranId, $userRole);
                    break;
                    
                case 'siswa_belum_dinilai':
                    $data = $this->getSiswaBelumDinilai($tahunAjaranId, $userRole);
                    break;
                    
                case 'mapel_tersulit':
                    $data = $this->getMapelDifficultyAnalysis($tahunAjaranId, $userRole);
                    break;

                case 'mata_pelajaran_analisis':
                    $data = $this->getMapelDifficultyAnalysis($tahunAjaranId, $userRole);
                    break;
                    
                case 'nilai_tertinggi_terendah':
                    $overview = $this->getNilaiOverview($tahunAjaranId, $userRole);
                    $siswaAnalysis = $this->getSiswaAnalysis($tahunAjaranId, $userRole);
                    $data = [
                        'nilai_tertinggi' => $overview['nilai_tertinggi'] ?? 0,
                        'nilai_terendah' => $overview['nilai_terendah'] ?? 0,
                        'siswa_nilai_tertinggi' => $siswaAnalysis['siswa_terbaik'] ?? [],
                        'siswa_nilai_terendah' => $siswaAnalysis['siswa_perlu_perhatian'] ?? [],
                        'context' => 'Analisis nilai tertinggi dan terendah'
                    ];
                    break;
                    
                case 'guru_progress':
                    $data = $this->getGuruPerformanceAnalysis($tahunAjaranId, $userRole);
                    break;
                    
                case 'kelas_progress':
                    $data = $this->getClassComparisonAnalysis($tahunAjaranId, $userRole);
                    break;
                    
                case 'rapor_progress':
                    $data = $this->getRaporProgressAnalysis($tahunAjaranId, $userRole);
                    break;
                    
                case 'siswa_lemah':
                    $data = $this->getSiswaLemah($tahunAjaranId, $userRole);
                    break;
                    
                case 'siswa_terbaik':
                    $data = $this->getSiswaTerbaik($tahunAjaranId, $userRole);
                    break;
                    
                case 'kelas_perbandingan':
                    $data = $this->getKelasPerbandingan($tahunAjaranId, $userRole);
                    break;
                    
                case 'trend_nilai':
                    $data = $this->getTrendNilai($tahunAjaranId, $userRole);
                    break;
                    
                case 'progress_input_nilai':
                    $data = $this->getProgressInputNilai($tahunAjaranId, $userRole);
                    break;
                    
                default:
                    $data = $this->getGeneralNilaiStats($tahunAjaranId, $userRole);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching nilai analysis data: ' . $e->getMessage());
            $data = ['error' => 'Tidak dapat mengambil data nilai: ' . $e->getMessage()];
        }
        
        return $data;
    }

    private function getMataPelajaranAnalisis($tahunAjaranId, $userRole)
    {
        $query = MataPelajaran::with(['nilais' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        }, 'guru', 'kelas'])->where('tahun_ajaran_id', $tahunAjaranId);
        
        $query = $this->applyRoleFilterToMapel($query, $userRole);
        $mataPelajarans = $query->get();
        
        $analisisMapel = $mataPelajarans->map(function($mapel) {
            $nilais = $mapel->nilais->pluck('nilai_akhir_rapor');
            if ($nilais->isEmpty()) return null;
            
            // Hitung siswa total dengan cara yang aman
            $totalSiswa = 0;
            if ($mapel->kelas) {
                $totalSiswa = Siswa::where('kelas_id', $mapel->kelas_id)->count();
            }
            
            $rataRata = $nilais->avg();
            
            return [
                'nama_mapel' => $mapel->nama_pelajaran,
                'kelas' => $mapel->kelas ? ($mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas) : 'N/A',
                'guru' => $mapel->guru ? $mapel->guru->nama : 'N/A',
                'rata_nilai' => round($rataRata, 2),
                'siswa_count' => $nilais->count(),
                'total_siswa' => $totalSiswa,
                'tingkat_kesulitan' => $this->kategorikanTingkatKesulitan($rataRata),
                'distribusi_nilai' => [
                    'di_atas_85' => $nilais->filter(fn($n) => $n >= 85)->count(),
                    'di_bawah_70' => $nilais->filter(fn($n) => $n < 70)->count()
                ]
            ];
        })->filter()->sortBy('rata_nilai');
        
        return [
            'total_mapel' => $analisisMapel->count(),
            'mapel_tersulit' => $analisisMapel->first(),
            'mapel_termudah' => $analisisMapel->last(),
            'detail_analisis' => $analisisMapel->values(),
            'rekomendasi' => $this->generateRekomendasiMapel($analisisMapel),
            'context' => 'Analisis tingkat kesulitan mata pelajaran'
        ];
    }

    private function getUserRole()
    {
        if (Auth::guard('web')->check()) {
            return 'admin';
        } elseif (Auth::guard('guru')->check()) {
            return session('selected_role') === 'wali_kelas' ? 'wali_kelas' : 'guru';
        }
        return 'guest';
    }

    private function getNilaiOverview($tahunAjaranId, $userRole)
    {
        $query = Nilai::where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        
        // Filter berdasarkan role
        $query = $this->applyRoleFilterToNilai($query, $userRole);
        
        $nilais = $query->get();
        
        if ($nilais->isEmpty()) {
            return ['message' => 'Belum ada data nilai untuk tahun ajaran ini'];
        }
        
        $nilai_akhir = $nilais->pluck('nilai_akhir_rapor');
        
        return [
            'total_nilai' => $nilais->count(),
            'rata_rata' => round($nilai_akhir->avg(), 2),
            'nilai_tertinggi' => $nilai_akhir->max(),
            'nilai_terendah' => $nilai_akhir->min(),
            'distribusi' => [
                'sangat_baik' => $nilai_akhir->filter(fn($n) => $n >= 90)->count(),
                'baik' => $nilai_akhir->filter(fn($n) => $n >= 80 && $n < 90)->count(),
                'cukup' => $nilai_akhir->filter(fn($n) => $n >= 70 && $n < 80)->count(),
                'kurang' => $nilai_akhir->filter(fn($n) => $n < 70)->count(),
            ],
            'statistik_mapel' => $this->getTopBottomMapel($nilais),
            'context' => "Overview nilai akademik tahun ajaran " . TahunAjaran::find($tahunAjaranId)->tahun_ajaran
        ];
    }

    private function getFallbackResponse($userMessage)
    {
        Log::error("All Gemini models failed, providing fallback response");
        
        // Analisis sederhana berdasarkan keyword
        $message = strtolower($userMessage);
        
        if (strpos($message, 'nilai') !== false || strpos($message, 'akademik') !== false) {
            $fallbackResponse = "Maaf, sistem AI sedang mengalami gangguan. Namun saya dapat membantu dengan informasi dasar:

    📊 **UNTUK ANALISIS NILAI:**
    - Cek dashboard untuk statistik nilai
    - Gunakan menu 'Score Management' untuk input nilai
    - Lihat progress di dashboard admin/guru

    🔍 **INFORMASI YANG TERSEDIA:**
    - Data nilai real-time di dashboard
    - Progress input nilai per guru
    - Statistik performa kelas

    💡 **SARAN:**
    Silakan coba lagi dalam beberapa menit, atau gunakan menu navigasi untuk mengakses data nilai secara langsung.";
        } else {
            $fallbackResponse = "Maaf, sistem AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.

    🛠️ **SOLUSI SEMENTARA:**
    - Gunakan menu navigasi untuk mengakses fitur yang diperlukan
    - Cek dokumentasi sistem di menu bantuan
    - Hubungi admin sistem jika ada masalah urgent

    Sistem akan kembali normal dalam beberapa menit. Terima kasih atas pengertiannya.";
        }
        
        return [
            'success' => true,
            'data' => $fallbackResponse,
            'model_used' => 'fallback',
            'fallback' => true
        ];
    }

    private function getSiswaLemah($tahunAjaranId, $userRole)
    {
        $query = Siswa::whereHas('nilais', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('nilai_akhir_rapor', '<', 70);
        });
        
        // Filter berdasarkan role
        $query = $this->applyRoleFilterToSiswa($query, $userRole);
        
        $siswaLemah = $query->with(['kelas', 'nilais' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('nilai_akhir_rapor', '<', 70)
            ->with('mataPelajaran');
        }])->limit(20)->get();
        
        $analisis = $siswaLemah->map(function($siswa) {
            $nilaiLemah = $siswa->nilais->where('nilai_akhir_rapor', '<', 70);
            return [
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas->nomor_kelas . $siswa->kelas->nama_kelas,
                'jumlah_mapel_lemah' => $nilaiLemah->count(),
                'rata_nilai' => round($nilaiLemah->avg('nilai_akhir_rapor'), 2),
                'mapel_terlemah' => $nilaiLemah->sortBy('nilai_akhir_rapor')->first()->mataPelajaran->nama_pelajaran ?? 'N/A'
            ];
        });
        
        return [
            'total_siswa_lemah' => $siswaLemah->count(),
            'detail_siswa' => $analisis->values(),
            'rekomendasi' => $this->generateRekomendasiSiswaLemah($analisis),
            'context' => 'Analisis siswa yang memerlukan perhatian khusus'
        ];
    }

    private function getSiswaTerbaik($tahunAjaranId, $userRole)
    {
        $query = Siswa::whereHas('nilais', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('nilai_akhir_rapor', '>=', 85);
        });
        
        $query = $this->applyRoleFilterToSiswa($query, $userRole);
        
        $siswaTerbaik = $query->with(['kelas', 'nilais' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->with('mataPelajaran');
        }])->get();
        
        $analisis = $siswaTerbaik->map(function($siswa) {
            $nilaiSiswa = $siswa->nilais->whereNotNull('nilai_akhir_rapor');
            return [
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas->nomor_kelas . $siswa->kelas->nama_kelas,
                'rata_nilai' => round($nilaiSiswa->avg('nilai_akhir_rapor'), 2),
                'jumlah_mapel' => $nilaiSiswa->count(),
                'nilai_tertinggi' => $nilaiSiswa->max('nilai_akhir_rapor')
            ];
        })->sortByDesc('rata_nilai')->take(10);
        
        return [
            'total_siswa_berprestasi' => $siswaTerbaik->count(),
            'top_10_siswa' => $analisis->values(),
            'insights' => $this->generateInsightsSiswaTerbaik($analisis),
            'context' => 'Analisis siswa berprestasi tinggi'
        ];
    }

    private function getKelasPerbandingan($tahunAjaranId, $userRole)
    {
        $query = Kelas::with(['siswas.nilais' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        }])->where('tahun_ajaran_id', $tahunAjaranId);
        
        if ($userRole === 'wali_kelas') {
            $guru = Auth::guard('guru')->user();
            $kelasId = $guru->getWaliKelasId();
            $query->where('id', $kelasId);
        }
        
        $kelasList = $query->get();
        
        $perbandinganKelas = $kelasList->map(function($kelas) {
            $allNilai = $kelas->siswas->flatMap->nilais->pluck('nilai_akhir_rapor');
            
            return [
                'kelas' => $kelas->nomor_kelas . $kelas->nama_kelas,
                'jumlah_siswa' => $kelas->siswas->count(),
                'rata_nilai' => $allNilai->isNotEmpty() ? round($allNilai->avg(), 2) : 0,
                'nilai_tertinggi' => $allNilai->max() ?? 0,
                'nilai_terendah' => $allNilai->min() ?? 0,
                'siswa_di_atas_kkm' => $allNilai->filter(fn($n) => $n >= 70)->count(),
                'wali_kelas' => $kelas->getWaliKelas()->nama ?? 'Belum ditentukan'
            ];
        })->sortByDesc('rata_nilai');
        
        return [
            'total_kelas' => $perbandinganKelas->count(),
            'kelas_terbaik' => $perbandinganKelas->first(),
            'kelas_terlemah' => $perbandinganKelas->last(),
            'detail_perbandingan' => $perbandinganKelas->values(),
            'insights' => $this->generateInsightsPerbandinganKelas($perbandinganKelas),
            'context' => 'Perbandingan performa antar kelas'
        ];
    }

    private function getTrendNilai($tahunAjaranId, $userRole)
    {
        $query = Nilai::where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        
        $query = $this->applyRoleFilterToNilai($query, $userRole);
        
        $nilais = $query->with(['siswa', 'mataPelajaran'])
            ->orderBy('created_at')
            ->get();
        
        // Group by month untuk trend
        $trendBulanan = $nilais->groupBy(function($nilai) {
            return $nilai->created_at->format('Y-m');
        })->map(function($nilaiPerBulan) {
            return [
                'bulan' => $nilaiPerBulan->first()->created_at->format('M Y'),
                'rata_nilai' => round($nilaiPerBulan->avg('nilai_akhir_rapor'), 2),
                'jumlah_nilai' => $nilaiPerBulan->count()
            ];
        });
        
        return [
            'trend_bulanan' => $trendBulanan->values(),
            'total_data_points' => $nilais->count(),
            'rata_keseluruhan' => round($nilais->avg('nilai_akhir_rapor'), 2),
            'trend_direction' => $this->calculateTrendDirection($trendBulanan),
            'context' => 'Analisis trend perkembangan nilai'
        ];
    }

    private function getSiswaBelumDinilai($tahunAjaranId, $userRole)
    {
        $guru = Auth::guard('guru')->user();
        
        if ($userRole === 'guru') {
            // Ambil mata pelajaran yang diajar guru ini
            $mataPelajarans = MataPelajaran::where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->with(['kelas.siswas'])
                ->get();
            
            $siswaBelumDinilai = [];
            
            foreach ($mataPelajarans as $mapel) {
                $siswasDiKelas = $mapel->kelas->siswas;
                
                foreach ($siswasDiKelas as $siswa) {
                    // Cek apakah siswa sudah punya nilai untuk mata pelajaran ini
                    $hasNilai = Nilai::where('siswa_id', $siswa->id)
                        ->where('mata_pelajaran_id', $mapel->id)
                        ->where('tahun_ajaran_id', $tahunAjaranId)
                        ->whereNotNull('nilai_akhir_rapor')
                        ->exists();
                    
                    if (!$hasNilai) {
                        $siswaBelumDinilai[] = [
                            'siswa_id' => $siswa->id,
                            'nama_siswa' => $siswa->nama,
                            'nis' => $siswa->nis,
                            'kelas' => $mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas,
                            'mata_pelajaran' => $mapel->nama_pelajaran,
                            'mata_pelajaran_id' => $mapel->id,
                            'status' => 'Belum ada nilai akhir rapor'
                        ];
                    }
                }
            }
            
            return [
                'total_siswa_belum_dinilai' => count($siswaBelumDinilai),
                'detail_siswa' => $siswaBelumDinilai,
                'mata_pelajaran_yang_diajar' => $mataPelajarans->map(function($mapel) {
                    return [
                        'nama' => $mapel->nama_pelajaran,
                        'kelas' => $mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas
                    ];
                }),
                'prioritas_aksi' => $this->generatePrioritasInputNilai($siswaBelumDinilai),
                'context' => 'Daftar siswa yang belum diisi nilai oleh guru ' . $guru->nama
            ];
            
        } elseif ($userRole === 'wali_kelas') {
            // Untuk wali kelas - cek semua mata pelajaran di kelasnya
            $kelasId = $guru->getWaliKelasId();
            
            if (!$kelasId) {
                return ['error' => 'Anda bukan wali kelas atau kelas tidak ditemukan'];
            }
            
            $kelas = Kelas::with(['siswas', 'mataPelajarans.guru'])->find($kelasId);
            $mataPelajarans = MataPelajaran::where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->with('guru')
                ->get();
            
            $analisisPerMapel = [];
            
            foreach ($mataPelajarans as $mapel) {
                $siswaBelumDinilai = [];
                
                foreach ($kelas->siswas as $siswa) {
                    $hasNilai = Nilai::where('siswa_id', $siswa->id)
                        ->where('mata_pelajaran_id', $mapel->id)
                        ->where('tahun_ajaran_id', $tahunAjaranId)
                        ->whereNotNull('nilai_akhir_rapor')
                        ->exists();
                    
                    if (!$hasNilai) {
                        $siswaBelumDinilai[] = [
                            'nama_siswa' => $siswa->nama,
                            'nis' => $siswa->nis
                        ];
                    }
                }
                
                $analisisPerMapel[] = [
                    'mata_pelajaran' => $mapel->nama_pelajaran,
                    'guru' => $mapel->guru->nama,
                    'total_siswa' => $kelas->siswas->count(),
                    'siswa_belum_dinilai' => count($siswaBelumDinilai),
                    'persentase_selesai' => $kelas->siswas->count() > 0 ? 
                        round((($kelas->siswas->count() - count($siswaBelumDinilai)) / $kelas->siswas->count()) * 100, 2) : 0,
                    'detail_siswa_belum_dinilai' => $siswaBelumDinilai
                ];
            }
            
            return [
                'kelas' => $kelas->nomor_kelas . $kelas->nama_kelas,
                'total_siswa_di_kelas' => $kelas->siswas->count(),
                'analisis_per_mapel' => $analisisPerMapel,
                'mapel_prioritas' => $this->getMapelPrioritas($analisisPerMapel),
                'rekomendasi_wali_kelas' => $this->generateRekomendasiWaliKelas($analisisPerMapel),
                'context' => 'Analisis kelengkapan nilai untuk kelas ' . $kelas->nomor_kelas . $kelas->nama_kelas
            ];
            
        } else {
            // Untuk admin - overview semua
            return $this->getOverviewSiswaBelumDinilaiAdmin($tahunAjaranId);
        }
    }

    private function getAdminProgressSummary($tahunAjaranId)
    {
        // Ambil statistik aggregat saja, bukan detail per siswa
        $totalGuru = Guru::whereHas('mataPelajarans', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId);
        })->count();
        
        $totalMataPelajaran = MataPelajaran::where('tahun_ajaran_id', $tahunAjaranId)->count();
        
        $totalNilaiExpected = MataPelajaran::where('tahun_ajaran_id', $tahunAjaranId)
            ->withCount('kelas.siswas as siswa_count')
            ->get()
            ->sum('siswa_count');
        
        $totalNilaiCompleted = Nilai::where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor')
            ->count();
        
        $overallProgress = $totalNilaiExpected > 0 ? 
            round(($totalNilaiCompleted / $totalNilaiExpected) * 100, 2) : 0;
        
        // Ambil 5 guru dengan progress terendah saja
        $guruProgress = $this->getTopBottomGuruProgress($tahunAjaranId, 5);
        
        return [
            'total_guru' => $totalGuru,
            'total_mata_pelajaran' => $totalMataPelajaran,
            'overall_progress' => $overallProgress,
            'total_expected' => $totalNilaiExpected,
            'total_completed' => $totalNilaiCompleted,
            'guru_terendah' => $guruProgress['terendah'],
            'guru_tertinggi' => $guruProgress['tertinggi'],
            'context' => 'Summary progress input nilai seluruh sekolah'
        ];
    }

    private function getTopBottomGuruProgress($tahunAjaranId, $limit = 5)
    {
        $allGuru = Guru::whereHas('mataPelajarans', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId);
        })->with(['mataPelajarans' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)->with('kelas:id');
        }])->get();
        
        $guruProgress = [];
        
        foreach ($allGuru as $guru) {
            $totalExpected = 0;
            $totalCompleted = 0;
            
            foreach ($guru->mataPelajarans as $mapel) {
                $siswaCount = $mapel->kelas->siswas()->count();
                $nilaiCount = Nilai::where('mata_pelajaran_id', $mapel->id)
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->whereNotNull('nilai_akhir_rapor')
                    ->count();
                
                $totalExpected += $siswaCount;
                $totalCompleted += $nilaiCount;
            }
            
            $completionRate = $totalExpected > 0 ? 
                round(($totalCompleted / $totalExpected) * 100, 2) : 0;
            
            $guruProgress[] = [
                'nama' => $guru->nama,
                'completion_rate' => $completionRate,
                'mapel_count' => $guru->mataPelajarans->count()
            ];
        }
        
        $sorted = collect($guruProgress)->sortBy('completion_rate');
        
        return [
            'terendah' => $sorted->take($limit)->values()->all(),
            'tertinggi' => $sorted->sortByDesc('completion_rate')->take($limit)->values()->all()
        ];
    }

    private function compactDatabaseData($databaseData, $intent)
    {
        // Jika data kosong atau error
        if (empty($databaseData) || isset($databaseData['error'])) {
            return $databaseData;
        }
        
        // Helper function untuk safely slice data
        $safeSlice = function($data, $limit = 3) {
            if (empty($data)) return [];
            if (is_array($data)) return array_slice($data, 0, $limit);
            if ($data instanceof \Illuminate\Support\Collection) return $data->take($limit)->toArray();
            return [];
        };
        
        // Ringkas berdasarkan intent
        switch ($intent) {
            case 'comprehensive_overview':
                return [
                    'statistik_umum' => [
                        'rata_rata_keseluruhan' => $databaseData['statistik_umum']['rata_rata_keseluruhan'] ?? 0,
                        'nilai_tertinggi' => $databaseData['statistik_umum']['nilai_tertinggi'] ?? 0,
                        'nilai_terendah' => $databaseData['statistik_umum']['nilai_terendah'] ?? 0,
                        'distribusi_grade' => $databaseData['statistik_umum']['distribusi_grade'] ?? []
                    ],
                    'siswa_analisis' => [
                        'total_siswa' => $databaseData['siswa_analisis']['total_siswa_aktif'] ?? 0,
                        'siswa_terbaik_count' => count($databaseData['siswa_analisis']['siswa_terbaik'] ?? []),
                        'siswa_perlu_perhatian_count' => count($databaseData['siswa_analisis']['siswa_perlu_perhatian'] ?? []),
                        'siswa_belum_dinilai' => $databaseData['siswa_analisis']['siswa_belum_dinilai'] ?? 0
                    ],
                    'mapel_analisis' => [
                        'mata_pelajaran_tersulit' => $safeSlice($databaseData['mapel_analisis']['mata_pelajaran_tersulit'] ?? [], 3),
                        'mata_pelajaran_termudah' => $safeSlice($databaseData['mapel_analisis']['mata_pelajaran_termudah'] ?? [], 3)
                    ],
                    'guru_performance' => [
                        'guru_perlu_followup_count' => count($databaseData['guru_performance']['guru_perlu_followup'] ?? []),
                        'guru_belum_input_count' => count($databaseData['guru_performance']['guru_belum_input'] ?? []),
                        'guru_sudah_selesai_count' => count($databaseData['guru_performance']['guru_sudah_selesai'] ?? []),
                        'rata_rata_completion' => $databaseData['guru_performance']['rata_rata_completion'] ?? 0
                    ],
                    'rapor_progress' => [
                        'siap_rapor' => $databaseData['progress_rapor']['siap_rapor'] ?? 0,
                        'belum_siap_rapor' => $databaseData['progress_rapor']['belum_siap_rapor'] ?? 0,
                        'persentase_kesiapan' => $databaseData['progress_rapor']['persentase_kesiapan'] ?? 0
                    ],
                    'context' => 'Overview komprehensif sistem akademik'
                ];
                
            case 'siswa_belum_dinilai':
                // ULTRA COMPACT untuk admin
                if (isset($databaseData['total_entries_expected'])) {
                    return [
                        'total_expected' => $databaseData['total_entries_expected'] ?? 0,
                        'total_missing' => $databaseData['total_entries_missing'] ?? 0,
                        'completion_rate' => $databaseData['overall_completion'] ?? 0,
                        'urgent_count' => count($databaseData['urgent_actions'] ?? []),
                        'guru_slowest' => $safeSlice($databaseData['guru_progress_ranking'] ?? [], 3),
                        'top_urgent_actions' => $safeSlice($databaseData['urgent_actions'] ?? [], 5),
                        'context' => 'Analisis kelengkapan input nilai'
                    ];
                }
                
                // Untuk guru individual
                return [
                    'total_belum_dinilai' => $databaseData['total_siswa_belum_dinilai'] ?? 0,
                    'mapel_bermasalah' => count($databaseData['mata_pelajaran_yang_diajar'] ?? []),
                    'detail_ringkas' => $safeSlice($databaseData['detail_siswa'] ?? [], 5),
                    'prioritas_aksi' => $databaseData['prioritas_aksi'] ?? [],
                    'context' => $databaseData['context'] ?? ''
                ];
                
            case 'mapel_tersulit':
            case 'mata_pelajaran_analisis':
                return [
                    'mapel_tersulit' => $databaseData['mapel_tersulit'] ?? null,
                    'mapel_termudah' => $databaseData['mapel_termudah'] ?? null,
                    'total_mapel' => $databaseData['total_mapel'] ?? 0,
                    'detail_analisis' => $safeSlice($databaseData['detail_analisis'] ?? [], 5),
                    'rekomendasi' => $databaseData['rekomendasi'] ?? [],
                    'context' => 'Analisis tingkat kesulitan mata pelajaran'
                ];
                
            case 'guru_progress':
                return [
                    'guru_terbaik' => $safeSlice($databaseData['guru_terbaik_completion'] ?? [], 5),
                    'guru_perlu_followup' => $safeSlice($databaseData['guru_perlu_followup'] ?? [], 5),
                    'guru_belum_input_count' => count($databaseData['guru_belum_input'] ?? []),
                    'guru_selesai_count' => count($databaseData['guru_sudah_selesai'] ?? []),
                    'rata_rata_completion' => $databaseData['rata_rata_completion'] ?? 0,
                    'context' => 'Analisis progress input nilai guru'
                ];
                
            case 'nilai_tertinggi_terendah':
                return [
                    'nilai_tertinggi_global' => $databaseData['nilai_tertinggi'] ?? 0,
                    'nilai_terendah_global' => $databaseData['nilai_terendah'] ?? 0,
                    'siswa_terbaik' => $safeSlice($databaseData['siswa_nilai_tertinggi'] ?? [], 3),
                    'siswa_terlemah' => $safeSlice($databaseData['siswa_nilai_terendah'] ?? [], 3),
                    'context' => 'Analisis nilai ekstrem (tertinggi-terendah)'
                ];
                
            case 'kelas_progress':
            case 'kelas_perbandingan':
                return [
                    'kelas_terbaik' => $safeSlice($databaseData['kelas_terbaik'] ?? [], 3),
                    'kelas_perlu_perhatian' => $safeSlice($databaseData['kelas_perlu_perhatian'] ?? [], 3),
                    'gap_performa' => $databaseData['gap_performa'] ?? 0,
                    'context' => 'Perbandingan performa antar kelas'
                ];
                
            case 'rapor_progress':
                return [
                    'total_siswa' => $databaseData['total_siswa'] ?? 0,
                    'siap_rapor' => $databaseData['siap_rapor'] ?? 0,
                    'belum_siap_rapor' => $databaseData['belum_siap_rapor'] ?? 0,
                    'persentase_kesiapan' => $databaseData['persentase_kesiapan'] ?? 0,
                    'kelas_siap_terbanyak' => $safeSlice($databaseData['progress_per_kelas'] ?? [], 3),
                    'masalah_umum' => $safeSlice($databaseData['masalah_umum'] ?? [], 5),
                    'context' => 'Analisis kesiapan rapor'
                ];
                
            case 'progress_input_nilai':
                return [
                    'overall_progress' => $databaseData['overall_progress'] ?? 0,
                    'mapel_count' => $databaseData['total_mata_pelajaran'] ?? 0,
                    'next_action' => $databaseData['next_action'] ?? '',
                    'detail_ringkas' => $safeSlice($databaseData['detail_per_mapel'] ?? [], 5),
                    'context' => 'Progress input nilai'
                ];
                
            case 'nilai_overview':
                return [
                    'total_nilai' => $databaseData['total_nilai'] ?? 0,
                    'rata_rata' => $databaseData['rata_rata'] ?? 0,
                    'nilai_tertinggi' => $databaseData['nilai_tertinggi'] ?? 0,
                    'nilai_terendah' => $databaseData['nilai_terendah'] ?? 0,
                    'distribusi' => $databaseData['distribusi'] ?? [],
                    'context' => 'Overview nilai akademik'
                ];
                
            case 'siswa_lemah':
                return [
                    'total_lemah' => $databaseData['total_siswa_lemah'] ?? 0,
                    'sample_siswa' => $safeSlice($databaseData['detail_siswa'] ?? [], 5),
                    'rekomendasi' => $databaseData['rekomendasi'] ?? [],
                    'context' => 'Siswa yang perlu perhatian'
                ];
                
            case 'siswa_terbaik':
                return [
                    'total_berprestasi' => $databaseData['total_siswa_berprestasi'] ?? 0,
                    'top_5' => $safeSlice($databaseData['top_10_siswa'] ?? [], 5),
                    'insights' => $databaseData['insights'] ?? [],
                    'context' => 'Siswa berprestasi'
                ];
                
            default:
                return [
                    'message' => 'Data tersedia untuk analisis',
                    'context' => $databaseData['context'] ?? ''
                ];
        }
    }

    private function getProgressInputNilai($tahunAjaranId, $userRole)
    {
        $guru = Auth::guard('guru')->user();
        
        if ($userRole === 'guru') {
            $mataPelajarans = MataPelajaran::where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->with('kelas.siswas')
                ->get();
            
            $progressData = [];
            
            foreach ($mataPelajarans as $mapel) {
                $totalSiswa = $mapel->kelas->siswas->count();
                $siswaSudahDinilai = Nilai::where('mata_pelajaran_id', $mapel->id)
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->whereNotNull('nilai_akhir_rapor')
                    ->distinct('siswa_id')
                    ->count();
                
                $persentase = $totalSiswa > 0 ? round(($siswaSudahDinilai / $totalSiswa) * 100, 2) : 0;
                
                $progressData[] = [
                    'mata_pelajaran' => $mapel->nama_pelajaran,
                    'kelas' => $mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas,
                    'total_siswa' => $totalSiswa,
                    'siswa_sudah_dinilai' => $siswaSudahDinilai,
                    'siswa_belum_dinilai' => $totalSiswa - $siswaSudahDinilai,
                    'persentase_selesai' => $persentase,
                    'status' => $this->getStatusProgress($persentase)
                ];
            }
            
            $overallProgress = $this->calculateOverallProgress($progressData);
            
            return [
                'guru' => $guru->nama,
                'total_mata_pelajaran' => count($progressData),
                'overall_progress' => $overallProgress,
                'detail_per_mapel' => $progressData,
                'next_action' => $this->getNextActionRecommendation($progressData),
                'context' => 'Progress input nilai untuk guru ' . $guru->nama
            ];
            
        } elseif ($userRole === 'wali_kelas') {
            // Progress untuk wali kelas - semua mapel di kelasnya
            $kelasId = $guru->getWaliKelasId();
            $kelas = Kelas::with('siswas')->find($kelasId);
            $mataPelajarans = MataPelajaran::where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->with('guru')
                ->get();
            
            $progressPerMapel = [];
            $totalSiswa = $kelas->siswas->count();
            
            foreach ($mataPelajarans as $mapel) {
                $siswaSudahDinilai = Nilai::where('mata_pelajaran_id', $mapel->id)
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->whereNotNull('nilai_akhir_rapor')
                    ->distinct('siswa_id')
                    ->count();
                
                $persentase = $totalSiswa > 0 ? round(($siswaSudahDinilai / $totalSiswa) * 100, 2) : 0;
                
                $progressPerMapel[] = [
                    'mata_pelajaran' => $mapel->nama_pelajaran,
                    'guru' => $mapel->guru->nama,
                    'siswa_sudah_dinilai' => $siswaSudahDinilai,
                    'siswa_belum_dinilai' => $totalSiswa - $siswaSudahDinilai,
                    'persentase_selesai' => $persentase,
                    'status' => $this->getStatusProgress($persentase)
                ];
            }
            
            return [
                'kelas' => $kelas->nomor_kelas . $kelas->nama_kelas,
                'total_siswa' => $totalSiswa,
                'total_mata_pelajaran' => count($progressPerMapel),
                'progress_per_mapel' => $progressPerMapel,
                'mapel_selesai' => collect($progressPerMapel)->where('persentase_selesai', 100)->count(),
                'mapel_belum_selesai' => collect($progressPerMapel)->where('persentase_selesai', '<', 100)->count(),
                'urgency_list' => $this->getUrgencyList($progressPerMapel),
                'context' => 'Progress input nilai untuk kelas ' . $kelas->nomor_kelas . $kelas->nama_kelas
            ];
        } else {
            // PERBAIKAN: Untuk admin, ambil summary saja, bukan detail
            return $this->getAdminProgressSummary($tahunAjaranId);
        }
    }

    private function getGeneralNilaiStats($tahunAjaranId, $userRole)
    {
        return [
            'message' => 'Silakan tanyakan hal spesifik tentang nilai akademik',
            'suggestions' => [
                'Berikan overview nilai akademik',
                'Siswa mana yang memerlukan perhatian?',
                'Progress input nilai saya',
                'Analisis mata pelajaran tersulit'
            ]
        ];
    }

    // Helper methods
    private function applyRoleFilterToNilai($query, $userRole)
    {
        switch ($userRole) {
            case 'guru':
                $guru = Auth::guard('guru')->user();
                return $query->whereHas('mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                });
                
            case 'wali_kelas':
                $guru = Auth::guard('guru')->user();
                $kelasId = $guru->getWaliKelasId();
                return $query->whereHas('siswa', function($q) use ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                });
                
            case 'admin':
            default:
                return $query;
        }
    }

    private function applyRoleFilterToSiswa($query, $userRole)
    {
        switch ($userRole) {
            case 'guru':
                $guru = Auth::guard('guru')->user();
                return $query->whereHas('kelas.mataPelajarans', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                });
                
            case 'wali_kelas':
                $guru = Auth::guard('guru')->user();
                $kelasId = $guru->getWaliKelasId();
                return $query->where('kelas_id', $kelasId);
                
            case 'admin':
            default:
                return $query;
        }
    }

    private function applyRoleFilterToMapel($query, $userRole)
    {
        switch ($userRole) {
            case 'guru':
                $guru = Auth::guard('guru')->user();
                return $query->where('guru_id', $guru->id);
                
            case 'wali_kelas':
                $guru = Auth::guard('guru')->user();
                $kelasId = $guru->getWaliKelasId();
                return $query->where('kelas_id', $kelasId);
                
            case 'admin':
            default:
                return $query;
        }
    }

    private function getTopBottomMapel($nilais)
    {
    $mapelStats = $nilais->groupBy('mata_pelajaran_id')->map(function($nilaiMapel) {
        $rataMapel = $nilaiMapel->avg('nilai_akhir_rapor');
        $mapel = $nilaiMapel->first()->mataPelajaran;
        
        return [
            'nama_mapel' => $mapel->nama_pelajaran,
            'rata_nilai' => round($rataMapel, 2),
            'jumlah_nilai' => $nilaiMapel->count()
        ];
    })->sortByDesc('rata_nilai');
    
    return [
        'mapel_terbaik' => $mapelStats->first(),
        'mapel_terlemah' => $mapelStats->last()
    ];
    }

    private function generateRekomendasiSiswaLemah($analisisSiswa)
    {
    if ($analisisSiswa->isEmpty()) {
        return ['message' => 'Tidak ada siswa yang memerlukan perhatian khusus'];
    }
    
    $mapelBermasalah = $analisisSiswa->pluck('mapel_terlemah')->countBy();
    $mapelTersulit = $mapelBermasalah->sortDesc()->keys()->first();
    
    return [
        'mata_pelajaran_prioritas' => $mapelTersulit,
        'strategi' => [
            'Buat program remedial khusus untuk ' . $mapelTersulit,
            'Bentuk kelompok belajar peer-to-peer',
            'Konsultasi dengan guru mata pelajaran',
            'Libatkan orang tua dalam monitoring'
        ],
        'tindak_lanjut' => 'Evaluasi mingguan dan konseling individual'
    ];
    }

    private function generateInsightsSiswaTerbaik($analisis)
    {
    if ($analisis->isEmpty()) {
        return ['message' => 'Belum ada data siswa berprestasi'];
    }
    
    $rataKeseluruhan = $analisis->avg('rata_nilai');
    
    return [
        'rata_siswa_terbaik' => round($rataKeseluruhan, 2),
        'standar_prestasi' => $rataKeseluruhan >= 90 ? 'Sangat Tinggi' : ($rataKeseluruhan >= 85 ? 'Tinggi' : 'Baik'),
        'rekomendasi' => [
            'Berikan tantangan pembelajaran yang lebih tinggi',
            'Jadikan mentor untuk siswa lain',
            'Sediakan program pengayaan',
            'Persiapkan untuk kompetisi akademik'
        ]
    ];
    }

    private function kategorikanTingkatKesulitan($rataRata)
    {
    if ($rataRata >= 85) return 'Mudah';
    if ($rataRata >= 75) return 'Sedang';
    if ($rataRata >= 65) return 'Sulit';
    return 'Sangat Sulit';
    }

    private function generateRekomendasiMapel($analisisMapel)
    {
    if ($analisisMapel->isEmpty()) {
        return ['message' => 'Belum ada data untuk analisis'];
    }
    
    $mapelSulit = $analisisMapel->where('tingkat_kesulitan', 'Sangat Sulit')->count();
    $mapelMudah = $analisisMapel->where('tingkat_kesulitan', 'Mudah')->count();
    
    return [
        'mapel_perlu_perhatian' => $mapelSulit,
        'mapel_sudah_baik' => $mapelMudah,
        'strategi_umum' => [
            'Review metode pembelajaran untuk mapel kategori sulit',
            'Tingkatkan alokasi waktu untuk mapel bermasalah',
            'Evaluasi kompetensi guru pengampu',
            'Perbaiki materi ajar dan media pembelajaran'
        ]
    ];
    }

    private function generateInsightsPerbandinganKelas($perbandinganKelas)
    {
    if ($perbandinganKelas->isEmpty()) {
        return ['message' => 'Belum ada data perbandingan kelas'];
    }
    
    $selisihTerbesar = $perbandinganKelas->first()['rata_nilai'] - $perbandinganKelas->last()['rata_nilai'];
    
    return [
        'gap_performa' => round($selisihTerbesar, 2),
        'status_gap' => $selisihTerbesar > 15 ? 'Tinggi' : ($selisihTerbesar > 8 ? 'Sedang' : 'Rendah'),
        'rekomendasi' => [
            'Analisis faktor penyebab perbedaan performa',
            'Sharing best practice dari kelas terbaik',
            'Evaluasi kompetensi wali kelas',
            'Pemerataan distribusi guru berpengalaman'
        ]
    ];
    }

    private function calculateTrendDirection($trendBulanan)
    {
    if ($trendBulanan->count() < 2) {
        return 'Belum bisa ditentukan';
    }
    
    $dataPoints = $trendBulanan->values()->all();
    $firstHalf = array_slice($dataPoints, 0, ceil(count($dataPoints) / 2));
    $secondHalf = array_slice($dataPoints, ceil(count($dataPoints) / 2));
    
    $rataAwal = collect($firstHalf)->avg('rata_nilai');
    $rataAkhir = collect($secondHalf)->avg('rata_nilai');
    
    $selisih = $rataAkhir - $rataAwal;
    
    if ($selisih > 2) return 'Meningkat Signifikan';
    if ($selisih > 0.5) return 'Meningkat';
    if ($selisih < -2) return 'Menurun Signifikan';
    if ($selisih < -0.5) return 'Menurun';
    return 'Stabil';
    }

    private function generatePrioritasInputNilai($siswaBelumDinilai)
    {
    if (empty($siswaBelumDinilai)) {
        return ['status' => 'Semua siswa sudah dinilai', 'aksi' => 'Tidak ada aksi yang diperlukan'];
    }
    
    // Group by mata pelajaran
    $groupedByMapel = collect($siswaBelumDinilai)->groupBy('mata_pelajaran');
    
    $prioritas = [];
    foreach ($groupedByMapel as $mapel => $siswa) {
        $prioritas[] = [
            'mata_pelajaran' => $mapel,
            'jumlah_siswa_belum_dinilai' => count($siswa),
            'prioritas' => count($siswa) > 5 ? 'TINGGI' : (count($siswa) > 2 ? 'SEDANG' : 'RENDAH')
        ];
    }
    
    // Sort by jumlah siswa belum dinilai
    $prioritas = collect($prioritas)->sortByDesc('jumlah_siswa_belum_dinilai')->values()->all();
    
    return [
        'status' => 'Ada siswa yang belum dinilai',
        'total_mapel_bermasalah' => count($prioritas),
        'prioritas_per_mapel' => $prioritas,
        'aksi_rekomendasi' => [
            'Prioritaskan input nilai untuk mata pelajaran dengan siswa belum dinilai terbanyak',
            'Set target harian untuk menyelesaikan input nilai',
            'Koordinasi dengan admin jika ada kendala sistem'
        ]
    ];
    }

    private function getMapelPrioritas($analisisPerMapel)
    {
    return collect($analisisPerMapel)
        ->sortBy('persentase_selesai')
        ->take(3)
        ->map(function($mapel) {
            return [
                'mata_pelajaran' => $mapel['mata_pelajaran'],
                'guru' => $mapel['guru'],
                'persentase_selesai' => $mapel['persentase_selesai'],
                'urgency' => $mapel['persentase_selesai'] < 25 ? 'URGENT' : 
                            ($mapel['persentase_selesai'] < 50 ? 'HIGH' : 'MEDIUM')
            ];
        })
        ->values()
        ->all();
    }

    private function generateRekomendasiWaliKelas($analisisPerMapel)
    {
        $mapelBermasalah = collect($analisisPerMapel)->where('persentase_selesai', '<', 80);
        
        if ($mapelBermasalah->isEmpty()) {
            return ['message' => 'Semua mata pelajaran sudah dalam progress baik'];
        }
        
        return [
            'total_mapel_bermasalah' => $mapelBermasalah->count(),
            'guru_perlu_difollow_up' => $mapelBermasalah->pluck('guru')->unique()->values(),
            'aksi_prioritas' => [
                'Koordinasi dengan guru yang progress input nilainya lambat',
                'Monitor deadline input nilai secara berkala',
                'Bantu identifikasi kendala yang dihadapi guru',
                'Laporkan progress ke admin jika diperlukan'
            ]
        ];
    }

    private function getStatusProgress($persentase)
    {
        if ($persentase == 100) return 'SELESAI';
        if ($persentase >= 80) return 'HAMPIR SELESAI';
        if ($persentase >= 50) return 'DALAM PROGRESS';
        if ($persentase >= 20) return 'BARU DIMULAI';
        return 'BELUM DIMULAI';
    }

    private function calculateOverallProgress($progressData)
    {
        if (empty($progressData)) return 0;
        
        $totalPersentase = collect($progressData)->sum('persentase_selesai');
        return round($totalPersentase / count($progressData), 2);
    }

    private function getNextActionRecommendation($progressData)
    {
        $mapelBelumSelesai = collect($progressData)->where('persentase_selesai', '<', 100)->sortBy('persentase_selesai');
        
        if ($mapelBelumSelesai->isEmpty()) {
            return 'Semua mata pelajaran sudah selesai dinilai. Lakukan review dan finalisasi.';
        }
        
        $mapelPrioritas = $mapelBelumSelesai->first();
        
        return "Prioritas: Selesaikan input nilai untuk {$mapelPrioritas['mata_pelajaran']} di kelas {$mapelPrioritas['kelas']}. Progress saat ini: {$mapelPrioritas['persentase_selesai']}%";
        }

        private function getUrgencyList($progressPerMapel)
        {
        return collect($progressPerMapel)
            ->where('persentase_selesai', '<', 75)
            ->sortBy('persentase_selesai')
            ->map(function($mapel) {
                return [
                    'mata_pelajaran' => $mapel['mata_pelajaran'],
                    'guru' => $mapel['guru'],
                    'persentase_selesai' => $mapel['persentase_selesai'],
                    'action_needed' => 'Follow up dengan guru ' . $mapel['guru']
                ];
            })
            ->values()
            ->all();
    }

    private function getComprehensiveAcademicOverview($tahunAjaranId, $userRole)
    {
        $data = [
            'statistik_umum' => $this->getGeneralAcademicStats($tahunAjaranId, $userRole),
            'siswa_analisis' => $this->getSiswaAnalysis($tahunAjaranId, $userRole),
            'mapel_analisis' => $this->getMapelDifficultyAnalysis($tahunAjaranId, $userRole),
            'guru_performance' => $this->getGuruPerformanceAnalysis($tahunAjaranId, $userRole),
            'kelas_comparison' => $this->getClassComparisonAnalysis($tahunAjaranId, $userRole),
            'progress_rapor' => $this->getRaporProgressAnalysis($tahunAjaranId, $userRole)
        ];
        
        return $data;
    }

    private function getRaporProgressAnalysis($tahunAjaranId, $userRole)
    {
        $query = Siswa::with(['kelas', 'nilais', 'absensi']);
        $query = $this->applyRoleFilterToSiswa($query, $userRole);
        $siswas = $query->get();
        
        $raporStats = [
            'siap_rapor' => 0,
            'belum_siap' => 0,
            'progress_per_kelas' => [],
            'masalah_umum' => []
        ];
        
        $kelasProgress = [];
        
        foreach ($siswas as $siswa) {
            $kelasName = $siswa->kelas->nomor_kelas . $siswa->kelas->nama_kelas;
            
            if (!isset($kelasProgress[$kelasName])) {
                $kelasProgress[$kelasName] = [
                    'total' => 0,
                    'siap' => 0,
                    'belum_siap' => 0
                ];
            }
            
            $kelasProgress[$kelasName]['total']++;
            
            $diagnosis = $siswa->diagnoseDataCompleteness();
            if ($diagnosis['complete']) {
                $raporStats['siap_rapor']++;
                $kelasProgress[$kelasName]['siap']++;
            } else {
                $raporStats['belum_siap']++;
                $kelasProgress[$kelasName]['belum_siap']++;
                
                if (!$diagnosis['nilai_status']) {
                    $raporStats['masalah_umum'][] = $diagnosis['nilai_message'];
                }
                if (!$diagnosis['absensi_status']) {
                    $raporStats['masalah_umum'][] = $diagnosis['absensi_message'];
                }
            }
        }
        
        foreach ($kelasProgress as $kelas => $data) {
            $raporStats['progress_per_kelas'][] = [
                'kelas' => $kelas,
                'total_siswa' => $data['total'],
                'siap_rapor' => $data['siap'],
                'persentase' => $data['total'] > 0 ? round(($data['siap'] / $data['total']) * 100, 2) : 0
            ];
        }
        
        return [
            'total_siswa' => $siswas->count(),
            'siap_rapor' => $raporStats['siap_rapor'],
            'belum_siap_rapor' => $raporStats['belum_siap'],
            'persentase_kesiapan' => $siswas->count() > 0 ? 
                round(($raporStats['siap_rapor'] / $siswas->count()) * 100, 2) : 0,
            'progress_per_kelas' => collect($raporStats['progress_per_kelas'])
                ->sortByDesc('persentase')->values()->toArray(),
            'masalah_umum' => array_unique($raporStats['masalah_umum']),
            'context' => 'Analisis kesiapan rapor'
        ];
    }

    private function calculateStandardDeviation($numbers)
    {
        if ($numbers->isEmpty()) return 0;
        
        $mean = $numbers->avg();
        $variance = $numbers->map(function($num) use ($mean) {
            return pow($num - $mean, 2);
        })->avg();
        
        return sqrt($variance);
    }

    private function calculateAboveKkmPercentage($nilais)
    {
        if ($nilais->isEmpty()) return 0;
        
        $aboveKkm = $nilais->where('nilai_akhir_rapor', '>=', 70)->count();
        return round(($aboveKkm / $nilais->count()) * 100, 2);
    }

    private function getSiswaBelumDinilaiCount($tahunAjaranId, $userRole)
    {
        // Implementasi sederhana untuk menghitung siswa yang belum dinilai
        $query = Siswa::whereDoesntHave('nilais', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        });
        
        $query = $this->applyRoleFilterToSiswa($query, $userRole);
        return $query->count();
    }

    private function generateMapelRecommendations($mapelStats)
    {
        $sulit = $mapelStats->where('tingkat_kesulitan', 'Sangat Sulit')->count();
        $mudah = $mapelStats->where('tingkat_kesulitan', 'Mudah')->count();
        
        $recommendations = [];
        
        if ($sulit > 0) {
            $recommendations[] = "Review metode pembelajaran untuk {$sulit} mata pelajaran kategori sangat sulit";
        }
        
        if ($mudah > 3) {
            $recommendations[] = "Pertimbangkan peningkatan standar untuk mata pelajaran yang terlalu mudah";
        }
        
        return $recommendations;
    }

    private function getInputStatus($completed, $expected)
    {
        if ($expected == 0) return 'Tidak ada siswa';
        
        $percentage = ($completed / $expected) * 100;
        
        if ($percentage == 100) return 'Selesai';
        if ($percentage >= 75) return 'Hampir Selesai';
        if ($percentage >= 50) return 'Dalam Progress';
        if ($percentage > 0) return 'Baru Dimulai';
        return 'Belum Dimulai';
    }

    private function getKelasCompletionRate($kelas)
    {
        $totalExpected = 0;
        $totalCompleted = 0;
        
        foreach ($kelas->mataPelajarans as $mapel) {
            $siswaCount = $kelas->siswas->count();
            $nilaiCount = Nilai::where('mata_pelajaran_id', $mapel->id)
                ->whereNotNull('nilai_akhir_rapor')
                ->count();
            
            $totalExpected += $siswaCount;
            $totalCompleted += $nilaiCount;
        }
        
        return $totalExpected > 0 ? round(($totalCompleted / $totalExpected) * 100, 2) : 0;
    }

    private function getMapelDifficultyAnalysis($tahunAjaranId, $userRole)
    {
        $query = MataPelajaran::with(['nilais' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        }, 'guru', 'kelas'])->where('tahun_ajaran_id', $tahunAjaranId);
        
        $query = $this->applyRoleFilterToMapel($query, $userRole);
        $mataPelajarans = $query->get();
        
        $mapelStats = $mataPelajarans->map(function($mapel) {
            $nilais = $mapel->nilais->pluck('nilai_akhir_rapor');
            if ($nilais->isEmpty()) return null;
            
            $rataRata = $nilais->avg();
            $tingkatKesulitan = $this->kategorikanTingkatKesulitan($rataRata);
            
            return [
                'nama_mapel' => $mapel->nama_pelajaran,
                'kelas' => $mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas,
                'guru' => $mapel->guru->nama,
                'rata_rata' => round($rataRata, 2),
                'tingkat_kesulitan' => $tingkatKesulitan,
                'total_siswa' => $nilais->count(),
                'siswa_di_atas_80' => $nilais->where('>=', 80)->count(),
                'siswa_di_bawah_70' => $nilais->where('<', 70)->count(),
                'standar_deviasi' => round($this->calculateStandardDeviation($nilais), 2)
            ];
        })->filter()->sortBy('rata_rata');
        
        return [
            'mata_pelajaran_tersulit' => $mapelStats->take(5)->values()->toArray(),
            'mata_pelajaran_termudah' => $mapelStats->sortByDesc('rata_rata')->take(5)->values()->toArray(),
            'distribusi_kesulitan' => [
                'sangat_sulit' => $mapelStats->where('tingkat_kesulitan', 'Sangat Sulit')->count(),
                'sulit' => $mapelStats->where('tingkat_kesulitan', 'Sulit')->count(),
                'sedang' => $mapelStats->where('tingkat_kesulitan', 'Sedang')->count(),
                'mudah' => $mapelStats->where('tingkat_kesulitan', 'Mudah')->count()
            ],
            'rekomendasi' => $this->generateMapelRecommendations($mapelStats),
            'context' => 'Analisis tingkat kesulitan mata pelajaran'
        ];
    }

    private function getClassComparisonAnalysis($tahunAjaranId, $userRole)
    {
        $query = Kelas::with(['siswas.nilais' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        }, 'mataPelajarans.guru'])->where('tahun_ajaran_id', $tahunAjaranId);
        
        if ($userRole === 'wali_kelas') {
            $guru = Auth::guard('guru')->user();
            $kelasId = $guru->getWaliKelasId();
            $query->where('id', $kelasId);
        }
        
        $kelasList = $query->get();
        
        $kelasStats = $kelasList->map(function($kelas) {
            $allNilai = $kelas->siswas->flatMap->nilais->pluck('nilai_akhir_rapor');
            $waliKelas = $kelas->getWaliKelas();
            
            return [
                'kelas' => $kelas->nomor_kelas . $kelas->nama_kelas,
                'wali_kelas' => $waliKelas ? $waliKelas->nama : 'Belum ditentukan',
                'jumlah_siswa' => $kelas->siswas->count(),
                'rata_rata' => $allNilai->isNotEmpty() ? round($allNilai->avg(), 2) : 0,
                'nilai_tertinggi' => $allNilai->max() ?? 0,
                'nilai_terendah' => $allNilai->min() ?? 0,
                'siswa_di_atas_kkm' => $allNilai->where('>=', 70)->count(),
                'completion_rate' => $this->getKelasCompletionRate($kelas)
            ];
        })->sortByDesc('rata_rata');
        
        return [
            'kelas_terbaik' => $kelasStats->take(3)->values()->toArray(),
            'kelas_perlu_perhatian' => $kelasStats->sortBy('rata_rata')->take(3)->values()->toArray(),
            'perbandingan_lengkap' => $kelasStats->values()->toArray(),
            'gap_performa' => $kelasStats->isNotEmpty() ? 
                round($kelasStats->first()['rata_rata'] - $kelasStats->last()['rata_rata'], 2) : 0,
            'context' => 'Perbandingan performa antar kelas'
        ];
    }

    private function getGuruPerformanceAnalysis($tahunAjaranId, $userRole)
    {
        if ($userRole !== 'admin') {
            return ['message' => 'Analisis performa guru hanya tersedia untuk admin'];
        }
        
        $gurus = Guru::whereHas('mataPelajarans', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId);
        })->with(['mataPelajarans' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->with(['kelas.siswas', 'nilais' => function($nq) use ($tahunAjaranId) {
                $nq->where('tahun_ajaran_id', $tahunAjaranId);
            }]);
        }])->get();
        
        $guruStats = $gurus->map(function($guru) {
            $totalExpected = 0;
            $totalCompleted = 0;
            $allNilai = collect();
            
            foreach ($guru->mataPelajarans as $mapel) {
                $siswaCount = $mapel->kelas->siswas->count();
                $nilaiCount = $mapel->nilais->whereNotNull('nilai_akhir_rapor')->count();
                
                $totalExpected += $siswaCount;
                $totalCompleted += $nilaiCount;
                $allNilai = $allNilai->merge($mapel->nilais->pluck('nilai_akhir_rapor')->filter());
            }
            
            return [
                'nama_guru' => $guru->nama,
                'completion_rate' => $totalExpected > 0 ? round(($totalCompleted / $totalExpected) * 100, 2) : 0,
                'rata_rata_nilai' => $allNilai->isNotEmpty() ? round($allNilai->avg(), 2) : 0,
                'jumlah_mapel' => $guru->mataPelajarans->count(),
                'total_siswa_diajar' => $totalExpected,
                'status_input' => $this->getInputStatus($totalCompleted, $totalExpected),
                'mata_pelajaran' => $guru->mataPelajarans->map(function($mapel) {
                    return [
                        'nama' => $mapel->nama_pelajaran,
                        'kelas' => $mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas
                    ];
                })
            ];
        });
        
        return [
            'guru_terbaik_completion' => $guruStats->sortByDesc('completion_rate')->take(5)->values(),
            'guru_perlu_followup' => $guruStats->where('completion_rate', '<', 75)->sortBy('completion_rate')->values(),
            'guru_belum_input' => $guruStats->where('completion_rate', 0)->values(),
            'guru_sudah_selesai' => $guruStats->where('completion_rate', 100)->values(),
            'rata_rata_completion' => round($guruStats->avg('completion_rate'), 2),
            'context' => 'Analisis performa input nilai guru'
        ];
    }
    
    private function getSiswaAnalysis($tahunAjaranId, $userRole)
    {
        $query = Siswa::whereHas('nilais', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        });
        
        $query = $this->applyRoleFilterToSiswa($query, $userRole);
        
        $siswas = $query->with(['kelas', 'nilais' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor')
            ->with('mataPelajaran');
        }])->get();
        
        $siswaStats = $siswas->map(function($siswa) {
            $nilaiSiswa = $siswa->nilais;
            $rataRata = $nilaiSiswa->avg('nilai_akhir_rapor');
            
            return [
                'nama' => $siswa->nama,
                'kelas' => $siswa->kelas->nomor_kelas . $siswa->kelas->nama_kelas,
                'rata_rata' => round($rataRata, 2),
                'jumlah_mapel' => $nilaiSiswa->count(),
                'nilai_tertinggi' => $nilaiSiswa->max('nilai_akhir_rapor'),
                'nilai_terendah' => $nilaiSiswa->min('nilai_akhir_rapor'),
                'mapel_lemah' => $nilaiSiswa->where('nilai_akhir_rapor', '<', 70)->count(),
                'mapel_unggul' => $nilaiSiswa->where('nilai_akhir_rapor', '>=', 85)->count()
            ];
        });
        
        return [
            'total_siswa_aktif' => $siswas->count(),
            'siswa_terbaik' => $siswaStats->sortByDesc('rata_rata')->take(5)->values()->toArray(),
            'siswa_perlu_perhatian' => $siswaStats->where('rata_rata', '<', 75)->sortBy('rata_rata')->take(10)->values()->toArray(),
            'siswa_belum_dinilai' => $this->getSiswaBelumDinilaiCount($tahunAjaranId, $userRole),
            'distribusi_performa' => [
                'sangat_baik' => $siswaStats->where('rata_rata', '>=', 85)->count(),
                'baik' => $siswaStats->whereBetween('rata_rata', [75, 84])->count(),
                'cukup' => $siswaStats->whereBetween('rata_rata', [65, 74])->count(),
                'perlu_bimbingan' => $siswaStats->where('rata_rata', '<', 65)->count()
            ],
            'context' => 'Analisis komprehensif siswa'
        ];
    }
    private function getGeneralAcademicStats($tahunAjaranId, $userRole)
    {
        $query = Nilai::where('tahun_ajaran_id', $tahunAjaranId)
            ->whereNotNull('nilai_akhir_rapor');
        
        $query = $this->applyRoleFilterToNilai($query, $userRole);
        $nilais = $query->get();
        
        if ($nilais->isEmpty()) {
            return ['message' => 'Belum ada data nilai'];
        }
        
        $rataKeseluruhan = $nilais->avg('nilai_akhir_rapor');
        
        return [
            'total_nilai_entries' => $nilais->count(),
            'rata_rata_keseluruhan' => round($rataKeseluruhan, 2),
            'nilai_tertinggi' => $nilais->max('nilai_akhir_rapor'),
            'nilai_terendah' => $nilais->min('nilai_akhir_rapor'),
            'standar_deviasi' => round($this->calculateStandardDeviation($nilais->pluck('nilai_akhir_rapor')), 2),
            'distribusi_grade' => [
                'A' => $nilais->where('nilai_akhir_rapor', '>=', 90)->count(),
                'B' => $nilais->whereBetween('nilai_akhir_rapor', [80, 89])->count(),
                'C' => $nilais->whereBetween('nilai_akhir_rapor', [70, 79])->count(),
                'D' => $nilais->where('nilai_akhir_rapor', '<', 70)->count()
            ],
            'persentase_di_atas_kkm' => $this->calculateAboveKkmPercentage($nilais),
            'context' => 'Statistik akademik keseluruhan'
        ];
    }

    private function getOverviewSiswaBelumDinilaiAdmin($tahunAjaranId)
    {
        // Ambil semua mata pelajaran di tahun ajaran ini
        $allMataPelajaran = MataPelajaran::where('tahun_ajaran_id', $tahunAjaranId)
            ->with(['kelas.siswas', 'guru'])
            ->get();
        
        $totalEntriesExpected = 0;
        $totalEntriesMissing = 0;
        $guruProgressData = [];
        $kelasProgressData = [];
        $urgentActions = [];
        
        foreach ($allMataPelajaran as $mapel) {
            $siswaCount = $mapel->kelas->siswas->count();
            $nilaiCount = Nilai::where('mata_pelajaran_id', $mapel->id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->whereNotNull('nilai_akhir_rapor')
                ->count();
            
            $missingCount = $siswaCount - $nilaiCount;
            $totalEntriesExpected += $siswaCount;
            $totalEntriesMissing += $missingCount;
            
            // Progress per guru
            $guruId = $mapel->guru_id;
            if (!isset($guruProgressData[$guruId])) {
                $guruProgressData[$guruId] = [
                    'nama_guru' => $mapel->guru->nama,
                    'total_expected' => 0,
                    'total_completed' => 0,
                    'mata_pelajaran' => []
                ];
            }
            
            $guruProgressData[$guruId]['total_expected'] += $siswaCount;
            $guruProgressData[$guruId]['total_completed'] += $nilaiCount;
            $guruProgressData[$guruId]['mata_pelajaran'][] = [
                'nama' => $mapel->nama_pelajaran,
                'kelas' => $mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas,
                'progress' => $siswaCount > 0 ? round(($nilaiCount / $siswaCount) * 100, 2) : 0
            ];
            
            // Progress per kelas
            $kelasId = $mapel->kelas_id;
            if (!isset($kelasProgressData[$kelasId])) {
                $kelasProgressData[$kelasId] = [
                    'nama_kelas' => $mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas,
                    'total_expected' => 0,
                    'total_completed' => 0,
                    'mata_pelajaran_count' => 0
                ];
            }
            
            $kelasProgressData[$kelasId]['total_expected'] += $siswaCount;
            $kelasProgressData[$kelasId]['total_completed'] += $nilaiCount;
            $kelasProgressData[$kelasId]['mata_pelajaran_count']++;
            
            // Urgent actions
            if ($missingCount > 0) {
                $progressPercent = $siswaCount > 0 ? round(($nilaiCount / $siswaCount) * 100, 2) : 0;
                if ($progressPercent < 50) {
                    $urgentActions[] = [
                        'mata_pelajaran' => $mapel->nama_pelajaran,
                        'kelas' => $mapel->kelas->nomor_kelas . $mapel->kelas->nama_kelas,
                        'guru' => $mapel->guru->nama,
                        'siswa_belum_dinilai' => $missingCount,
                        'progress_percent' => $progressPercent,
                        'priority' => $progressPercent < 25 ? 'URGENT' : 'HIGH'
                    ];
                }
            }
        }
        
        // Hitung completion rate per guru
        foreach ($guruProgressData as &$guru) {
            $guru['completion_rate'] = $guru['total_expected'] > 0 ? 
                round(($guru['total_completed'] / $guru['total_expected']) * 100, 2) : 0;
        }
        
        // Hitung completion rate per kelas
        foreach ($kelasProgressData as &$kelas) {
            $kelas['completion_rate'] = $kelas['total_expected'] > 0 ? 
                round(($kelas['total_completed'] / $kelas['total_expected']) * 100, 2) : 0;
        }
        
        // Sort dan ambil yang terburuk
        $guruProgressSorted = collect($guruProgressData)->sortBy('completion_rate')->values();
        $kelasProgressSorted = collect($kelasProgressData)->sortBy('completion_rate')->values();
        
        return [
            'total_entries_expected' => $totalEntriesExpected,
            'total_entries_missing' => $totalEntriesMissing,
            'overall_completion' => $totalEntriesExpected > 0 ? 
                round((($totalEntriesExpected - $totalEntriesMissing) / $totalEntriesExpected) * 100, 2) : 0,
            'guru_progress_ranking' => $guruProgressSorted->take(10)->toArray(),
            'kelas_progress_ranking' => $kelasProgressSorted->take(10)->toArray(),
            'urgent_actions' => collect($urgentActions)->sortBy('progress_percent')->take(15)->values()->toArray(),
            'top_urgent_gurus' => $guruProgressSorted->where('completion_rate', '<', 75)->take(5)->toArray(),
            'context' => 'Overview lengkap kelengkapan input nilai seluruh sekolah'
        ];
    }

    private function analyzeGuruProgress($tahunAjaranId)
    {
        $allGuru = Guru::whereHas('mataPelajarans', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId);
        })->with(['mataPelajarans' => function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId)->with('kelas.siswas');
        }])->get();
        
        $guruAnalysis = [];
        
        foreach ($allGuru as $guru) {
            $totalExpected = 0;
            $totalCompleted = 0;
            
            foreach ($guru->mataPelajarans as $mapel) {
                $siswaCount = $mapel->kelas->siswas->count();
                $nilaiCount = Nilai::where('mata_pelajaran_id', $mapel->id)
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->whereNotNull('nilai_akhir_rapor')
                    ->count();
                
                $totalExpected += $siswaCount;
                $totalCompleted += $nilaiCount;
            }
            
            $guruAnalysis[] = [
                'nama_guru' => $guru->nama,
                'total_expected' => $totalExpected,
                'total_completed' => $totalCompleted,
                'completion_rate' => $totalExpected > 0 ? round(($totalCompleted / $totalExpected) * 100, 2) : 0,
                'mata_pelajaran_count' => $guru->mataPelajarans->count()
            ];
        }
        
        return collect($guruAnalysis)->sortBy('completion_rate')->values()->all();
    }

    private function getUrgentActionsAdmin($kelasData)
    {
            $urgentActions = [];
            
            foreach ($kelasData as $kelas) {
                foreach ($kelas['mata_pelajaran_analysis'] as $mapel) {
                    if ($mapel['persentase_selesai'] < 50) {
                        $urgentActions[] = [
                            'action' => 'Follow up dengan guru ' . $mapel['guru'],
                            'detail' => 'Mata pelajaran ' . $mapel['mata_pelajaran'] . ' di ' . $kelas['kelas'] . ' baru ' . $mapel['persentase_selesai'] . '% selesai',
                            'priority' => $mapel['persentase_selesai'] < 25 ? 'URGENT' : 'HIGH'
                        ];
                    }
                }
        }
        
        return $urgentActions;
    }

    private function buildNilaiAnalysisPrompt($userMessage, $databaseData, $intent)
    {
        $userRole = $this->getUserRole();
        $roleContext = $this->getRoleContext($userRole);
        
        // Compact data untuk mencegah prompt terlalu panjang
        $compactData = $this->compactDatabaseData($databaseData, $intent);
        
        $systemPrompt = "Anda adalah AI Assistant untuk SISTEM RAPOR SDIT AL-HIDAYAH yang mengkhususkan diri dalam analisis data akademik real-time.

    === KONTEKS PENGGUNA ===
    Role: {$roleContext['role']}
    Akses: {$roleContext['akses']}
    Fokus: {$roleContext['fokus']}

    === DATA ANALISIS AKADEMIK REAL-TIME ===
    Intent: {$intent}
    " . json_encode($compactData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "
    === END DATA ANALISIS ===

    === KNOWLEDGE BASE SISTEM (UNTUK PANDUAN TEKNIS) ===
    {$this->systemContext}
    === END KNOWLEDGE BASE ===

    === KEMAMPUAN ANALISIS UTAMA ===
    1. **STATISTIK AKADEMIK**: Analisis nilai, rata-rata, distribusi, standar deviasi
    2. **IDENTIFIKASI SISWA**: 
    - Siswa berprestasi tinggi dan yang memerlukan perhatian
    - Siswa yang belum dinilai atau data tidak lengkap
    3. **ANALISIS MATA PELAJARAN**: 
    - Mata pelajaran tersulit dan termudah berdasarkan rata-rata nilai
    - Tingkat kesulitan dan rekomendasi pembelajaran
    4. **PERFORMA GURU**: 
    - Progress input nilai per guru
    - Guru yang sudah selesai vs yang belum
    - Completion rate dan efisiensi
    5. **PERBANDINGAN KELAS**: 
    - Ranking kelas berdasarkan performa
    - Gap performa antar kelas
    6. **PROGRESS RAPOR**: 
    - Kesiapan data untuk generate rapor
    - Siswa siap vs belum siap rapor
    7. **TREND & PERKEMBANGAN**: 
    - Pola perkembangan nilai dari waktu ke waktu

    === INSTRUKSI RESPONSE ===
    1. **FOKUS PADA DATA KONKRET**: Selalu sertakan angka, persentase, dan statistik spesifik
    2. **ACTIONABLE INSIGHTS**: Berikan rekomendasi yang dapat ditindaklanjuti
    3. **PRIORITAS BERDASARKAN URGENSI**: Identifikasi hal yang perlu perhatian segera
    4. **KONTEKSTUAL UNTUK ROLE**: Sesuaikan analisis dengan peran pengguna
    5. **BAHASA PROFESIONAL**: Gunakan bahasa yang mudah dipahami namun tetap profesional

    === FORMAT RESPONSE YANG DIHARAPKAN ===
    - **RINGKASAN EKSEKUTIF**: 2-3 kalimat key findings
    - **DETAIL ANALISIS**: Data spesifik dengan interpretasi
    - **REKOMENDASI TINDAKAN**: Action items yang spesifik dan dapat dilakukan
    - **PRIORITAS**: Urutkan berdasarkan urgensi dan dampak

    === CONTOH ANALISIS YANG BAIK ===
    \"Berdasarkan data real-time, rata-rata nilai keseluruhan adalah 78.5 dengan 15% siswa memerlukan perhatian khusus. Mata pelajaran Matematika menunjukkan tingkat kesulitan tertinggi (rata-rata 72.1). Untuk tindakan segera: 1) Follow up dengan 3 guru yang completion rate <50%, 2) Program remedial untuk 12 siswa dengan nilai <70, 3) Review metode pembelajaran Matematika.\"

    PERTANYAAN USER: {$userMessage}

    ";

        return $systemPrompt;
    }

    private function getRoleContext($userRole)
    {
        switch ($userRole) {
            case 'admin':
                return [
                    'role' => 'Administrator Sekolah',
                    'akses' => 'Semua data nilai di sekolah',
                    'fokus' => 'Monitoring keseluruhan performa akademik sekolah'
                ];
            case 'guru':
                return [
                    'role' => 'Guru Mata Pelajaran',
                    'akses' => 'Data nilai mata pelajaran yang diajar',
                    'fokus' => 'Efisiensi pengelolaan nilai di mata pelajaran tertentu'
                ];
            case 'wali_kelas':
                return [
                    'role' => 'Wali Kelas',
                    'akses' => 'Data nilai semua siswa di kelas yang diwalikan',
                    'fokus' => 'Monitoring dan pembinaan akademik siswa di kelas'
                ];
            default:
                return [
                    'role' => 'Pengguna',
                    'akses' => 'Data terbatas',
                    'fokus' => 'Informasi umum'
                ];
        }
    }

    public function testNilaiAnalysis()
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        $userRole = $this->getUserRole();
        
        $testData = [
            'role' => $userRole,
            'tahun_ajaran_id' => $tahunAjaranId,
            'sample_overview' => $this->getNilaiOverview($tahunAjaranId, $userRole),
            'sample_siswa_lemah' => $this->getSiswaLemah($tahunAjaranId, $userRole),
            'sample_progress' => $this->getProgressInputNilai($tahunAjaranId, $userRole)
        ];
        
        return response()->json([
            'success' => true,
            'test_data' => $testData,
            'message' => 'AI Nilai Analysis siap digunakan!'
        ]);
    }
    public function clearHistory()
    {
        try {
            $userId = null;
            
            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('guru')->check()) {
                $userId = Auth::guard('guru')->id();
            }
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Hapus semua chat history untuk user ini
            $deletedCount = GeminiChat::where('user_id', $userId)->delete();
            
            Log::info("Chat history cleared for user {$userId}, deleted {$deletedCount} records");
            
            return response()->json([
                'success' => true,
                'message' => 'Riwayat chat berhasil dihapus',
                'deleted_count' => $deletedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error clearing chat history: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus riwayat'
            ], 500);
        }
    }

    public function deleteChat($chatId)
    {
        try {
            $userId = null;
            
            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('guru')->check()) {
                $userId = Auth::guard('guru')->id();
            }
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Hapus chat spesifik milik user ini
            $deleted = GeminiChat::where('id', $chatId)
                                ->where('user_id', $userId)
                                ->delete();
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chat berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat tidak ditemukan atau tidak memiliki akses'
                ], 404);
            }
            
        } catch (\Exception $e) {
            Log::error('Error deleting specific chat: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus chat'
            ], 500);
        }
    }
}