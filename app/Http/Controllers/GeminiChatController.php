<?php

namespace App\Http\Controllers;

use App\Models\GeminiChat;
use Illuminate\Http\Request;
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

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->message;
        
        // Debug logging
        Log::info('User message received: ' . $userMessage);
        Log::info('Knowledge base length: ' . strlen($this->systemContext));
        
        $apiKey = env('GEMINI_API_KEY');
        
        // Pastikan API key ada
        if (!$apiKey) {
            Log::error('GEMINI_API_KEY not found in .env');
            return response()->json([
                'success' => false,
                'message' => 'API key tidak ditemukan. Periksa konfigurasi.'
            ], 500);
        }
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key={$apiKey}";

        // Buat prompt dengan context knowledge base
        $contextualPrompt = $this->buildContextualPrompt($userMessage);
        
        // Debug: log prompt yang dikirim
        Log::info('Contextual prompt: ' . substr($contextualPrompt, 0, 500) . '...');

        try {
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
                    'maxOutputTokens' => 1200,
                    'topP' => 0.8,
                    'topK' => 40,
                ]
            ]);

            // Debug: log response status
            Log::info('Gemini API response status: ' . $response->status());
            Log::info('Gemini API response body: ' . $response->body());

            if (!$response->successful()) {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'API tidak merespons dengan benar. Status: ' . $response->status()
                ], 500);
            }

            $data = $response->json();
            
            // Debug: log parsed data
            Log::info('Parsed response data: ' . json_encode($data));
            
            $aiResponse = $this->extractResponse($data);
            
            if (!$aiResponse) {
                Log::error('No response text found in API response');
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengekstrak respons dari API'
                ], 500);
            }

            // Bersihkan response
            $aiResponse = $this->cleanResponse($aiResponse);
            
            // Debug: log final response
            Log::info('Final AI response: ' . $aiResponse);

            // Simpan chat ke database
            $chat = GeminiChat::create([
                'user_id' => Auth::id(),
                'message' => $userMessage,
                'response' => $aiResponse
            ]);

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'chat' => $chat
            ]);

        } catch (\Exception $e) {
            Log::error('Gemini Chat Exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
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
        $chats = GeminiChat::where('user_id', Auth::id())
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
}