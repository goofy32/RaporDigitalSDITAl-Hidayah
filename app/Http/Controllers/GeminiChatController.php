<?php

namespace App\Http\Controllers;

use App\Models\GeminiChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GeminiChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = $request->message;
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent?key={$apiKey}";

        try {
            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $message
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 800,
                ]
            ]);

            $data = $response->json();
            $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak dapat memproses pesan Anda saat ini.';

            if (!$response->successful()) {
                \Log::error('Gemini API Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'API tidak merespon dengan benar: ' . $response->status()
                ]);
            }

            // Simpan chat ke database
            $chat = GeminiChat::create([
                'user_id' => Auth::id(),
                'message' => $message,
                'response' => $aiResponse
            ]);

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'chat' => $chat
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
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
}