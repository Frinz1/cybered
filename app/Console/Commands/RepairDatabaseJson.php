<?php

namespace App\Console\Commands;

use App\Models\ThreatScenario;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RepairDatabaseJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:repair-database-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair any broken JSON data in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database JSON repair...');

        
        $this->info('Fixing threat_scenarios table...');
        $scenariosFixed = 0;
        
        $scenarios = ThreatScenario::all();
        foreach ($scenarios as $scenario) {
            $updates = [];
            $needsUpdate = false;
            
        
            try {
                if (is_string($scenario->getRawOriginal('keywords'))) {
                    json_decode($scenario->getRawOriginal('keywords'));
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->warn("Invalid keywords JSON for scenario #{$scenario->id}");
                        $updates['keywords'] = json_encode([]);
                        $needsUpdate = true;
                    }
                } else if ($scenario->getRawOriginal('keywords') === null) {
                    $updates['keywords'] = json_encode([]);
                    $needsUpdate = true;
                }
            } catch (\Exception $e) {
                $this->error("Error checking keywords for scenario #{$scenario->id}: " . $e->getMessage());
                $updates['keywords'] = json_encode([]);
                $needsUpdate = true;
            }
            
        
            try {
                if (is_string($scenario->getRawOriginal('mitigation_steps'))) {
                    json_decode($scenario->getRawOriginal('mitigation_steps'));
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->warn("Invalid mitigation_steps JSON for scenario #{$scenario->id}");
                        
                        
                        if (!empty($scenario->solution)) {
                            $steps = array_filter(array_map('trim', explode("\n", $scenario->solution)));
                            $updates['mitigation_steps'] = json_encode($steps);
                        } else {
                            $updates['mitigation_steps'] = json_encode([]);
                        }
                        
                        $needsUpdate = true;
                    }
                } else if ($scenario->getRawOriginal('mitigation_steps') === null) {
                    if (!empty($scenario->solution)) {
                        $steps = array_filter(array_map('trim', explode("\n", $scenario->solution)));
                        $updates['mitigation_steps'] = json_encode($steps);
                    } else {
                        $updates['mitigation_steps'] = json_encode([]);
                    }
                    $needsUpdate = true;
                }
            } catch (\Exception $e) {
                $this->error("Error checking mitigation_steps for scenario #{$scenario->id}: " . $e->getMessage());
                $updates['mitigation_steps'] = json_encode([]);
                $needsUpdate = true;
            }
            
    
            if ($needsUpdate) {
                try {
                    $scenario->update($updates);
                    $scenariosFixed++;
                    $this->info("Fixed scenario #{$scenario->id}");
                } catch (\Exception $e) {
                    $this->error("Failed to update scenario #{$scenario->id}: " . $e->getMessage());
                }
            }
        }

    
        $this->info('Fixing chat_sessions table...');
        $sessionsFixed = 0;
        
        $sessions = ChatSession::all();
        foreach ($sessions as $session) {
            $updates = [];
            $needsUpdate = false;
            

            try {
                if (is_string($session->getRawOriginal('context_data'))) {
                    json_decode($session->getRawOriginal('context_data'));
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->warn("Invalid context_data JSON for session #{$session->id}");
                        $updates['context_data'] = json_encode([]);
                        $needsUpdate = true;
                    }
                } else if ($session->getRawOriginal('context_data') === null) {
                    $updates['context_data'] = json_encode([]);
                    $needsUpdate = true;
                }
            } catch (\Exception $e) {
                $this->error("Error checking context_data for session #{$session->id}: " . $e->getMessage());
                $updates['context_data'] = json_encode([]);
                $needsUpdate = true;
            }
            
        
            if ($needsUpdate) {
                try {
                    $session->update($updates);
                    $sessionsFixed++;
                    $this->info("Fixed session #{$session->id}");
                } catch (\Exception $e) {
                    $this->error("Failed to update session #{$session->id}: " . $e->getMessage());
                }
            }
        }

    
        $this->info('Fixing chat_messages table...');
        $messagesFixed = 0;
        
        $messages = ChatMessage::all();
        foreach ($messages as $message) {
            $updates = [];
            $needsUpdate = false;
            
        
            try {
                if (is_string($message->getRawOriginal('metadata'))) {
                    json_decode($message->getRawOriginal('metadata'));
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->warn("Invalid metadata JSON for message #{$message->id}");
                        $updates['metadata'] = json_encode([]);
                        $needsUpdate = true;
                    }
                } else if ($message->getRawOriginal('metadata') === null) {
                    $updates['metadata'] = json_encode([]);
                    $needsUpdate = true;
                }
            } catch (\Exception $e) {
                $this->error("Error checking metadata for message #{$message->id}: " . $e->getMessage());
                $updates['metadata'] = json_encode([]);
                $needsUpdate = true;
            }
            
            
            if ($needsUpdate) {
                try {
                    $message->update($updates);
                    $messagesFixed++;
                    $this->info("Fixed message #{$message->id}");
                } catch (\Exception $e) {
                    $this->error("Failed to update message #{$message->id}: " . $e->getMessage());
                }
            }
        }

        $this->info('Database JSON repair completed.');
        $this->info("Fixed $scenariosFixed scenarios, $sessionsFixed sessions, and $messagesFixed messages.");
        
        return 0;
    }
}