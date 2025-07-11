<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatSession;
use App\Models\ThreatScenario;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
            return $next($request);
        });
    }

    /**
     * Get dashboard statistics.
     */
    public function dashboard()
    {
        try {
            // Check if statistics are cached
            if (Cache::has('admin_statistics')) {
                return response()->json([
                    'success' => true,
                    'statistics' => Cache::get('admin_statistics'),
                    'cached' => true
                ]);
            }
            
            // Generate statistics if not cached
            $statistics = $this->generateStatistics();
            
            // Cache statistics for 24 hours
            Cache::put('admin_statistics', $statistics, now()->addHours(24));
            
            return response()->json([
                'success' => true,
                'statistics' => $statistics,
                'cached' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate statistics.
     */
    private function generateStatistics()
    {
        
        $userCount = User::where('is_admin', false)->count();
        $adminCount = User::where('is_admin', true)->count();
        $sessionCount = ChatSession::count();
        $scenarioCount = ThreatScenario::count();
        $messageCount = ChatMessage::count();
        
    
        $completedSessions = ChatSession::where('status', 'completed')->count();
        $completionRate = $sessionCount > 0 ? round(($completedSessions / $sessionCount) * 100) : 0;
        
        
        $userActivity = DB::table('chat_sessions')
            ->select(DB::raw('DAYOFWEEK(created_at) as day_of_week, COUNT(*) as count'))
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();
        
    
        $daysOfWeek = [1, 2, 3, 4, 5, 6, 7]; 
        foreach ($daysOfWeek as $day) {
            if (!isset($userActivity[$day])) {
                $userActivity[$day] = 0;
            }
        }
        ksort($userActivity);
        
        
        $scenarioUsageByType = DB::table('threat_scenarios')
            ->select('type', DB::raw('SUM(usage_count) as total_usage'))
            ->groupBy('type')
            ->get()
            ->keyBy('type')
            ->map(function ($item) {
                return $item->total_usage;
            })
            ->toArray();
        
    
        $types = ['phishing', 'malware'];
        foreach ($types as $type) {
            if (!isset($scenarioUsageByType[$type])) {
                $scenarioUsageByType[$type] = 0;
            }
        }
        
        
        $scenarioUsageBySeverity = DB::table('threat_scenarios')
            ->select('severity', DB::raw('SUM(usage_count) as total_usage'))
            ->groupBy('severity')
            ->get()
            ->keyBy('severity')
            ->map(function ($item) {
                return $item->total_usage;
            })
            ->toArray();
        
        
        $severities = ['low', 'medium', 'high'];
        foreach ($severities as $severity) {
            if (!isset($scenarioUsageBySeverity[$severity])) {
                $scenarioUsageBySeverity[$severity] = 0;
            }
        }
        
    
        $topScenarios = ThreatScenario::orderBy('usage_count', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'type', 'severity', 'usage_count'])
            ->toArray();
        

        $recentUsers = User::where('is_admin', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at'])
            ->toArray();
        
        
        $recentSessions = ChatSession::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'user_id', 'title', 'status', 'created_at'])
            ->toArray();
        

        return [
            'counts' => [
                'users' => $userCount,
                'admins' => $adminCount,
                'sessions' => $sessionCount,
                'scenarios' => $scenarioCount,
                'messages' => $messageCount,
            ],
            'completion_rate' => $completionRate,
            'user_activity' => $userActivity,
            'scenario_usage' => [
                'by_type' => $scenarioUsageByType,
                'by_severity' => $scenarioUsageBySeverity,
            ],
            'top_scenarios' => $topScenarios,
            'recent' => [
                'users' => $recentUsers,
                'sessions' => $recentSessions,
            ],
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}