<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'status',
        'current_step',
        'context_data',
    ];

    protected $casts = [
        'context_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function getContextDataAttribute($value)
    {
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
            } catch (\Exception $e) {
                Log::error('Error decoding context_data JSON: ' . $e->getMessage());
                return [];
            }
        }
        return $value ?: [];
    }

    
    public function setContextDataAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['context_data'] = json_encode($value);
        } else if (is_string($value)) {
            
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['context_data'] = $value;
            } else {
    
                $this->attributes['context_data'] = json_encode([]);
            }
        } else {
            $this->attributes['context_data'] = json_encode([]);
        }
    }
}