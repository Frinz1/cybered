<?php

namespace App\Http\Controllers;

use App\Models\ThreatScenario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ThreatScenarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Access denied');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ThreatScenario::query();
            
   
            if ($request->has('type') && in_array($request->type, ['phishing', 'malware'])) {
                $query->where('type', $request->type);
            }
            
            
            if ($request->has('severity') && in_array($request->severity, ['low', 'medium', 'high'])) {
                $query->where('severity', $request->severity);
            }
            
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }
            

            $sortColumn = $request->input('sort', 'created_at');
            $sortDirection = $request->input('direction', 'desc');
            
          
            $allowedColumns = ['title', 'type', 'severity', 'usage_count', 'created_at', 'updated_at'];
            if (!in_array($sortColumn, $allowedColumns)) {
                $sortColumn = 'created_at';
            }
            
            $query->orderBy($sortColumn, $sortDirection === 'asc' ? 'asc' : 'desc');
            
        
            $perPage = $request->input('per_page', 10);
            $scenarios = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'scenarios' => $scenarios
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching scenarios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch scenarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:phishing,malware',
                'severity' => 'required|in:low,medium,high',
                'explanation' => 'required|string',
                'mitigation' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
        
            $mitigationSteps = array_filter(array_map('trim', explode("\n", $request->mitigation)));
            
        
            $keywords = $this->generateKeywords($request->title . ' ' . $request->description);
            
            $scenario = ThreatScenario::create([
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'severity' => $request->severity,
                'explanation' => $request->explanation,
                'mitigation_steps' => json_encode($mitigationSteps),
                'keywords' => json_encode($keywords),
                'solution' => $request->mitigation,
                'usage_count' => 0,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Scenario created successfully',
                'scenario' => $scenario
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating scenario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create scenario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ThreatScenario $scenario)
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
            Log::error('Error fetching scenario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch scenario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThreatScenario $scenario)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:phishing,malware',
                'severity' => 'required|in:low,medium,high',
                'explanation' => 'required|string',
                'mitigation' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            
            $mitigationSteps = array_filter(array_map('trim', explode("\n", $request->mitigation)));
            
    
            $keywords = $this->generateKeywords($request->title . ' ' . $request->description);
            
            $scenario->update([
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'severity' => $request->severity,
                'explanation' => $request->explanation,
                'mitigation_steps' => json_encode($mitigationSteps),
                'keywords' => json_encode($keywords),
                'solution' => $request->mitigation,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Scenario updated successfully',
                'scenario' => $scenario
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating scenario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update scenario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ThreatScenario $scenario)
    {
        try {
            $scenario->delete();
            return response()->json([
                'success' => true,
                'message' => 'Scenario deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting scenario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete scenario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate keywords from text.
     */
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