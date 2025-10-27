<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use App\Models\Message;

class MessagingService
{
    protected $twilioClient;
    protected $telegramClient;

    public function __construct()
    {
        $this->twilioClient = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );

        $this->telegramClient = new GuzzleClient([
            'base_uri' => config('services.telegram.api_url'),
            'timeout' => 10,
        ]);
    }

    /**
     * Send SMS via Twilio
     *
     * @param string $to
     * @param string $message
     * @return array
     */
    public function sendSms(string $to, string $message): array
    {
        try {
            $result = $this->twilioClient->messages->create(
                $to,
                [
                    'from' => config('services.twilio.sms_from'),
                    'body' => $message
                ]
            );

            Log::info('SMS sent successfully', [
                'to' => $to,
                'sid' => $result->sid
            ]);

            // Save to database
            Message::create([
                'platform' => 'sms',
                'direction' => 'outbound',
                'from_number' => config('services.twilio.sms_from'),
                'to_number' => $to,
                'message_body' => $message,
                'message_sid' => $result->sid,
                'status' => $result->status,
                'sent_at' => now(),
                'metadata' => [
                    'price' => $result->price ?? null,
                    'price_unit' => $result->priceUnit ?? null,
                ]
            ]);

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'sid' => $result->sid,
                'status' => $result->status,
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio SMS Error: ' . $e->getMessage());

            // Save failed message
            Message::create([
                'platform' => 'sms',
                'direction' => 'outbound',
                'from_number' => config('services.twilio.sms_from'),
                'to_number' => $to,
                'message_body' => $message,
                'status' => 'failed',
                'error_code' => (string)$e->getCode(),
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            Log::error('SMS Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send WhatsApp message via Twilio
     *
     * @param string $to
     * @param string $message
     * @return array
     */
    public function sendWhatsApp(string $to, string $message): array
    {
        try {
            // Ensure the 'to' number is in WhatsApp format
            if (!str_starts_with($to, 'whatsapp:')) {
                $to = 'whatsapp:' . $to;
            }

            $result = $this->twilioClient->messages->create(
            $to,
            [
                'from' => config('services.twilio.whatsapp_from'),
                'body' => $message
                ]
            );

            // Save to database
            Message::create([
                'platform' => 'whatsapp',
                'direction' => 'outbound',
                'from_number' => config('services.twilio.whatsapp_from'),
                'to_number' => $to,
                'message_body' => $message,
                'message_sid' => $result->sid,
                'status' => $result->status,
                'sent_at' => now(),
                'metadata' => [
                    'price' => $result->price ?? null,
                    'price_unit' => $result->priceUnit ?? null,
                ]
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'sid' => $result->sid,
                'status' => $result->status,
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio WhatsApp Error: ' . $e->getMessage());

            // Save failed message
            Message::create([
                'platform' => 'whatsapp',
                'direction' => 'outbound',
                'from_number' => config('services.twilio.whatsapp_from'),
                'to_number' => $to,
                'message_body' => $message,
                'status' => 'failed',
                'error_code' => (string)$e->getCode(),
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp message: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send Messenger message via Twilio
     *
     * @param string $to
     * @param string $message
     * @return array
     */
    public function sendMessenger(string $to, string $message): array
    {
        try {
            // Ensure the 'to' is in Messenger format
            if (!str_starts_with($to, 'messenger:')) {
                $to = 'messenger:' . $to;
            }

            $result = $this->twilioClient->messages->create(
                $to,
                [
                    'from' => config('services.twilio.messenger_from'),
                    'body' => $message
                ]
            );

            Log::info('Messenger message sent successfully', [
                'to' => $to,
                'sid' => $result->sid
            ]);

            // Save to database
            Message::create([
                'platform' => 'messenger',
                'direction' => 'outbound',
                'from_number' => config('services.twilio.messenger_from'),
                'to_number' => $to,
                'message_body' => $message,
                'message_sid' => $result->sid,
                'status' => $result->status,
                'sent_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Messenger message sent successfully',
                'sid' => $result->sid,
                'status' => $result->status,
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio Messenger Error: ' . $e->getMessage());

            // Save failed message
            Message::create([
                'platform' => 'messenger',
                'direction' => 'outbound',
                'from_number' => config('services.twilio.messenger_from'),
                'to_number' => $to,
                'message_body' => $message,
                'status' => 'failed',
                'error_code' => (string)$e->getCode(),
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send Messenger message: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            Log::error('Messenger Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send Telegram message
     *
     * @param string $chatId
     * @param string $message
     * @return array
     */
    public function sendTelegram(string $chatId, string $message): array
    {
        try {
            $botToken = config('services.telegram.bot_token');

            if (empty($botToken)) {
                throw new \Exception('Telegram bot token is not configured');
            }

            $response = $this->telegramClient->post($botToken . '/sendMessage', [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($body['ok']) {
                Log::info('Telegram message sent successfully', [
                    'chat_id' => $chatId,
                    'message_id' => $body['result']['message_id']
                ]);

                // Save to database
                Message::create([
                    'platform' => 'telegram',
                    'direction' => 'outbound',
                    'from_number' => 'bot',
                    'to_number' => $chatId,
                    'message_body' => $message,
                    'message_sid' => (string)$body['result']['message_id'],
                    'status' => 'sent',
                    'sent_at' => now(),
                    'metadata' => [
                        'chat_id' => $chatId,
                        'message_id' => $body['result']['message_id'],
                    ]
                ]);

                return [
                    'success' => true,
                    'message' => 'Telegram message sent successfully',
                    'message_id' => $body['result']['message_id'],
                ];
            } else {
                throw new \Exception($body['description'] ?? 'Unknown error');
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorMessage = $responseBody['description'] ?? $errorMessage;
            }

            Log::error('Telegram API Error: ' . $errorMessage);

            // Save failed message
            Message::create([
                'platform' => 'telegram',
                'direction' => 'outbound',
                'from_number' => 'bot',
                'to_number' => $chatId,
                'message_body' => $message,
                'status' => 'failed',
                'error_message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send Telegram message: ' . $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('Telegram Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }
}
