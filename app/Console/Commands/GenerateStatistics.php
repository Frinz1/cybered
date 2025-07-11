<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ChatSession;
use App\Models\ThreatScenario;
use App\Models\ChatMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GenerateStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate statistics for the admin dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating statistics...');

    
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
        
  
        $statistics = [
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
        
  
        Cache::put('admin_statistics', $statistics, now()->addHours(24));
        
        $this->info('Statistics generated and cached successfully.');
        
        return 0;
    }
}