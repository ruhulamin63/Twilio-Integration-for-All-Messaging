# 📞 Callback Setup Guide

This guide explains how to configure callbacks for receiving inbound messages and status updates across all messaging platforms.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Callback vs Webhook - What's the Difference?](#callback-vs-webhook---whats-the-difference)
- [Available Callback Endpoints](#available-callback-endpoints)
- [Twilio SMS Callback Setup](#twilio-sms-callback-setup)
- [Twilio WhatsApp Callback Setup](#twilio-whatsapp-callback-setup)
- [Twilio Messenger Callback Setup](#twilio-messenger-callback-setup)
- [Telegram Webhook Setup](#telegram-webhook-setup)
- [Testing Callbacks](#testing-callbacks)
- [Callback Data Reference](#callback-data-reference)
- [Troubleshooting](#troubleshooting)

---

## Overview

Callbacks allow external messaging services to notify your application when:

✅ **Inbound messages arrive** - Someone sends you a message  
✅ **Message status changes** - Delivered, read, failed, etc.  
✅ **Media is received** - Images, videos, documents  

All callbacks automatically:
- Save messages to database
- Update message status
- Log all events
- Return proper HTTP responses

---

## Callback vs Webhook - What's the Difference?

### Webhooks (`/webhook/*`)
- **Purpose:** General-purpose inbound message receivers
- **Use case:** Primary integration for receiving messages
- **Endpoints:** `/webhook/twilio`, `/webhook/telegram`

### Callbacks (`/callback/*`)
- **Purpose:** Enhanced handlers with status updates and media support
- **Use case:** Advanced features like delivery receipts, read receipts, media handling
- **Endpoints:** `/callback/sms`, `/callback/whatsapp`, `/callback/messenger`, `/callback/twilio`

**Recommendation:** Use **callbacks** for production as they handle more scenarios.

---

## Available Callback Endpoints

All endpoints are **POST** only and **CSRF exempt**.

| Endpoint | Platform | Use For |
|----------|----------|---------|
| `/callback/twilio` | All Twilio (SMS/WhatsApp/Messenger) | Recommended - handles all Twilio platforms |
| `/callback/sms` | SMS only | Specific SMS callback |
| `/callback/whatsapp` | WhatsApp only | Specific WhatsApp callback |
| `/callback/messenger` | Messenger only | Specific Messenger callback |
| `/webhook/telegram` | Telegram | Telegram bot updates |

**Best Practice:** Use `/callback/twilio` for all Twilio-based platforms (SMS, WhatsApp, Messenger).

---

## Twilio SMS Callback Setup

### Step 1: Login to Twilio Console

Visit: https://console.twilio.com/

### Step 2: Navigate to Phone Numbers

1. Click **Phone Numbers** in left sidebar
2. Click **Manage** → **Active numbers**
3. Click on your phone number

### Step 3: Configure Messaging Callbacks

Scroll to **Messaging Configuration** section:

#### A. Inbound Messages

**When a message comes in:**
- **Webhook URL**: `https://your-domain.com/callback/twilio`
- **HTTP Method**: `POST`

#### B. Status Callbacks (Optional but Recommended)

**Status Callback URL:**
- **Webhook URL**: `https://your-domain.com/callback/twilio`
- **HTTP Method**: `POST`

**Status Callback Events** (check all):
- ✅ Delivered
- ✅ Failed
- ✅ Sent
- ✅ Undelivered

### Step 4: Save Configuration

Click **Save** at the bottom of the page.

### Step 5: Test

Send an SMS to your Twilio number:
```
From your phone → To: +1234567890 (your Twilio number)
Message: "Test inbound SMS"
```

Check database:
```sql
SELECT * FROM messages WHERE direction = 'inbound' AND platform = 'sms' ORDER BY created_at DESC LIMIT 1;
```

---

## Twilio WhatsApp Callback Setup

### For Sandbox (Testing)

#### Step 1: Access WhatsApp Sandbox

1. Go to Twilio Console
2. Click **Messaging** → **Try it out** → **Send a WhatsApp message**
3. Click **Sandbox settings**

#### Step 2: Configure Callback

**When a message comes in:**
- **Webhook URL**: `https://your-domain.com/callback/whatsapp`
  - OR use: `https://your-domain.com/callback/twilio` (recommended)
- **HTTP Method**: `POST`

**Status Callback URL:**
- **Webhook URL**: `https://your-domain.com/callback/twilio`
- **HTTP Method**: `POST`

#### Step 3: Save

Click **Save**.

#### Step 4: Test

1. Join sandbox (send join code to sandbox number)
2. Send a WhatsApp message to the sandbox number
3. Check database for inbound message

### For Production WhatsApp Business API

Once approved for WhatsApp Business API:

1. Go to **Messaging** → **Senders** → **WhatsApp senders**
2. Click on your WhatsApp sender
3. Configure callbacks:
   - **Inbound**: `https://your-domain.com/callback/twilio`
   - **Status**: `https://your-domain.com/callback/twilio`

---

## Twilio Messenger Callback Setup

### Prerequisites

- Facebook Page connected to Twilio
- Messenger sender configured in Twilio Console

### Step 1: Navigate to Messenger Senders

1. Twilio Console → **Messaging** → **Senders**
2. Click **Messenger senders**
3. Click on your Facebook Page

### Step 2: Configure Callback

**Callback URL:**
- **Webhook URL**: `https://your-domain.com/callback/messenger`
  - OR use: `https://your-domain.com/callback/twilio` (recommended)
- **HTTP Method**: `POST`

### Step 3: Save and Test

Send a message from Facebook Messenger to your Page.

---

## Telegram Webhook Setup

Telegram uses webhooks (not callbacks) via Bot API.

### Step 1: Get Your Bot Token

If you don't have one:
```
1. Open Telegram
2. Search for @BotFather
3. Send: /newbot
4. Follow instructions
5. Copy the token
```

### Step 2: Set Webhook URL

**Using cURL (Windows PowerShell):**

```powershell
$token = "YOUR_BOT_TOKEN"
$url = "https://your-domain.com/webhook/telegram"

Invoke-WebRequest -Uri "https://api.telegram.org/bot$token/setWebhook" `
  -Method POST `
  -ContentType "application/json" `
  -Body "{`"url`":`"$url`"}"
```

**Using cURL (bash):**

```bash
curl -X POST "https://api.telegram.org/bot<YOUR_TOKEN>/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://your-domain.com/webhook/telegram"}'
```

### Step 3: Verify Webhook

```powershell
Invoke-WebRequest -Uri "https://api.telegram.org/bot$token/getWebhookInfo"
```

Response should show:
```json
{
  "ok": true,
  "result": {
    "url": "https://your-domain.com/webhook/telegram",
    "has_custom_certificate": false,
    "pending_update_count": 0
  }
}
```

### Step 4: Test

Send a message to your bot on Telegram and check the database.

---

## Testing Callbacks

### Local Testing with ngrok

For development, use ngrok to expose your local server:

#### 1. Install ngrok

Download from: https://ngrok.com/download

#### 2. Start Laravel

```bash
php artisan serve
```

#### 3. Start ngrok

```bash
ngrok http 8000
```

Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)

#### 4. Configure Callbacks

Use your ngrok URL:
- SMS: `https://abc123.ngrok.io/callback/sms`
- WhatsApp: `https://abc123.ngrok.io/callback/whatsapp`
- Twilio (all): `https://abc123.ngrok.io/callback/twilio`
- Telegram: `https://abc123.ngrok.io/webhook/telegram`

### Test Inbound Messages

#### Test SMS
```
1. Send SMS to your Twilio number
2. Check Laravel logs: tail -f storage/logs/laravel.log
3. Check database: SELECT * FROM messages WHERE platform='sms' AND direction='inbound';
```

#### Test WhatsApp
```
1. Send WhatsApp to sandbox number
2. Check logs
3. Check database: SELECT * FROM messages WHERE platform='whatsapp' AND direction='inbound';
```

#### Test Telegram
```
1. Send message to your bot
2. Check logs
3. Check database: SELECT * FROM messages WHERE platform='telegram' AND direction='inbound';
```

### Test Status Updates

Send a message from the UI, then check for status updates:

```sql
SELECT message_sid, status, sent_at, delivered_at, read_at 
FROM messages 
WHERE direction = 'outbound' 
ORDER BY created_at DESC 
LIMIT 5;
```

---

## Callback Data Reference

### What Data is Received?

#### SMS Callback (Twilio)

**Inbound Message:**
```
MessageSid: SM1234567890abcdef
From: +1234567890
To: +0987654321
Body: Hello, this is a test
NumMedia: 0
```

**Status Update:**
```
MessageSid: SM1234567890abcdef
MessageStatus: delivered
ErrorCode: null
```

#### WhatsApp Callback (Twilio)

**Inbound Message:**
```
MessageSid: SM1234567890abcdef
From: whatsapp:+1234567890
To: whatsapp:+14155238886
Body: Hi from WhatsApp
ProfileName: John Doe
NumMedia: 1
MediaUrl0: https://api.twilio.com/...
MediaContentType0: image/jpeg
```

#### Telegram Webhook

**Inbound Message:**
```json
{
  "update_id": 123456789,
  "message": {
    "message_id": 1,
    "from": {
      "id": 987654321,
      "first_name": "John",
      "username": "johndoe"
    },
    "chat": {
      "id": 987654321,
      "type": "private"
    },
    "date": 1234567890,
    "text": "Hello bot!"
  }
}
```

### What Gets Saved?

All callbacks save to `messages` table:

```php
[
    'platform' => 'sms|whatsapp|messenger|telegram',
    'direction' => 'inbound',
    'from_number' => '+1234567890',
    'to_number' => '+0987654321',
    'message_body' => 'Message text',
    'message_sid' => 'SM123456...',
    'status' => 'received',
    'sent_at' => '2025-10-26 10:30:00',
    'metadata' => { /* full callback data */ }
]
```

---

## Troubleshooting

### Issue: "Callback not receiving data"

**Solutions:**

1. **Check ngrok is running:**
   ```bash
   ngrok http 8000
   ```

2. **Verify callback URL in Twilio Console**
   - Must be HTTPS (ngrok provides this)
   - Must be publicly accessible

3. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Test callback manually:**
   ```bash
   curl -X POST http://localhost:8000/callback/twilio \
     -d "From=+1234567890" \
     -d "To=+0987654321" \
     -d "Body=Test" \
     -d "MessageSid=TEST123"
   ```

### Issue: "419 CSRF Token Mismatch"

**Solution:** Callbacks are already exempt from CSRF. If you still get this error:

Check `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    '/callback/*',
    '/webhook/*',
];
```

### Issue: "Messages not saving to database"

**Checklist:**

1. ✅ Migrations run: `php artisan migrate`
2. ✅ Database connected (check `.env`)
3. ✅ Callback URL is correct
4. ✅ Laravel logs show callback received

**Debug:**

Add to `receiveCallback()`:
```php
\Log::info('Callback data:', $request->all());
```

### Issue: "Status updates not working"

**Solution:**

Enable status callbacks in Twilio Console:
1. Phone Numbers → Your number
2. Messaging Configuration
3. Status Callback URL: `https://your-domain.com/callback/twilio`
4. Check all status events

### Issue: "Media messages show '[Media message]'"

**Expected behavior.** Media handling:

1. Check `metadata` column for `media_urls`
2. Download media from Twilio URLs
3. Implement media storage as needed

**Query media messages:**
```sql
SELECT * FROM messages WHERE message_body = '[Media message]';
```

### Issue: "Telegram webhook 'not found'"

**Solution:**

Re-register webhook:
```bash
curl -X POST "https://api.telegram.org/bot<TOKEN>/setWebhook" \
  -d "url=https://your-new-url.com/webhook/telegram"
```

Delete old webhook first if needed:
```bash
curl -X POST "https://api.telegram.org/bot<TOKEN>/deleteWebhook"
```

---

## Advanced Features

### Handling Media Files

Media URLs are stored in `metadata->media_urls`:

```php
$message = Message::find(1);
$mediaUrls = $message->metadata['media_urls'] ?? [];

foreach ($mediaUrls as $media) {
    echo "URL: " . $media['url'] . "\n";
    echo "Type: " . $media['content_type'] . "\n";
}
```

### Automatic Replies

Add to `receiveCallback()` after saving message:

```php
// Auto-reply to inbound messages
if ($platform === 'telegram') {
    $this->messagingService->sendTelegram(
        $message->from_number,
        "Thanks for your message!"
    );
}
```

### Custom Status Handling

Override `handleStatusUpdate()`:

```php
protected function handleStatusUpdate(string $messageSid, string $status, array $data): void
{
    parent::handleStatusUpdate($messageSid, $status, $data);
    
    // Custom logic
    if ($status === 'failed') {
        // Send alert email
        Mail::to('admin@example.com')->send(new MessageFailedAlert($messageSid));
    }
}
```

---

## Callback Endpoints Summary

| Endpoint | Platform | Handles | Recommended For |
|----------|----------|---------|-----------------|
| `/callback/twilio` | SMS, WhatsApp, Messenger | Inbound + Status | ✅ All Twilio platforms |
| `/callback/sms` | SMS only | Inbound + Status | Specific SMS setup |
| `/callback/whatsapp` | WhatsApp only | Inbound + Status | Specific WhatsApp setup |
| `/callback/messenger` | Messenger only | Inbound + Status | Specific Messenger setup |
| `/webhook/telegram` | Telegram | Inbound only | Telegram bots |
| `/webhook/twilio` | SMS, WhatsApp, Messenger | Inbound only | Alternative to callbacks |

**Production Recommendation:**
- Use `/callback/twilio` for all Twilio-based messaging
- Use `/webhook/telegram` for Telegram

---

## Security Best Practices

### 1. Validate Twilio Requests

Add Twilio signature validation:

```php
use Twilio\Security\RequestValidator;

public function receiveCallback(Request $request): JsonResponse
{
    $validator = new RequestValidator(config('services.twilio.auth_token'));
    
    $signature = $request->header('X-Twilio-Signature');
    $url = $request->fullUrl();
    $params = $request->all();
    
    if (!$validator->validate($signature, $url, $params)) {
        return response()->json(['error' => 'Invalid signature'], 403);
    }
    
    // Process callback...
}
```

### 2. Rate Limiting

Add to `routes/web.php`:

```php
Route::post('/callback/twilio', [MessageController::class, 'receiveCallback'])
    ->middleware('throttle:100,1'); // 100 requests per minute
```

### 3. IP Whitelisting

Twilio IPs: https://www.twilio.com/docs/usage/webhooks/ip-addresses

---

## Next Steps

1. ✅ Configure callbacks in Twilio Console
2. ✅ Set up ngrok for local testing
3. ✅ Test inbound messages
4. ✅ Enable status callbacks
5. ✅ Monitor Laravel logs
6. ✅ Check message history at `/messages`

---

**For webhook configuration, see:** [WEBHOOK_SETUP.md](WEBHOOK_SETUP.md)  
**For message history, see:** [MESSAGE_HISTORY.md](MESSAGE_HISTORY.md)  
**For general testing, see:** [TESTING.md](TESTING.md)

**Happy messaging! 📱✨**
