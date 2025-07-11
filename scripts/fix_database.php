<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

echo "Starting database fix script...\n";

try {

    if (!Schema::hasTable('threat_scenarios')) {
        echo "Table 'threat_scenarios' does not exist. Running migrations...\n";
        Artisan::call('migrate', ['--force' => true]);
        echo Artisan::output();
    }


    echo "Fixing threat_scenarios table...\n";
    $scenarios = DB::table('threat_scenarios')->get();
    foreach ($scenarios as $scenario) {
        $updates = [];
        
  
        if (isset($scenario->keywords)) {
            if (!is_array(json_decode($scenario->keywords, true)) && $scenario->keywords !== null) {
                echo "Fixing keywords for scenario #{$scenario->id}\n";
                if (is_string($scenario->keywords)) {
                    try {
                        $keywords = json_decode($scenario->keywords, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                    
                            $keywords = [$scenario->keywords];
                            $updates['keywords'] = json_encode($keywords);
                        }
                    } catch (\Exception $e) {
                        $updates['keywords'] = json_encode([]);
                    }
                } else {
                    $updates['keywords'] = json_encode([]);
                }
            }
        } else {
            $updates['keywords'] = json_encode([]);
        }
        
    
        if (isset($scenario->mitigation_steps)) {
            if (!is_array(json_decode($scenario->mitigation_steps, true)) && $scenario->mitigation_steps !== null) {
                echo "Fixing mitigation_steps for scenario #{$scenario->id}\n";
                if (is_string($scenario->mitigation_steps)) {
                    try {
                        $steps = json_decode($scenario->mitigation_steps, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
              
                            $steps = [$scenario->mitigation_steps];
                            $updates['mitigation_steps'] = json_encode($steps);
                        }
                    } catch (\Exception $e) {
                        $updates['mitigation_steps'] = json_encode([]);
                    }
                } else {
                    $updates['mitigation_steps'] = json_encode([]);
                }
            }
        } else {
            $updates['mitigation_steps'] = json_encode([]);
        }
        
   
        if (!empty($updates)) {
            DB::table('threat_scenarios')->where('id', $scenario->id)->update($updates);
        }
    }

    
    echo "Fixing chat_sessions table...\n";
    if (Schema::hasTable('chat_sessions')) {
        $sessions = DB::table('chat_sessions')->get();
        foreach ($sessions as $session) {
            $updates = [];
            
      
            if (isset($session->context_data)) {
                if (!is_array(json_decode($session->context_data, true)) && $session->context_data !== null) {
                    echo "Fixing context_data for session #{$session->id}\n";
                    if (is_string($session->context_data)) {
                        try {
                            $contextData = json_decode($session->context_data, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $updates['context_data'] = json_encode([]);
                            }
                        } catch (\Exception $e) {
                            $updates['context_data'] = json_encode([]);
                        }
                    } else {
                        $updates['context_data'] = json_encode([]);
                    }
                }
            } else {
                $updates['context_data'] = json_encode([]);
            }
            
    
            if (!empty($updates)) {
                DB::table('chat_sessions')->where('id', $session->id)->update($updates);
            }
        }
    }


    echo "Fixing chat_messages table...\n";
    if (Schema::hasTable('chat_messages')) {
        $messages = DB::table('chat_messages')->get();
        foreach ($messages as $message) {
            $updates = [];
            
         
            if (isset($message->metadata)) {
                if (!is_array(json_decode($message->metadata, true)) && $message->metadata !== null) {
                    echo "Fixing metadata for message #{$message->id}\n";
                    if (is_string($message->metadata)) {
                        try {
                            $metadata = json_decode($message->metadata, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $updates['metadata'] = json_encode([]);
                            }
                        } catch (\Exception $e) {
                            $updates['metadata'] = json_encode([]);
                        }
                    } else {
                        $updates['metadata'] = json_encode([]);
                    }
                }
            } else {
                $updates['metadata'] = json_encode([]);
            }
       
            if (!empty($updates)) {
                DB::table('chat_messages')->where('id', $message->id)->update($updates);
            }
        }
    }

    echo "Database fix script completed successfully.\n";
    echo "Now running the JSON integrity check command...\n";
    
    Artisan::call('app:check-json-integrity');
    echo Artisan::output();
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}