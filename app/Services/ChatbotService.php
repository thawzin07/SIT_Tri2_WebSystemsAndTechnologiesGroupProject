<?php

namespace App\Services;

use App\Models\GymClassModel;
use App\Models\LocationModel;
use App\Models\MembershipPlanModel;
use App\Models\TrainerModel;
use RuntimeException;

class ChatbotService
{
    private const FALLBACK_SCOPE_REPLY = 'I can only help with PulsePoint Fitness website topics like memberships, classes, trainers, locations, bookings, and contact info.';

    public function ask(string $message): string
    {
        $config = config('openai');
        $apiKey = (string) ($config['api_key'] ?? '');
        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is missing.');
        }

        $payload = [
            'model' => (string) ($config['model'] ?? 'gpt-4.1-mini'),
            'temperature' => (float) ($config['temperature'] ?? 0.2),
            'max_output_tokens' => (int) ($config['max_output_tokens'] ?? 240),
            'input' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $this->systemInstructions(),
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => "Website context:\n" . $this->buildSiteContext() . "\n\nUser question:\n" . $message,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->requestWithCompatibilityFallback(
            $apiKey,
            $payload,
            (int) ($config['timeout_seconds'] ?? 20)
        );

        $text = trim($this->extractText($response));
        if ($text === '') {
            throw new RuntimeException('OpenAI returned an empty response.');
        }

        return $this->sanitizeReply($text);
    }

    private function systemInstructions(): string
    {
        return implode("\n", [
            'You are the official website assistant for PulsePoint Fitness.',
            'Your scope is strictly PulsePoint Fitness website and services.',
            'Only answer questions about: memberships, plans, classes, trainers, locations, bookings, waitlist, login/register, contact, FAQ, and website navigation.',
            'If the user asks anything outside this scope, do not answer the outside topic. Reply with exactly:',
            '"' . self::FALLBACK_SCOPE_REPLY . '"',
            'Do not invent information not present in the provided website context.',
            'Keep answers concise, clear, and practical.',
            'Do not use markdown formatting like **bold**, bullet lists, or headings unless the user explicitly asks for it.',
            'Use plain text with short sentences.',
            'Answer only what was asked. Do not add extra details.',
            'For trainer questions, give trainer name + specialty first. Include class schedules only if user asks for schedule/date/time.',
        ]);
    }

    private function buildSiteContext(): string
    {
        $planModel = new MembershipPlanModel();
        $locationModel = new LocationModel();
        $classModel = new GymClassModel();
        $trainerModel = new TrainerModel();

        $plans = array_slice($planModel->activePlans(), 0, 6);
        $locations = array_slice($locationModel->active(), 0, 6);
        $classes = array_slice($classModel->upcomingActive(), 0, 8);
        $trainers = array_slice($trainerModel->active(), 0, 12);

        $context = [];
        $context[] = 'Website: PulsePoint Fitness';
        $context[] = 'Public pages: Home, About, Plans, Trainers, Schedule, Locations, Contact, FAQ.';
        $context[] = 'Member pages: Dashboard, Profile, Bookings, Membership actions.';

        if ($trainers !== []) {
            $context[] = 'Active trainers and specialties:';
            foreach ($trainers as $trainer) {
                $context[] = '- ' . (string) $trainer['name'] . ': ' . (string) $trainer['specialty'] . '.';
            }
        }

        if ($plans !== []) {
            $context[] = 'Active plans:';
            foreach ($plans as $plan) {
                $context[] = '- ' . (string) $plan['name'] . ' (' . number_format((float) $plan['price'], 2) . ', ' . (int) $plan['duration_months'] . ' month(s)).';
            }
        }

        if ($locations !== []) {
            $context[] = 'Active locations:';
            foreach ($locations as $location) {
                $context[] = '- ' . (string) $location['name'] . ': ' . (string) $location['address'] . ' (' . (string) $location['opening_hours'] . ').';
            }
        }

        if ($classes !== []) {
            $context[] = 'Upcoming active classes:';
            foreach ($classes as $class) {
                $context[] = '- ' . (string) $class['title']
                    . ' on ' . (string) $class['class_date']
                    . ' at ' . (string) $class['start_time']
                    . ' with ' . (string) $class['trainer_name']
                    . ' at ' . (string) $class['location_name'] . '.';
            }
        }

        return implode("\n", $context);
    }

    private function requestResponsesApi(string $apiKey, array $payload, int $timeoutSeconds): array
    {
        $ch = curl_init('https://api.openai.com/v1/responses');
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize HTTP request.');
        }

        $jsonPayload = json_encode($payload);
        if ($jsonPayload === false) {
            throw new RuntimeException('Failed to encode request payload.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_TIMEOUT => max(5, $timeoutSeconds),
        ]);

        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('OpenAI request failed: ' . $curlError);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('OpenAI response was not valid JSON.');
        }

        if ($status >= 400) {
            $apiMessage = (string) ($decoded['error']['message'] ?? 'OpenAI API error.');
            throw new RuntimeException($apiMessage);
        }

        return $decoded;
    }

    private function requestWithCompatibilityFallback(string $apiKey, array $payload, int $timeoutSeconds): array
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                return $this->requestResponsesApi($apiKey, $payload, $timeoutSeconds);
            } catch (RuntimeException $e) {
                $message = $e->getMessage();
                if (!preg_match("/Unsupported parameter: '([^']+)'/", $message, $matches)) {
                    throw $e;
                }

                $unsupportedKey = $matches[1] ?? '';
                if ($unsupportedKey === '' || !array_key_exists($unsupportedKey, $payload)) {
                    throw $e;
                }

                unset($payload[$unsupportedKey]);
            }
        }

        throw new RuntimeException('OpenAI request failed due to unsupported parameters.');
    }

    private function extractText(array $response): string
    {
        if (is_string($response['output_text'] ?? null)) {
            return (string) $response['output_text'];
        }

        $chunks = [];
        foreach (($response['output'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }

            foreach (($item['content'] ?? []) as $content) {
                if (!is_array($content)) {
                    continue;
                }

                $text = $content['text'] ?? null;
                if (is_string($text) && $text !== '') {
                    $chunks[] = $text;
                }
            }
        }

        return implode("\n", $chunks);
    }

    private function sanitizeReply(string $text): string
    {
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text) ?? $text;
        $text = preg_replace('/^[\-\*\d\.\)\s]+/m', '', $text) ?? $text;
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
