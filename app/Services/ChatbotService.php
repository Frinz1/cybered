<?php

namespace App\Services;

use App\Models\ThreatScenario;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $phishingKeywords = [
        'suspicious link', 'phishing', 'fake email', 'suspicious email', 
        'click here', 'verify account', 'urgent action', 'suspicious attachment',
        'email scam', 'fake website', 'login credentials', 'password reset'
    ];

    protected $malwareKeywords = [
        'malware', 'virus', 'suspicious file', 'infected computer', 
        'slow computer', 'pop-up', 'ransomware', 'trojan', 'spyware',
        'suspicious software', 'unknown program', 'computer acting strange'
    ];

    public function processMessage(ChatSession $session, string $message)
    {
        try {
            $currentStep = $session->current_step;
            $contextData = $this->getContextData($session);
            $lowerMessage = strtolower(trim($message));

            switch ($currentStep) {
                case 'greeting':
                    return $this->handleGreeting($lowerMessage, $contextData);
                
                case 'topic_selected':
                    return $this->handleTopicSelection($lowerMessage, $contextData);
                
                case 'awaiting_severity':
                    return $this->handleSeveritySelection($lowerMessage, $contextData);
                
                case 'providing_solution':
                    return $this->handleFollowUp($lowerMessage, $contextData);
                
                default:
                    return $this->handleGeneral($lowerMessage, $contextData);
            }
        } catch (\Exception $e) {
            Log::error('Error processing message: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a fallback response
            return [
                'message' => 'I apologize, but I encountered an error processing your request. Please try again.',
                'type' => 'error',
                'next_step' => 'greeting',
                'context_data' => [],
            ];
        }
    }

    protected function getContextData(ChatSession $session)
    {
        try {
            if (is_array($session->context_data)) {
                return $session->context_data;
            }
            
            if (is_string($session->context_data)) {
                $decoded = json_decode($session->context_data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting context data: ' . $e->getMessage());
            return [];
        }
    }

    protected function handleGreeting($message, $contextData)
    {
        // Check if user mentions a specific threat type
        if ($this->containsKeywords($message, $this->phishingKeywords)) {
            return $this->askForSeverity('phishing', $message, $contextData);
        }
        
        if ($this->containsKeywords($message, $this->malwareKeywords)) {
            return $this->askForSeverity('malware', $message, $contextData);
        }

        // Check for topic selection
        if (strpos($message, 'phishing') !== false) {
            return [
                'message' => 'Great! You\'ve selected phishing awareness. Can you describe the specific phishing-related issue you\'re experiencing? For example, "I received a suspicious email" or "Someone sent me a suspicious link".',
                'type' => 'topic_guidance',
                'next_step' => 'topic_selected',
                'context_data' => array_merge($contextData, ['topic' => 'phishing'])
            ];
        }

        if (strpos($message, 'malware') !== false) {
            return [
                'message' => 'Great! You\'ve selected malware protection. Can you describe the specific malware-related issue you\'re experiencing? For example, "My computer is acting strange" or "I found a suspicious USB drive".',
                'type' => 'topic_guidance',
                'next_step' => 'topic_selected',
                'context_data' => array_merge($contextData, ['topic' => 'malware'])
            ];
        }

        return [
            'message' => 'I can help you with cybersecurity threats. Please tell me about the issue you\'re experiencing, or choose a topic to learn about.',
            'type' => 'clarification',
            'next_step' => 'greeting',
            'context_data' => $contextData,
            'metadata' => [
                'options' => ['phishing', 'malware']
            ]
        ];
    }

    protected function handleTopicSelection($message, $contextData)
    {
        $topic = $contextData['topic'] ?? null;
        
        if ($topic === 'phishing' && $this->containsKeywords($message, $this->phishingKeywords)) {
            return $this->askForSeverity('phishing', $message, $contextData);
        }
        
        if ($topic === 'malware' && $this->containsKeywords($message, $this->malwareKeywords)) {
            return $this->askForSeverity('malware', $message, $contextData);
        }

        // If no specific keywords found, ask for more details
        return [
            'message' => 'Can you provide more specific details about the issue? This will help me give you the most relevant guidance.',
            'type' => 'clarification',
            'next_step' => 'topic_selected',
            'context_data' => $contextData
        ];
    }

    protected function askForSeverity($type, $userMessage, $contextData)
    {
        return [
            'message' => 'I understand you\'re dealing with a ' . $type . ' issue. To provide you with the most appropriate guidance, please rate the severity of this threat:',
            'type' => 'severity_request',
            'next_step' => 'awaiting_severity',
            'context_data' => array_merge($contextData, [
                'threat_type' => $type,
                'user_description' => $userMessage
            ]),
            'metadata' => [
                'severity_options' => ['low', 'medium', 'high']
            ]
        ];
    }

    protected function handleSeveritySelection($message, $contextData)
    {
        $severity = null;
        
        if (strpos($message, 'low') !== false) {
            $severity = 'low';
        } elseif (strpos($message, 'medium') !== false) {
            $severity = 'medium';
        } elseif (strpos($message, 'high') !== false) {
            $severity = 'high';
        }

        if (!$severity) {
            return [
                'message' => 'Please select a severity level: Low, Medium, or High.',
                'type' => 'severity_request',
                'next_step' => 'awaiting_severity',
                'context_data' => $contextData,
                'metadata' => [
                    'severity_options' => ['low', 'medium', 'high']
                ]
            ];
        }

        try {
            // Find appropriate solution
            $threatType = $contextData['threat_type'] ?? 'general';
            $userDescription = $contextData['user_description'] ?? '';
            
            $scenario = ThreatScenario::findByKeywords($userDescription, $severity);
            
            if (!$scenario) {
                // Fallback to any scenario of the same type and severity
                $scenario = ThreatScenario::where('type', $threatType)
                    ->where('severity', $severity)
                    ->first();
            }

            if ($scenario) {
                $scenario->incrementUsage();
                
                $response = "**" . $scenario->title . "**\n\n";
                $response .= "**Explanation:** " . $scenario->explanation . "\n\n";
                $response .= "**Recommended Actions:**\n";
                
                $mitigationSteps = $this->getMitigationSteps($scenario);
                if (is_array($mitigationSteps) && count($mitigationSteps) > 0) {
                    foreach ($mitigationSteps as $step) {
                        $response .= "• " . $step . "\n";
                    }
                } else {
                    $response .= $scenario->solution;
                }
                
                $response .= "\n**Remember:** Always report suspicious activities to your IT department immediately.";
                
                return [
                    'message' => $response,
                    'type' => 'solution',
                    'next_step' => 'providing_solution',
                    'context_data' => array_merge($contextData, [
                        'severity' => $severity,
                        'scenario_id' => $scenario->id,
                        'solution_provided' => true
                    ])
                ];
            }

            // Generic response if no specific scenario found
            $genericResponse = $this->getGenericResponse($threatType, $severity);
            
            return [
                'message' => $genericResponse,
                'type' => 'generic_solution',
                'next_step' => 'providing_solution',
                'context_data' => array_merge($contextData, [
                    'severity' => $severity,
                    'solution_provided' => true
                ])
            ];
        } catch (\Exception $e) {
            Log::error('Error in handleSeveritySelection: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback response
            return [
                'message' => 'I apologize, but I encountered an error finding the appropriate solution. Here are some general tips for ' . $contextData['threat_type'] . ' issues: Always verify the source, do not click suspicious links, and report any concerns to your IT department.',
                'type' => 'error_solution',
                'next_step' => 'providing_solution',
                'context_data' => array_merge($contextData, [
                    'severity' => $severity,
                    'solution_provided' => true
                ])
            ];
        }
    }

    protected function getMitigationSteps($scenario)
    {
        try {
            if (is_array($scenario->mitigation_steps)) {
                return $scenario->mitigation_steps;
            }
            
            if (is_string($scenario->mitigation_steps)) {
                $decoded = json_decode($scenario->mitigation_steps, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            
            // Fallback to solution if mitigation_steps is not available
            if (!empty($scenario->solution)) {
                return [$scenario->solution];
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Error getting mitigation steps: ' . $e->getMessage());
            return [];
        }
    }

    protected function handleFollowUp($message, $contextData)
    {
        if (strpos($message, 'thank') !== false || strpos($message, 'help') !== false) {
            return [
                'message' => 'You\'re welcome! Is there anything else you\'d like to know about cybersecurity? I can help with other phishing or malware concerns.',
                'type' => 'follow_up',
                'next_step' => 'greeting',
                'context_data' => []
            ];
        }

        // Check if they want to start over or ask about something else
        if ($this->containsKeywords($message, array_merge($this->phishingKeywords, $this->malwareKeywords))) {
            return $this->handleGreeting($message, []);
        }

        return [
            'message' => 'Is there anything else I can help you with regarding cybersecurity? You can ask about phishing, malware, or any other security concerns.',
            'type' => 'follow_up',
            'next_step' => 'greeting',
            'context_data' => [],
            'metadata' => [
                'options' => ['phishing', 'malware', 'new_session']
            ]
        ];
    }

    protected function handleGeneral($message, $contextData)
    {
        return $this->handleGreeting($message, $contextData);
    }

    protected function containsKeywords($message, $keywords)
    {
        foreach ($keywords as $keyword) {
            if (strpos($message, strtolower($keyword)) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function getGenericResponse($type, $severity)
    {
        $responses = [
            'phishing' => [
                'low' => "For low-severity phishing attempts:\n• Don't click on suspicious links\n• Verify sender identity through official channels\n• Report the email to your IT department\n• Delete the suspicious email",
                'medium' => "For medium-severity phishing attempts:\n• Immediately stop any interaction with the suspicious content\n• Change your passwords if you've entered them\n• Run a security scan on your computer\n• Report to IT department urgently\n• Monitor your accounts for unusual activity",
                'high' => "For high-severity phishing attempts:\n• Disconnect from the internet immediately\n• Do not enter any personal information\n• Contact IT department immediately\n• Change all passwords from a secure device\n• Monitor all accounts and credit reports\n• Consider enabling two-factor authentication"
            ],
            'malware' => [
                'low' => "For low-severity malware concerns:\n• Run a full antivirus scan\n• Update your operating system and software\n• Avoid downloading suspicious files\n• Report to IT department\n• Monitor system performance",
                'medium' => "For medium-severity malware infections:\n• Disconnect from the network immediately\n• Run antivirus and anti-malware scans\n• Contact IT department for assistance\n• Back up important data to clean storage\n• Consider system restoration if needed",
                'high' => "For high-severity malware infections:\n• Immediately disconnect from all networks\n• Do not use the infected system for sensitive tasks\n• Contact IT department emergency line\n• Isolate the system completely\n• Prepare for potential data recovery procedures\n• Change all passwords from a clean device"
            ]
        ];

        return $responses[$type][$severity] ?? "Please contact your IT department for specific guidance on this security issue.";
    }
}