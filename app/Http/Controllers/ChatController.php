<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\ThreatScenario;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    public function index()
    {
        $sessions = Auth::user()->chatSessions()
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('chat.index', compact('sessions'));
    }

    public function getSessions()
    {
        try {
            $sessions = Auth::user()->chatSessions()
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'sessions' => $sessions
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching chat sessions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load chat sessions'
            ], 500);
        }
    }

    public function createSession(Request $request)
    {
        try {
            
            $session = ChatSession::create([
                'user_id' => Auth::id(),
                'title' => 'New Chat Session',
                'status' => 'active',
                'current_step' => 'greeting',
                'context_data' => json_encode([]), // Ensure JSON format
            ]);

    
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'message' => 'Welcome to the Cyber Threat Education Bot! I\'m here to help you learn about cybersecurity threats that municipal employees commonly face. What would you like to learn about today?',
                'is_bot' => true,
                'message_type' => 'greeting',
                'metadata' => json_encode(['options' => ['phishing', 'malware']]), // Ensure JSON format
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $session->id,
                'message' => 'New session created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create chat session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'session_id' => 'required|exists:chat_sessions,id',
                'message' => 'required|string|max:1000',
            ]);

            $session = ChatSession::findOrFail($request->session_id);
            
            
            if ($session->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

        
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'message' => $request->message,
                'is_bot' => false,
                'message_type' => 'user_input',
                'metadata' => json_encode([]),
            ]);

            
            $response = $this->chatbotService->processMessage($session, $request->message);

            
            $metadata = isset($response['metadata']) ? $response['metadata'] : [];

            
            $botMessage = ChatMessage::create([
                'chat_session_id' => $session->id,
                'message' => $response['message'],
                'is_bot' => true,
                'message_type' => $response['type'],
                'metadata' => json_encode($metadata), 
            ]);

  
            $session->update([
                'current_step' => $response['next_step'],
                'context_data' => json_encode($response['context_data']), 
                'updated_at' => now(),
            ]);


            if ($session->title === 'New Chat Session') {
                $session->update([
                    'title' => $this->generateSessionTitle($request->message)
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $response['message'],
                'type' => $response['type'],
                'metadata' => $metadata,
                'session_updated' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSession(Request $request, $sessionId)
    {
        try {
            $session = ChatSession::with(['messages' => function($query) {
                $query->orderBy('created_at', 'asc');
            }])->where('user_id', Auth::id())->findOrFail($sessionId);

            $formattedMessages = $session->messages->map(function ($message) {
                $metadata = [];
                try {
                    if (!empty($message->metadata)) {
                        if (is_string($message->metadata)) {
                            $metadata = json_decode($message->metadata, true) ?: [];
                        } else {
                            $metadata = $message->metadata;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error parsing message metadata: ' . $e->getMessage());
                    $metadata = [];
                }

                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_bot' => (bool)$message->is_bot,
                    'type' => $message->message_type,
                    'metadata' => $metadata,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'status' => $session->status,
                    'current_step' => $session->current_step,
                    'created_at' => $session->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $session->updated_at->format('Y-m-d H:i:s'),
                ],
                'messages' => $formattedMessages
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateSession(Request $request, $sessionId)
    {
        try {
            $session = ChatSession::where('user_id', Auth::id())->findOrFail($sessionId);
            
            $request->validate([
                'title' => 'required|string|max:255',
            ]);

            $session->update([
                'title' => $request->title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session updated successfully',
                'session' => $session
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteSession(Request $request, $sessionId)
    {
        try {
            $session = ChatSession::where('user_id', Auth::id())->findOrFail($sessionId);
            $session->delete();

            return response()->json([
                'success' => true,
                'message' => 'Session deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete session: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateSessionTitle($message)
    {

        $title = substr($message, 0, 30);
        if (strlen($message) > 30) {
            $title .= '...';
        }
        return $title;
    }
}