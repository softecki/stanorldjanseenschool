<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle WhatsApp webhook requests
     * Supports both GET (verification) and POST (incoming messages/events)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request)
    {
        // 1️⃣ VERIFICATION (GET request from Meta)
        if ($request->isMethod('get')) {
            return $this->handleVerification($request);
        }

        // 2️⃣ INCOMING MESSAGES/EVENTS (POST request from WhatsApp)
        if ($request->isMethod('post')) {
            return $this->handleIncomingMessage($request);
        }

        // If neither GET nor POST, return 405 Method Not Allowed
        return response('Method not allowed', 405);
    }

    /**
     * Handle webhook verification from Meta
     * Meta sends: GET /api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=xxx&hub.challenge=xxx
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private function handleVerification(Request $request)
    {
        // WhatsApp sends: hub.mode, hub.verify_token, hub.challenge
        // Laravel converts dots to underscores in query parameters
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');
        $token = "nalopa_school_whatsapp_2026";
        // Hardcoded verify token
        $verifyToken = 'nalopa_school_whatsapp_2026';
         

        // Log verification attempt
        Log::info('WhatsApp Webhook Verification Attempt', [
            'mode' => $mode,
            'token_received' => $token,
            'token_expected' => $verifyToken,
            'challenge' => $challenge
        ]);

        // Verify the mode and token
        if ($mode === 'subscribe' && $token === $verifyToken) {
            // Return the challenge to complete verification
            Log::info('WhatsApp Webhook Verification Successful');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        // Verification failed
        Log::warning('WhatsApp Webhook Verification Failed', [
            'reason' => 'Invalid token or mode',
            'mode' => $mode,
            'token_match' => $token === $verifyToken
        ]);

        return response('Verification failed', 403);
    }

    /**
     * Handle incoming messages and events from WhatsApp
     * WhatsApp sends POST requests with message data
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    private function handleIncomingMessage(Request $request)
    {
        // Log the entire webhook payload
        Log::info('WhatsApp Webhook Received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            $data = $request->all();

            // Extract entry data (WhatsApp sends data in 'entry' array)
            if (isset($data['entry']) && is_array($data['entry'])) {
                foreach ($data['entry'] as $entry) {
                    // Process changes in each entry
                    if (isset($entry['changes']) && is_array($entry['changes'])) {
                        foreach ($entry['changes'] as $change) {
                            $this->processChange($change);
                        }
                    }
                }
            }

            // Always return 200 OK to acknowledge receipt
            return response('EVENT_RECEIVED', 200);

        } catch (\Exception $e) {
            // Log any errors but still return 200 to prevent retries
            Log::error('WhatsApp Webhook Processing Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response('EVENT_RECEIVED', 200);
        }
    }

    /**
     * Process individual change event from WhatsApp
     * Changes can be: messages, message status updates, etc.
     *
     * @param array $change
     * @return void
     */
    private function processChange(array $change)
    {
        $value = $change['value'] ?? [];

        // Log the change type
        Log::info('WhatsApp Webhook Change Event', [
            'change' => $change
        ]);

        // Handle incoming messages
        if (isset($value['messages']) && is_array($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->processMessage($message, $value);
            }
        }

        // Handle message status updates (delivered, read, failed, etc.)
        if (isset($value['statuses']) && is_array($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->processStatus($status);
            }
        }

        // Handle other events (contacts, etc.)
        if (isset($value['contacts'])) {
            Log::info('WhatsApp Contact Event', [
                'contacts' => $value['contacts']
            ]);
        }
    }

    /**
     * Process incoming message
     *
     * @param array $message
     * @param array $value
     * @return void
     */
    private function processMessage(array $message, array $value)
    {
        $messageId = $message['id'] ?? null;
        $from = $message['from'] ?? null;
        $timestamp = $message['timestamp'] ?? null;
        $type = $message['type'] ?? 'unknown';

        // Extract message content based on type
        $content = $this->extractMessageContent($message);

        // Log the message details
        Log::info('WhatsApp Incoming Message', [
            'message_id' => $messageId,
            'from' => $from,
            'type' => $type,
            'content' => $content,
            'timestamp' => $timestamp,
            'metadata' => $value['metadata'] ?? null,
            'full_message' => $message
        ]);

        // TODO: Add your message processing logic here
        // Examples:
        // - Save message to database
        // - Auto-reply based on keywords
        // - Lookup student/parent by phone number
        // - Process fee inquiries
        // - Send notifications
    }

    /**
     * Extract message content based on message type
     *
     * @param array $message
     * @return array|string|null
     */
    private function extractMessageContent(array $message)
    {
        $type = $message['type'] ?? 'text';

        switch ($type) {
            case 'text':
                return $message['text']['body'] ?? null;

            case 'image':
                return [
                    'caption' => $message['image']['caption'] ?? null,
                    'id' => $message['image']['id'] ?? null,
                    'mime_type' => $message['image']['mime_type'] ?? null
                ];

            case 'document':
                return [
                    'caption' => $message['document']['caption'] ?? null,
                    'filename' => $message['document']['filename'] ?? null,
                    'id' => $message['document']['id'] ?? null,
                    'mime_type' => $message['document']['mime_type'] ?? null
                ];

            case 'audio':
                return [
                    'id' => $message['audio']['id'] ?? null,
                    'mime_type' => $message['audio']['mime_type'] ?? null
                ];

            case 'video':
                return [
                    'caption' => $message['video']['caption'] ?? null,
                    'id' => $message['video']['id'] ?? null,
                    'mime_type' => $message['video']['mime_type'] ?? null
                ];

            case 'location':
                return [
                    'latitude' => $message['location']['latitude'] ?? null,
                    'longitude' => $message['location']['longitude'] ?? null,
                    'name' => $message['location']['name'] ?? null,
                    'address' => $message['location']['address'] ?? null
                ];

            case 'contacts':
                return $message['contacts'] ?? null;

            default:
                return $message;
        }
    }

    /**
     * Process message status updates (delivered, read, failed, etc.)
     *
     * @param array $status
     * @return void
     */
    private function processStatus(array $status)
    {
        $messageId = $status['id'] ?? null;
        $statusType = $status['status'] ?? 'unknown';
        $recipientId = $status['recipient_id'] ?? null;
        $timestamp = $status['timestamp'] ?? null;
        $errors = $status['errors'] ?? null;

        // Log the status update with detailed information
        Log::info('WhatsApp Message Status Update', [
            'message_id' => $messageId,
            'status' => $statusType, // sent, delivered, read, failed
            'recipient_id' => $recipientId,
            'timestamp' => $timestamp,
            'errors' => $errors,
            'full_status' => $status
        ]);

        // Log specific error details if message failed
        if ($statusType === 'failed' && isset($errors)) {
            foreach ($errors as $error) {
                Log::error('WhatsApp Message Delivery Failed', [
                    'message_id' => $messageId,
                    'error_code' => $error['code'] ?? null,
                    'error_title' => $error['title'] ?? null,
                    'error_message' => $error['message'] ?? null,
                    'error_details' => $error['error_data'] ?? null,
                    'error_href' => $error['href'] ?? null
                ]);
            }
        }

        // TODO: Add your status processing logic here
        // Examples:
        // - Update message status in database
        // - Track delivery/read receipts
        // - Handle failed messages
    }
}

