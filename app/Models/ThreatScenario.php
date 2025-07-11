<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ThreatScenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'severity',
        'keywords',
        'solution',
        'mitigation_steps',
        'explanation',
        'usage_count',
    ];

    protected $casts = [
        'keywords' => 'array',
        'mitigation_steps' => 'array',
    ];

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public static function findByKeywords($input, $severity = null)
    {
        try {
            $query = self::query();
            
        
            $words = explode(' ', strtolower($input));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    if (strlen($word) > 3) {
                        $q->orWhere('title', 'LIKE', '%' . $word . '%')
                          ->orWhere('description', 'LIKE', '%' . $word . '%');
                    }
                }
            });

            if ($severity) {
                $query->where('severity', $severity);
            }

            return $query->orderBy('usage_count', 'desc')->first();
        } catch (\Exception $e) {
            Log::error('Error finding scenario by keywords: ' . $e->getMessage());
            return null;
        }
    }

    
    public function getKeywordsAttribute($value)
    {
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
            } catch (\Exception $e) {
                Log::error('Error decoding keywords JSON: ' . $e->getMessage());
                return [];
            }
        }
        return $value ?: [];
    }

    
    public function getMitigationStepsAttribute($value)
    {
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
            } catch (\Exception $e) {
                Log::error('Error decoding mitigation_steps JSON: ' . $e->getMessage());
                return [];
            }
        }
        return $value ?: [];
    }
}