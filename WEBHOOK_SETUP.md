# Webhook Setup Guide

This guide explains how to configure webhooks to receive inbound messages from Twilio and Telegram.

## Table of Contents
- [Overview](#overview)
- [Local Testing with ngrok](#local-testing-with-ngrok)
- [Twilio Webhook Setup](#twilio-webhook-setup)
- [Telegram Webhook Setup](#telegram-webhook-setup)
- [Testing Webhooks](#testing-webhooks)
- [Troubleshooting](#troubleshooting)

---

## Overview

Webhooks allow external services to send HTTP requests to your application when events occur. This CRM system uses webhooks to:

- **Receive inbound SMS and WhatsApp messages** (via Twilio)
- **Receive inbound Telegram messages** (via Telegram Bot API)
- **Track message delivery status** (delivered, failed, etc.)

All inbound messages are automatically saved to the database with `direction = 'inbound'`.

---

## Local Testing with ngrok

For local development, use **ngrok** to expose your Laravel app to the internet.

### Step 1: Install ngrok

Download from: https://ngrok.com/download

### Step 2: Start ngrok

```bash
ngrok http 8000
```

This creates a public URL like: `https://abc123.ngrok.io`

### Step 3: Note Your Webhook URLs

Once ngrok is running, your webhook endpoints will be:

- **Twilio Webhook**: `https://abc123.ngrok.io/webhook/twilio`
- **Telegram Webhook**: `https://abc123.ngrok.io/webhook/telegram`

**⚠️ Important:** These URLs change every time you restart ngrok (unless you have a paid plan).

---

## Twilio Webhook Setup

### For SMS Messages

1. **Login to Twilio Console**: https://console.twilio.com/
2. **Navigate to Phone Numbers**:
   - Go to **Phone Numbers** → **Manage** → **Active numbers**
   - Click on your phone number
3. **Configure Messaging Webhook**:
   - Scroll to **Messaging Configuration**
   - Under "A MESSAGE COMES IN", set:
     - **Webhook URL**: `https://your-domain.com/webhook/twilio`
     - **HTTP Method**: `POST`
4. **Save Changes**

### For WhatsApp Messages

1. **Login to Twilio Console**: https://console.twilio.com/
2. **Navigate to Messaging** → **Try it out** → **Send a WhatsApp message**
3. **Find Sandbox Settings**:
   - Click on **Sandbox settings**
4. **Configure Webhook**:
   - Under "WHEN A MESSAGE COMES IN", set:
     - **Webhook URL**: `https://your-domain.com/webhook/twilio`
     - **HTTP Method**: `POST`
5. **Save**

### What Data Twilio Sends

When someone sends you a message, Twilio will POST data like:

```
MessageSid=SMxxxx
From=+1234567890
To=whatsapp:+1987654321
Body=Hello, this is a test message
```

Your webhook handler automatically:
- Saves the message to database
- Sets `direction = 'inbound'`
- Stores sender/receiver info
- Records the message body

---

## Telegram Webhook Setup

Telegram uses the Bot API and requires you to register your webhook programmatically.

### Step 1: Get Your Bot Token

If you don't have one:
1. Talk to **@BotFather** on Telegram
2. Create a new bot with `/newbot`
3. Copy the bot token (e.g., `123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11`)

### Step 2: Set Webhook URL

Run this command (replace values):

```bash
curl -X POST "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://your-domain.com/webhook/telegram"
  }'
```

**Windows PowerShell version:**
```powershell
Invoke-WebRequest -Uri "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"url": "https://your-domain.com/webhook/telegram"}'
```

**Response (success):**
```json
{
  "ok": true,
  "result": true,
  "description": "Webhook was set"
}
```

### Step 3: Verify Webhook

Check your webhook status:

```bash
curl "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo"
```

### What Data Telegram Sends

When someone messages your bot, Telegram sends:

```json
{
  "update_id": 123456789,
  "message": {
    "message_id": 1,
    "from": {
      "id": 987654321,
      "first_name": "John"
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

Your webhook handler extracts:
- Chat ID (for the sender)
- Message text
- Saves to database as `direction = 'inbound'`

---

## Testing Webhooks

### Test 1: Send a Message to Your Number/Bot

**For Twilio (SMS):**
1. Send an SMS to your Twilio phone number
2. Check the database: `SELECT * FROM messages WHERE direction = 'inbound';`
3. Visit `/messages` in your browser
4. Click "Message History" tab
5. Filter by "Inbound" to see received messages

**For Telegram:**
1. Find your bot on Telegram (search for @YourBotName)
2. Send it a message: "Hello!"
3. Check the database for the inbound message
4. View in `/messages` page

### Test 2: Check Webhook Logs

Add temporary logging to debug:

**In `app/Http/Controllers/MessageController.php`:**

```php
public function twilioWebhook(Request $request)
{
    \Log::info('Twilio Webhook Received', $request->all());
    // ... rest of the method
}
```

Then check logs:
```bash
tail -f storage/logs/laravel.log
```

### Test 3: Simulate Webhook Locally

Use **Postman** or **curl** to test:

```bash
curl -X POST http://localhost:8000/webhook/twilio \
  -d "MessageSid=TEST123" \
  -d "From=+1234567890" \
  -d "To=+0987654321" \
  -d "Body=Test message"
```

---

## Troubleshooting

### Issue: "Webhook not receiving messages"

**Solutions:**
1. **Check ngrok is running**
   ```bash
   ngrok http 8000
   ```
2. **Verify webhook URL is correct** in Twilio/Telegram
3. **Check Laravel logs** for errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```
4. **Ensure CSRF exemption** (already configured in `VerifyCsrfToken.php`)

### Issue: "403 Forbidden" or "419 CSRF token mismatch"

**Solution:**
Webhook routes must be exempt from CSRF. Check `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    '/webhook/twilio',
    '/webhook/telegram',
];
```

### Issue: "Messages not saving to database"

**Checklist:**
1. Run migrations: `php artisan migrate`
2. Check database connection in `.env`
3. Add debug logging:
   ```php
   \Log::info('Saving message', ['data' => $request->all()]);
   ```

### Issue: Telegram "Webhook not found"

**Solution:**
Re-register the webhook:
```bash
curl -X POST "https://api.telegram.org/bot<TOKEN>/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://your-new-url.com/webhook/telegram"}'
```

### Issue: ngrok URL keeps changing

**Solutions:**
1. **Free option**: Update webhooks each time you restart ngrok
2. **Paid option**: Get ngrok Pro for a static domain
3. **Production**: Use your actual domain (no ngrok needed)

---

## Production Deployment

When deploying to production:

1. **Remove ngrok** - Use your real domain
2. **Update Twilio webhooks** to `https://yourdomain.com/webhook/twilio`
3. **Update Telegram webhook** to `https://yourdomain.com/webhook/telegram`
4. **Enable HTTPS** - Webhooks require SSL certificates
5. **Monitor logs** - Set up error monitoring (Sentry, Bugsnag, etc.)

---

## Webhook Endpoints Summary

| Service | Endpoint | Method | Purpose |
|---------|----------|--------|---------|
| Twilio | `/webhook/twilio` | POST | Receive SMS/WhatsApp inbound messages |
| Telegram | `/webhook/telegram` | POST | Receive Telegram bot messages |

Both endpoints:
- ✅ Auto-save to database
- ✅ Set `direction = 'inbound'`
- ✅ CSRF exempt
- ✅ Return 200 OK on success

---

## Next Steps

1. ✅ Setup webhooks (this guide)
2. Send test messages to your numbers/bot
3. Check `/messages` page to see inbound messages
4. Monitor the "Message History" tab
5. View statistics in the "Statistics" tab

**Need help?** Check:
- `docs/TESTING.md` for testing instructions
- `docs/API_DOCS.md` for API reference
- `docs/WHATSAPP_SETUP.md` for WhatsApp-specific setup

---

**Happy messaging! 🚀**
