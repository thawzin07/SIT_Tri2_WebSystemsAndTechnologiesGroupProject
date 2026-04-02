<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ChatbotService;
use Throwable;

class ChatbotController extends Controller
{
    public function ask(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Message is required.']);
            return;
        }

        $messageLength = function_exists('mb_strlen') ? mb_strlen($message) : strlen($message);
        if ($messageLength > 1000) {
            http_response_code(422);
            echo json_encode(['error' => 'Message is too long.']);
            return;
        }

        try {
            $reply = (new ChatbotService())->ask($message);
            echo json_encode(['reply' => $reply]);
        } catch (Throwable $e) {
            error_log('Chatbot request failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Chatbot is currently unavailable. Please try again shortly.',
            ]);
        }
    }
}
