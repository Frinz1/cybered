<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_session_id',
        'message',
        'is_bot',
        'message_type',
        'metadata',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'metadata' => 'array',
    ];

    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }


    public function getMetadataAttribute($value)
    {
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                return (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
            } catch (\Exception $e) {
                Log::error('Error decoding metadata JSON: ' . $e->getMessage());
                return [];
            }
        }
        return $value ?: [];
    }

    public function setMetadataAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['metadata'] = json_encode($value);
        } else if (is_string($value)) {
        
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['metadata'] = $value;
            } else {
            
                $this->attributes['metadata'] = json_encode([]);
            }
        } else {
            $this->attributes['metadata'] = json_encode([]);
        }
    }
}