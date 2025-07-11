<?php

namespace Database\Seeders;

use App\Models\ThreatScenario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ThreatScenarioSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $scenarios = [
                
                [
                    'title' => 'Suspicious Email from Unknown Sender',
                    'description' => 'You receive an email from an unknown sender asking you to click a link to verify your account.',
                    'type' => 'phishing',
                    'severity' => 'low',
                    'keywords' => ['suspicious', 'email', 'unknown', 'sender', 'verify', 'account', 'click', 'link'],
                    'explanation' => 'This is a common phishing attempt where attackers try to steal your credentials by impersonating legitimate services.',
                    'mitigation_steps' => [
                        'Do not click on any links in the email',
                        'Verify the sender through official channels',
                        'Check the email address for suspicious domains',
                        'Report the email to your IT department',
                        'Delete the email after reporting'
                    ],
                    'solution' => 'Never click suspicious links. Always verify through official channels.'
                ],
                [
                    'title' => 'Fake IT Department Email',
                    'description' => 'An email claiming to be from your IT department asks you to update your password by clicking a link.',
                    'type' => 'phishing',
                    'severity' => 'medium',
                    'keywords' => ['fake', 'it', 'department', 'password', 'update', 'click', 'link'],
                    'explanation' => 'Attackers often impersonate IT departments to gain access to employee credentials.',
                    'mitigation_steps' => [
                        'Contact your IT department directly to verify',
                        'Do not click the link',
                        'Check the sender email address carefully',
                        'Report to your security team',
                        'Use official IT channels for password changes'
                    ],
                    'solution' => 'Always verify IT requests through official channels.'
                ],
                [
                    'title' => 'Urgent Payment Request Scam',
                    'description' => 'You receive an urgent email from what appears to be your supervisor requesting an immediate wire transfer.',
                    'type' => 'phishing',
                    'severity' => 'high',
                    'keywords' => ['urgent', 'payment', 'supervisor', 'wire', 'transfer', 'immediate'],
                    'explanation' => 'This is a business email compromise (BEC) attack targeting financial transactions.',
                    'mitigation_steps' => [
                        'Do not process any payment immediately',
                        'Verify the request through phone call',
                        'Check with your supervisor in person',
                        'Follow your organization\'s payment verification procedures',
                        'Report to security team immediately'
                    ],
                    'solution' => 'Always verify payment requests through multiple channels.'
                ],

       
                [
                    'title' => 'Suspicious USB Drive Found',
                    'description' => 'You find a USB drive in the parking lot labeled "Employee Salary Information - Confidential".',
                    'type' => 'malware',
                    'severity' => 'high',
                    'keywords' => ['usb', 'drive', 'found', 'parking', 'salary', 'confidential'],
                    'explanation' => 'USB drives found in public areas are often infected with malware as part of social engineering attacks.',
                    'mitigation_steps' => [
                        'Do not plug the USB drive into any computer',
                        'Report the found device to security',
                        'Turn in the device to your IT department',
                        'Warn colleagues about the potential threat',
                        'Follow your organization\'s security protocols'
                    ],
                    'solution' => 'Never use unknown USB devices. Report to IT immediately.'
                ],
                [
                    'title' => 'Suspicious Software Pop-up',
                    'description' => 'A pop-up appears claiming your antivirus is out of date and needs immediate updating.',
                    'type' => 'malware',
                    'severity' => 'medium',
                    'keywords' => ['popup', 'antivirus', 'update', 'software', 'immediate'],
                    'explanation' => 'Fake antivirus pop-ups are used to trick users into downloading malware.',
                    'mitigation_steps' => [
                        'Do not click on the pop-up',
                        'Close the browser or application',
                        'Run a legitimate antivirus scan',
                        'Update software through official channels only',
                        'Report the incident to IT'
                    ],
                    'solution' => 'Only update software through official channels.'
                ],
                [
                    'title' => 'Computer Running Slowly',
                    'description' => 'Your computer has been running unusually slow and showing strange pop-ups.',
                    'type' => 'malware',
                    'severity' => 'medium',
                    'keywords' => ['computer', 'slow', 'strange', 'popup', 'unusual'],
                    'explanation' => 'These symptoms often indicate malware infection affecting system performance.',
                    'mitigation_steps' => [
                        'Disconnect from the network immediately',
                        'Run a full system antivirus scan',
                        'Contact IT support for assistance',
                        'Do not access sensitive information',
                        'Consider system restoration if needed'
                    ],
                    'solution' => 'Isolate the system and run comprehensive security scans.'
                ],

                [
                    'title' => 'Social Media Friend Request Scam',
                    'description' => 'You receive a friend request from someone claiming to be a coworker but the profile looks suspicious.',
                    'type' => 'phishing',
                    'severity' => 'low',
                    'keywords' => ['social', 'media', 'friend', 'request', 'coworker', 'suspicious', 'profile'],
                    'explanation' => 'Attackers create fake profiles to gather information about employees and their organizations.',
                    'mitigation_steps' => [
                        'Do not accept the friend request',
                        'Verify the person\'s identity in person',
                        'Check if they already have a legitimate profile',
                        'Report the fake profile to the platform',
                        'Warn your colleagues about the suspicious account'
                    ],
                    'solution' => 'Always verify social media connections through official channels.'
                ],
                [
                    'title' => 'Fake Software License Renewal',
                    'description' => 'You receive an email about renewing software licenses with a link to pay immediately.',
                    'type' => 'phishing',
                    'severity' => 'medium',
                    'keywords' => ['software', 'license', 'renewal', 'pay', 'immediately', 'link'],
                    'explanation' => 'Scammers impersonate software vendors to steal payment information and credentials.',
                    'mitigation_steps' => [
                        'Do not click payment links in emails',
                        'Verify with your IT department',
                        'Check official vendor websites directly',
                        'Review your organization\'s software inventory',
                        'Report suspicious license renewal requests'
                    ],
                    'solution' => 'Verify all software renewals through official vendor channels.'
                ]
            ];

            foreach ($scenarios as $scenario) {
                
                if (isset($scenario['keywords']) && is_array($scenario['keywords'])) {
                    $scenario['keywords'] = json_encode($scenario['keywords']);
                }
                
                if (isset($scenario['mitigation_steps']) && is_array($scenario['mitigation_steps'])) {
                    $scenario['mitigation_steps'] = json_encode($scenario['mitigation_steps']);
                }
                
                ThreatScenario::updateOrCreate(
                    ['title' => $scenario['title']],
                    $scenario
                );
            }

        
            $this->generateAdditionalScenarios();
            
            Log::info('ThreatScenario seeding completed successfully');
        } catch (\Exception $e) {
            Log::error('Error during ThreatScenario seeding: ' . $e->getMessage());
            throw $e; 
        }
    }

    private function generateAdditionalScenarios()
    {
        try {
            $phishingTemplates = [
                'Bank Account Verification',
                'Credit Card Expiration Notice',
                'Tax Refund Notification',
                'Package Delivery Failure',
                'Account Suspension Warning',
                'Security Alert Notification',
                'Prize Winner Announcement',
                'Survey Participation Request',
                'Document Sharing Invitation',
                'Meeting Invitation Scam'
            ];

            $malwareTemplates = [
                'Infected Email Attachment',
                'Malicious Download Link',
                'Compromised Website Visit',
                'Fake System Update',
                'Trojan Horse Software',
                'Ransomware Warning Signs',
                'Keylogger Detection',
                'Browser Hijacking',
                'Adware Installation',
                'Spyware Symptoms'
            ];

            $severities = ['low', 'medium', 'high'];

            foreach ($phishingTemplates as $index => $template) {
                $keywords = explode(' ', strtolower($template));
                
                ThreatScenario::updateOrCreate(
                    ['title' => $template],
                    [
                        'title' => $template,
                        'description' => 'A phishing scenario involving ' . strtolower($template),
                        'type' => 'phishing',
                        'severity' => $severities[$index % 3],
                        'keywords' => json_encode($keywords),
                        'explanation' => 'This is a common phishing attack that targets users through deceptive communications.',
                        'mitigation_steps' => json_encode([
                            'Verify the source through official channels',
                            'Do not provide personal information',
                            'Report the suspicious communication',
                            'Delete the message after reporting'
                        ]),
                        'solution' => 'Always verify suspicious communications through official channels.'
                    ]
                );
            }

            foreach ($malwareTemplates as $index => $template) {
                $keywords = explode(' ', strtolower($template));
                
                ThreatScenario::updateOrCreate(
                    ['title' => $template],
                    [
                        'title' => $template,
                        'description' => 'A malware scenario involving ' . strtolower($template),
                        'type' => 'malware',
                        'severity' => $severities[$index % 3],
                        'keywords' => json_encode($keywords),
                        'explanation' => 'This malware threat can compromise your system and data security.',
                        'mitigation_steps' => json_encode([
                            'Isolate the affected system',
                            'Run comprehensive security scans',
                            'Contact IT support immediately',
                            'Follow incident response procedures'
                        ]),
                        'solution' => 'Immediately isolate and scan the system for threats.'
                    ]
                );
            }
            
            Log::info('Additional scenarios generated successfully');
        } catch (\Exception $e) {
            Log::error('Error generating additional scenarios: ' . $e->getMessage());
            throw $e; 
        }
    }
}