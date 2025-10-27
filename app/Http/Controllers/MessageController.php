<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\MessagingService;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    protected $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    /**
     * Display the messaging form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('messages');
    }

    /**
     * Send SMS message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendSms(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string',
            'message' => 'required|string|max:1600',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->messagingService->sendSms(
            $request->input('to'),
            $request->input('message')
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Send WhatsApp message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendWhatsapp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string',
            'message' => 'required|string|max:1600',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->messagingService->sendWhatsApp(
            $request->input('to'),
            $request->input('message')
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Receive callback for WhatsApp/SMS/Messenger (Twilio)
     * This handles both inbound messages and status updates
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function receiveCallback(Request $request): JsonResponse
    {
        try {
            $from = $request->input('From');
            $to = $request->input('To');
            $body = $request->input('Body');
            $messageSid = $request->input('MessageSid');
            $messageStatus = $request->input('MessageStatus');
            $numMedia = $request->input('NumMedia', 0);

            // Determine platform from the From/To fields
            $platform = 'sms';
            if (str_contains($from, 'whatsapp:') || str_contains($to, 'whatsapp:')) {
                $platform = 'whatsapp';
            } elseif (str_contains($from, 'messenger:') || str_contains($to, 'messenger:')) {
                $platform = 'messenger';
            }

            // Check if this is a status update (delivery receipt)
            if ($messageStatus && !$body) {
                $this->handleStatusUpdate($messageSid, $messageStatus, $request->all());
                return response()->json([
                    'success' => true,
                    'type' => 'status_update',
                    'status' => $messageStatus
                ]);
            }

            // This is an incoming message
            $messageData = [
                'platform' => $platform,
                'direction' => 'inbound',
                'from_number' => $from,
                'to_number' => $to,
                'message_body' => $body ?: '[Media message]',
                'message_sid' => $messageSid,
                'status' => 'received',
                'sent_at' => now(),
                'metadata' => array_merge($request->all(), [
                    'num_media' => $numMedia,
                    'media_urls' => $this->extractMediaUrls($request, $numMedia)
                ]),
            ];

            $message = Message::create($messageData);

            Log::info('Inbound message saved via callback', [
                'id' => $message->id,
                'platform' => $platform,
                'from' => $from,
                'message_sid' => $messageSid,
                'has_media' => $numMedia > 0
            ]);

            return response()->json([
                'success' => true,
                'type' => 'inbound_message',
                'message_id' => $message->id,
                'platform' => $platform
            ]);

        } catch (\Exception $e) {
            Log::error('Callback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle message status updates (delivered, read, failed, etc.)
     *
     * @param string $messageSid
     * @param string $status
     * @param array $data
     * @return void
     */
    protected function handleStatusUpdate(string $messageSid, string $status, array $data): void
    {
        $message = Message::where('message_sid', $messageSid)->first();

        if ($message) {
            $updateData = ['status' => $status];

            // Set appropriate timestamps based on status
            switch ($status) {
                case 'delivered':
                    $updateData['delivered_at'] = now();
                    break;
                case 'read':
                    $updateData['delivered_at'] = $updateData['delivered_at'] ?? now();
                    $updateData['read_at'] = now();
                    break;
                case 'failed':
                case 'undelivered':
                    $updateData['error_code'] = $data['ErrorCode'] ?? null;
                    $updateData['error_message'] = $data['ErrorMessage'] ?? 'Message delivery failed';
                    break;
            }

            $message->update($updateData);

            Log::info('Message status updated', [
                'message_id' => $message->id,
                'sid' => $messageSid,
                'old_status' => $message->status,
                'new_status' => $status
            ]);
        } else {
            Log::warning('Status update for unknown message', [
                'sid' => $messageSid,
                'status' => $status
            ]);
        }
    }

    /**
     * Extract media URLs from Twilio request
     *
     * @param Request $request
     * @param int $numMedia
     * @return array
     */
    protected function extractMediaUrls(Request $request, int $numMedia): array
    {
        $mediaUrls = [];

        for ($i = 0; $i < $numMedia; $i++) {
            $mediaUrl = $request->input("MediaUrl{$i}");
            $mediaType = $request->input("MediaContentType{$i}");

            if ($mediaUrl) {
                $mediaUrls[] = [
                    'url' => $mediaUrl,
                    'content_type' => $mediaType,
                    'index' => $i
                ];
            }
        }

        return $mediaUrls;
    }

    /**
     * Send Messenger message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessenger(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->messagingService->sendMessenger(
            $request->input('to'),
            $request->input('message')
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Send Telegram message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendTelegram(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string',
            'message' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->messagingService->sendTelegram(
            $request->input('to'),
            $request->input('message')
        );

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Get message history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessages(Request $request): JsonResponse
    {
        $platform = $request->query('platform');
        $direction = $request->query('direction');
        $limit = $request->query('limit', 50);

        $query = Message::recent();

        if ($platform) {
            $query->platform($platform);
        }

        if ($direction) {
            if ($direction === 'inbound') {
                $query->inbound();
            } elseif ($direction === 'outbound') {
                $query->outbound();
            }
        }

        $messages = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'count' => $messages->count(),
        ]);
    }

    /**
     * Webhook handler for Twilio SMS/WhatsApp/Messenger
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function twilioWebhook(Request $request)
    {
        try {
            Log::info('Twilio webhook received', $request->all());

            $from = $request->input('From');
            $to = $request->input('To');
            $body = $request->input('Body');
            $messageSid = $request->input('MessageSid');
            $messageStatus = $request->input('MessageStatus');

            // Determine platform
            $platform = 'sms';
            if (str_starts_with($from, 'whatsapp:')) {
                $platform = 'whatsapp';
            } elseif (str_starts_with($from, 'messenger:')) {
                $platform = 'messenger';
            }

            // Check if this is a status update
            if ($messageStatus) {
                $message = Message::where('message_sid', $messageSid)->first();
                if ($message) {
                    $message->update([
                        'status' => $messageStatus,
                        'delivered_at' => in_array($messageStatus, ['delivered', 'read']) ? now() : null,
                        'read_at' => $messageStatus === 'read' ? now() : null,
                    ]);
                }
            } else {
                // This is an incoming message
                Message::create([
                    'platform' => $platform,
                    'direction' => 'inbound',
                    'from_number' => $from,
                    'to_number' => $to,
                    'message_body' => $body,
                    'message_sid' => $messageSid,
                    'status' => 'received',
                    'sent_at' => now(),
                    'metadata' => $request->all(),
                ]);

                Log::info('Inbound message saved', [
                    'platform' => $platform,
                    'from' => $from,
                    'message_sid' => $messageSid
                ]);
            }

            return response('Message received', 200);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response('Error processing webhook', 500);
        }
    }

    /**
     * Webhook handler for Telegram
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function telegramWebhook(Request $request): JsonResponse
    {
        try {
            Log::info('Telegram webhook received', $request->all());

            $update = $request->all();

            if (isset($update['message'])) {
                $message = $update['message'];
                
                Message::create([
                    'platform' => 'telegram',
                    'direction' => 'inbound',
                    'from_number' => (string)$message['from']['id'],
                    'to_number' => 'bot',
                    'message_body' => $message['text'] ?? '[Media or unsupported content]',
                    'message_sid' => (string)$message['message_id'],
                    'status' => 'received',
                    'sent_at' => now(),
                    'metadata' => $message,
                ]);

                Log::info('Telegram inbound message saved', [
                    'from' => $message['from']['id'],
                    'message_id' => $message['message_id']
                ]);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
