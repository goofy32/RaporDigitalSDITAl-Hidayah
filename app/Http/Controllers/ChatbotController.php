<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\AudioEncoding;
use Google\Cloud\Dialogflow\V2\InputAudioConfig;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected $projectId;
    protected $languageCode;

    public function __construct()
    {
        $this->projectId = env('DIALOGFLOW_PROJECT_ID');
        $this->languageCode = env('DIALOGFLOW_LANGUAGE', 'id');
        
        // Set credentials path globally for Google Cloud
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . env('GOOGLE_APPLICATION_CREDENTIALS'));
    }

    public function index()
    {
        return view('chatbot');
    }

    public function sendText(Request $request)
    {
        $text = $request->input('text');
        
        try {
            $sessionsClient = new SessionsClient();
            $session = $sessionsClient->sessionName($this->projectId, session()->getId());
            
            // Create text input
            $textInput = new TextInput();
            $textInput->setText($text);
            $textInput->setLanguageCode($this->languageCode);
            
            // Create query input
            $queryInput = new QueryInput();
            $queryInput->setText($textInput);
            
            // Get response
            $response = $sessionsClient->detectIntent($session, $queryInput);
            $queryResult = $response->getQueryResult();
            $fulfillmentText = $queryResult->getFulfillmentText();
            
            $sessionsClient->close();
            
            return response()->json([
                'success' => true,
                'message' => $fulfillmentText,
                'intent' => $queryResult->getIntent()->getDisplayName(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendAudio(Request $request)
    {
        try {
            // Validasi audio input
            $request->validate([
                'audio' => 'required',
            ]);
            
            \Log::info('Audio request received', [
                'content_length' => strlen($request->audio)
            ]);
            
            // Decode base64 audio
            $audioData = base64_decode(preg_replace('#^data:audio/[^;]+;base64,#', '', $request->audio));
            
            // Set credentials
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . env('GOOGLE_APPLICATION_CREDENTIALS'));
            
            $sessionsClient = new SessionsClient();
            $session = $sessionsClient->sessionName($this->projectId, session()->getId());
            
            // Updated audio configuration - using 48000 Hz for WEBM OPUS
            $audioConfig = new InputAudioConfig();
            $audioConfig->setAudioEncoding(AudioEncoding::AUDIO_ENCODING_AUDIO_ENCODING_UNSPECIFIED); // Let Dialogflow detect the encoding
            $audioConfig->setLanguageCode($this->languageCode);
            // Either set to 48000 or remove this line to let Dialogflow detect it
            $audioConfig->setSampleRateHertz(48000);
            
            // Create query input
            $queryInput = new QueryInput();
            $queryInput->setAudioConfig($audioConfig);
            
            \Log::info('Sending audio to Dialogflow', [
                'session' => $session,
                'audio_length' => strlen($audioData),
                'sample_rate' => 48000
            ]);
            
            $response = $sessionsClient->detectIntent($session, $queryInput, ['inputAudio' => $audioData]);
            $queryResult = $response->getQueryResult();
            $fulfillmentText = $queryResult->getFulfillmentText();
            
            $sessionsClient->close();
            
            return response()->json([
                'success' => true,
                'message' => $fulfillmentText,
                'detected_text' => $queryResult->getQueryText(),
                'intent' => $queryResult->getIntent()->getDisplayName(),
            ]);
        } catch (\Exception $e) {
            // Extracting more detailed error message if it's a JSON
            $errorMessage = $e->getMessage();
            $errorDetails = "";
            
            // Try to parse JSON error message
            if (strpos($errorMessage, '{') !== false) {
                try {
                    $jsonStart = strpos($errorMessage, '{');
                    $jsonPart = substr($errorMessage, $jsonStart);
                    $errorData = json_decode($jsonPart, true);
                    if ($errorData && isset($errorData['message'])) {
                        $errorDetails = $errorData['message'];
                    }
                } catch (\Exception $jsonEx) {
                    // If JSON parsing fails, just use the original message
                }
            }
            
            \Log::error('Audio processing error', [
                'message' => $e->getMessage(),
                'details' => $errorDetails,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Maaf, tidak dapat memproses rekaman suara. ' . 
                            (env('APP_DEBUG') ? "Detail: $errorDetails" : ""),
            ], 500);
        }
    }
}