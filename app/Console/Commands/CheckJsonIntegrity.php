<?php

namespace App\Console\Commands;

use App\Models\ThreatScenario;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckJsonIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-json-integrity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and fix JSON integrity in database records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting JSON integrity check...');


        $this->info('Checking ThreatScenario records...');
        $scenarios = ThreatScenario::all();
        $scenarioIssues = 0;

        foreach ($scenarios as $scenario) {
       
            if (!is_array($scenario->keywords)) {
                $this->warn("Issue with scenario #{$scenario->id} ({$scenario->title}): keywords is not an array");
                $scenarioIssues++;
                
      
                try {
                    if (is_string($scenario->getRawOriginal('keywords'))) {
                        $keywords = json_decode($scenario->getRawOriginal('keywords'), true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($keywords)) {
                            $scenario->keywords = $keywords;
                            $scenario->save();
                            $this->info("  - Fixed keywords for scenario #{$scenario->id}");
                        } else {
                            $this->error("  - Could not fix keywords for scenario #{$scenario->id}: " . json_last_error_msg());
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("  - Error fixing keywords: " . $e->getMessage());
                }
            }
            
     
            if (!is_array($scenario->mitigation_steps)) {
                $this->warn("Issue with scenario #{$scenario->id} ({$scenario->title}): mitigation_steps is not an array");
                $scenarioIssues++;

                try {
                    if (is_string($scenario->getRawOriginal('mitigation_steps'))) {
                        $steps = json_decode($scenario->getRawOriginal('mitigation_steps'), true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($steps)) {
                            $scenario->mitigation_steps = $steps;
                            $scenario->save();
                            $this->info("  - Fixed mitigation_steps for scenario #{$scenario->id}");
                        } else {
                            $this->error("  - Could not fix mitigation_steps for scenario #{$scenario->id}: " . json_last_error_msg());
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("  - Error fixing mitigation_steps: " . $e->getMessage());
                }
            }
        }


        $this->info('Checking ChatSession records...');
        $sessions = ChatSession::all();
        $sessionIssues = 0;

        foreach ($sessions as $session) {

            if (!is_array($session->context_data)) {
                $this->warn("Issue with session #{$session->id}: context_data is not an array");
                $sessionIssues++;
                
         
                try {
                    if (is_string($session->getRawOriginal('context_data'))) {
                        $contextData = json_decode($session->getRawOriginal('context_data'), true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $session->context_data = $contextData ?: [];
                            $session->save();
                            $this->info("  - Fixed context_data for session #{$session->id}");
                        } else {
                            $this->error("  - Could not fix context_data for session #{$session->id}: " . json_last_error_msg());
                     
                            $session->context_data = [];
                            $session->save();
                            $this->info("  - Set context_data to empty array for session #{$session->id}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("  - Error fixing context_data: " . $e->getMessage());
                }
            }
        }

  
        $this->info('Checking ChatMessage records...');
        $messages = ChatMessage::all();
        $messageIssues = 0;

        foreach ($messages as $message) {
    
            if (!is_array($message->metadata)) {
                $this->warn("Issue with message #{$message->id}: metadata is not an array");
                $messageIssues++;
                
         
                try {
                    if (is_string($message->getRawOriginal('metadata'))) {
                        $metadata = json_decode($message->getRawOriginal('metadata'), true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $message->metadata = $metadata ?: [];
                            $message->save();
                            $this->info("  - Fixed metadata for message #{$message->id}");
                        } else {
                            $this->error("  - Could not fix metadata for message #{$message->id}: " . json_last_error_msg());
                      
                            $message->metadata = [];
                            $message->save();
                            $this->info("  - Set metadata to empty array for message #{$message->id}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("  - Error fixing metadata: " . $e->getMessage());
                }
            }
        }

        $this->info('JSON integrity check completed.');
        $this->info('Issues found:');
        $this->info("- ThreatScenario: $scenarioIssues issues");
        $this->info("- ChatSession: $sessionIssues issues");
        $this->info("- ChatMessage: $messageIssues issues");

        if ($scenarioIssues === 0 && $sessionIssues === 0 && $messageIssues === 0) {
            $this->info('All JSON fields are valid!');
            return 0;
        } else {
            $this->warn('Some issues were found and fixed. Please run the command again to verify.');
            return 1;
        }
    }
}