<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ThreatScenario;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "Starting JSON integrity check...\n";


echo "Checking ThreatScenario records...\n";
$scenarios = ThreatScenario::all();
$scenarioIssues = 0;

foreach ($scenarios as $scenario) {
    
    if (!is_array($scenario->keywords)) {
        echo "Issue with scenario #{$scenario->id} ({$scenario->title}): keywords is not an array\n";
        $scenarioIssues++;
        
  
        try {
            if (is_string($scenario->getRawOriginal('keywords'))) {
                $keywords = json_decode($scenario->getRawOriginal('keywords'), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($keywords)) {
                    $scenario->keywords = $keywords;
                    $scenario->save();
                    echo "  - Fixed keywords for scenario #{$scenario->id}\n";
                } else {
                    echo "  - Could not fix keywords for scenario #{$scenario->id}: " . json_last_error_msg() . "\n";
                }
            }
        } catch (\Exception $e) {
            echo "  - Error fixing keywords: " . $e->getMessage() . "\n";
        }
    }

    if (!is_array($scenario->mitigation_steps)) {
        echo "Issue with scenario #{$scenario->id} ({$scenario->title}): mitigation_steps is not an array\n";
        $scenarioIssues++;
        
   
        try {
            if (is_string($scenario->getRawOriginal('mitigation_steps'))) {
                $steps = json_decode($scenario->getRawOriginal('mitigation_steps'), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($steps)) {
                    $scenario->mitigation_steps = $steps;
                    $scenario->save();
                    echo "  - Fixed mitigation_steps for scenario #{$scenario->id}\n";
                } else {
                    echo "  - Could not fix mitigation_steps for scenario #{$scenario->id}: " . json_last_error_msg() . "\n";
                }
            }
        } catch (\Exception $e) {
            echo "  - Error fixing mitigation_steps: " . $e->getMessage() . "\n";
        }
    }
}


echo "\nChecking ChatSession records...\n";
$sessions = ChatSession::all();
$sessionIssues = 0;

foreach ($sessions as $session) {

    if (!is_array($session->context_data)) {
        echo "Issue with session #{$session->id}: context_data is not an array\n";
        $sessionIssues++;
     
        try {
            if (is_string($session->getRawOriginal('context_data'))) {
                $contextData = json_decode($session->getRawOriginal('context_data'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $session->context_data = $contextData ?: [];
                    $session->save();
                    echo "  - Fixed context_data for session #{$session->id}\n";
                } else {
                    echo "  - Could not fix context_data for session #{$session->id}: " . json_last_error_msg() . "\n";
              
                    $session->context_data = [];
                    $session->save();
                    echo "  - Set context_data to empty array for session #{$session->id}\n";
                }
            }
        } catch (\Exception $e) {
            echo "  - Error fixing context_data: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nChecking ChatMessage records...\n";
$messages = ChatMessage::all();
$messageIssues = 0;

foreach ($messages as $message) {
 
    if (!is_array($message->metadata)) {
        echo "Issue with message #{$message->id}: metadata is not an array\n";
        $messageIssues++;
        
       try {
            if (is_string($message->getRawOriginal('metadata'))) {
                $metadata = json_decode($message->getRawOriginal('metadata'), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $message->metadata = $metadata ?: [];
                    $message->save();
                    echo "  - Fixed metadata for message #{$message->id}\n";
                } else {
                    echo "  - Could not fix metadata for message #{$message->id}: " . json_last_error_msg() . "\n";
                
                    $message->metadata = [];
                    $message->save();
                    echo "  - Set metadata to empty array for message #{$message->id}\n";
                }
            }
        } catch (\Exception $e) {
            echo "  - Error fixing metadata: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nJSON integrity check completed.\n";
echo "Issues found:\n";
echo "- ThreatScenario: $scenarioIssues issues\n";
echo "- ChatSession: $sessionIssues issues\n";
echo "- ChatMessage: $messageIssues issues\n";

if ($scenarioIssues === 0 && $sessionIssues === 0 && $messageIssues === 0) {
    echo "\nAll JSON fields are valid!\n";
} else {
    echo "\nSome issues were found and fixed. Please run the script again to verify.\n";
}