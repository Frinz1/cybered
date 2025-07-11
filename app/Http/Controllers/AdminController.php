<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ThreatScenario;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Access denied');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::where('is_admin', false)->count(),
            'chat_sessions' => ChatSession::count(),
            'scenarios' => ThreatScenario::count(),
            'completion_rate' => $this->calculateCompletionRate(),
        ];

        $recentActivity = $this->getRecentActivity();

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }

    public function users()
    {
        $users = User::where('is_admin', false)
            ->withCount('chatSessions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users', compact('users'));
    }

    public function scenarios()
    {
        $scenarios = ThreatScenario::orderBy('created_at', 'desc')->get();
        return view('admin.scenarios', compact('scenarios'));
    }

    public function storeUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully', 
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editUser(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            ]);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully', 
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyUser(User $user)
    {
        try {
            if ($user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete admin user'
                ], 403);
            }

            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeScenario(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:phishing,malware',
                'severity' => 'required|in:low,medium,high',
                'explanation' => 'required|string',
                'mitigation' => 'required|string',
            ]);

        
            $mitigationSteps = array_filter(array_map('trim', explode("\n", $validated['mitigation'])));

            
            $keywords = $this->generateKeywords($validated['title'] . ' ' . $validated['description']);

            $scenario = ThreatScenario::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'severity' => $validated['severity'],
                'explanation' => $validated['explanation'],
                'mitigation_steps' => json_encode($mitigationSteps), 
                'keywords' => json_encode($keywords), 
                'solution' => $validated['mitigation'],
                'usage_count' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scenario created successfully', 
                'scenario' => $scenario
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create scenario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create scenario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editScenario(ThreatScenario $scenario)
    {
        try {
    
            $scenarioData = $scenario->toArray();
            
    
            if (is_string($scenario->keywords)) {
                $scenarioData['keywords'] = json_decode($scenario->keywords, true) ?: [];
            }
            
            if (is_string($scenario->mitigation_steps)) {
                $scenarioData['mitigation_steps'] = json_decode($scenario->mitigation_steps, true) ?: [];
            }
            
            return response()->json([
                'success' => true,
                'scenario' => $scenarioData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to edit scenario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load scenario data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateScenario(Request $request, ThreatScenario $scenario)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:phishing,malware',
                'severity' => 'required|in:low,medium,high',
                'explanation' => 'required|string',
                'mitigation' => 'required|string',
            ]);

            $mitigationSteps = array_filter(array_map('trim', explode("\n", $validated['mitigation'])));
            $keywords = $this->generateKeywords($validated['title'] . ' ' . $validated['description']);

            $scenario->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'severity' => $validated['severity'],
                'explanation' => $validated['explanation'],
                'mitigation_steps' => json_encode($mitigationSteps), // Ensure JSON format
                'keywords' => json_encode($keywords), // Ensure JSON format
                'solution' => $validated['mitigation'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scenario updated successfully', 
                'scenario' => $scenario
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update scenario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update scenario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyScenario(ThreatScenario $scenario)
    {
        try {
            $scenario->delete();
            return response()->json([
                'success' => true,
                'message' => 'Scenario deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete scenario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete scenario: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateCompletionRate()
    {
        $totalSessions = ChatSession::count();
        if ($totalSessions === 0) return 0;

        $completedSessions = ChatSession::where('status', 'completed')->count();
        return round(($completedSessions / $totalSessions) * 100);
    }

    private function getRecentActivity()
    {
        $recentUsers = User::where('is_admin', false)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($user) {
                return [
                    'type' => 'user_registered',
                    'description' => "New user registered: {$user->name}",
                    'time' => $user->created_at->diffForHumans()
                ];
            });
            
        $recentSessions = ChatSession::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($session) {
                return [
                    'type' => 'session_created',
                    'description' => "Chat session started by {$session->user->name}",
                    'time' => $session->created_at->diffForHumans()
                ];
            });
            
        return $recentUsers->concat($recentSessions)->sortByDesc('time')->values()->all();
    }

    private function generateKeywords($text)
    {
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should'];
        
        $words = str_word_count(strtolower($text), 1);
        $keywords = array_filter($words, function($word) use ($commonWords) {
            return strlen($word) > 3 && !in_array($word, $commonWords);
        });

        return array_values(array_unique($keywords));
    }
}