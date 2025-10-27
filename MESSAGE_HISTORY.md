# Message History & Tracking Guide

## Overview

The enhanced messaging interface (`/messages`) provides complete message tracking with:
- ✅ **Outbound messages** (sent by you)
- ✅ **Inbound messages** (received via webhooks)
- ✅ **Real-time statistics**
- ✅ **Platform filtering**
- ✅ **Auto-refresh**

---

## Quick Start

1. **Access the interface:**
   ```
   http://localhost:8000/messages
   ```

2. **Three main tabs:**
   - **Send Messages** - Send SMS, WhatsApp, Messenger, Telegram
   - **Message History** - View all sent/received messages
   - **Statistics** - See message counts and breakdowns

---

## Message History Features

### Filters

| Filter | Options | Description |
|--------|---------|-------------|
| **Platform** | All, SMS, WhatsApp, Messenger, Telegram | Filter by messaging platform |
| **Direction** | All, Outbound, Inbound | Show sent or received messages |
| **Limit** | 50, 100, 200 | Number of messages to display |

### Message Display

Each message shows:
- **Platform badge** - Color-coded by service
- **Direction badge** - Blue (outbound) or Yellow (inbound)
- **Status badge** - Green (success) or Red (failed)
- **From/To numbers** - Sender and recipient
- **Message body** - Full message content
- **Timestamp** - When the message was sent/received
- **Message SID** - Twilio identifier (when available)
- **Error details** - If message failed

### Color Coding

```
📗 Outbound messages - Light blue background
📘 Inbound messages - Light purple background
📕 Failed messages - Light red background
```

---

## How It Works

### Outbound Messages (Sent by You)

When you send a message through the interface:

1. Form submitted via AJAX
2. Controller calls `MessagingService`
3. Message sent via Twilio/Telegram API
4. **Automatically saved to database:**
   ```php
   Message::create([
       'platform' => 'sms',
       'direction' => 'outbound',
       'from_number' => config('services.twilio.phone'),
       'to_number' => '+1234567890',
       'message_body' => 'Hello!',
       'message_sid' => 'SM123456',
       'status' => 'sent'
   ]);
   ```
5. Success/error response shown
6. History refreshed automatically

### Inbound Messages (Received via Webhooks)

When someone sends YOU a message:

1. Twilio/Telegram calls your webhook endpoint
2. Webhook handler extracts message data
3. **Automatically saved to database:**
   ```php
   Message::create([
       'platform' => 'sms',
       'direction' => 'inbound',
       'from_number' => '+1234567890',
       'to_number' => config('services.twilio.phone'),
       'message_body' => 'Hi there!',
       'message_sid' => 'SM654321',
       'status' => 'received'
   ]);
   ```
4. Appears in message history automatically

**Setup required:** See `WEBHOOK_SETUP.md` for webhook configuration

---

## API Endpoints

### Get Message History

```http
GET /api/messages
```

**Query Parameters:**

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `platform` | string | Filter by platform | `?platform=sms` |
| `direction` | string | Filter by direction | `?direction=inbound` |
| `limit` | integer | Max messages | `?limit=100` |

**Example Requests:**

```bash
# Get all messages
curl http://localhost:8000/api/messages

# Get only WhatsApp messages
curl http://localhost:8000/api/messages?platform=whatsapp

# Get only inbound SMS
curl http://localhost:8000/api/messages?platform=sms&direction=inbound

# Get last 200 Telegram messages
curl http://localhost:8000/api/messages?platform=telegram&limit=200
```

**Response:**

```json
{
  "success": true,
  "messages": [
    {
      "id": 1,
      "platform": "sms",
      "direction": "outbound",
      "from_number": "+1234567890",
      "to_number": "+0987654321",
      "message_body": "Hello World",
      "message_sid": "SM123456",
      "status": "sent",
      "error_code": null,
      "error_message": null,
      "created_at": "2025-01-26 10:30:00",
      "sent_at": "2025-01-26 10:30:01"
    }
  ],
  "count": 1
}
```

---

## Database Schema

Messages are stored in the `messages` table:

```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY,
    platform ENUM('sms', 'whatsapp', 'messenger', 'telegram'),
    direction ENUM('outbound', 'inbound'),
    from_number VARCHAR(255),
    to_number VARCHAR(255),
    message_body TEXT,
    message_sid VARCHAR(255),
    status VARCHAR(50),
    error_code VARCHAR(50),
    error_message TEXT,
    metadata JSON,
    sent_at TIMESTAMP,
    delivered_at TIMESTAMP,
    read_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Query Examples

```sql
-- Get all outbound SMS
SELECT * FROM messages 
WHERE platform = 'sms' 
AND direction = 'outbound';

-- Get recent inbound WhatsApp messages
SELECT * FROM messages 
WHERE platform = 'whatsapp' 
AND direction = 'inbound'
ORDER BY created_at DESC 
LIMIT 50;

-- Count failed messages
SELECT COUNT(*) FROM messages 
WHERE status = 'failed';

-- Messages from specific number
SELECT * FROM messages 
WHERE from_number = '+1234567890';
```

---

## Statistics Tab

The **Statistics** tab shows:

### Overall Stats
- **Total Messages** - All messages in database
- **Sent** - Outbound messages count
- **Received** - Inbound messages count
- **Failed** - Failed delivery count

### Platform Breakdown
Shows message count per platform:
- 📱 SMS: X messages
- 💬 WhatsApp: Y messages
- 🔵 Messenger: Z messages
- ✈️ Telegram: W messages

**Auto-updates** when you refresh message history.

---

## Auto-Refresh

The message history automatically refreshes:
- ⏰ Every 30 seconds (when on History tab)
- 🔄 After sending a new message
- 🖱️ When clicking "Refresh" button

To disable auto-refresh, edit `resources/views/messages.blade.php`:

```javascript
// Change interval from 30000 (30 sec) to 0 (disabled)
setInterval(() => {
    if (document.getElementById('history-tab').classList.contains('active')) {
        loadMessages();
    }
}, 30000); // <-- Change this number
```

---

## Testing Message History

### Test Outbound Tracking

1. Go to `/messages`
2. Click **Send Messages** tab
3. Send an SMS:
   - To: Your phone number
   - Message: "Testing outbound"
4. Click **Message History** tab
5. You should see the sent message with:
   - Platform: SMS
   - Direction: OUTBOUND
   - Status: SENT

### Test Inbound Tracking

1. **Setup webhooks** (see `WEBHOOK_SETUP.md`)
2. Send a message TO your Twilio number or Telegram bot
3. Go to `/messages` → **Message History**
4. Filter by "Inbound"
5. You should see the received message

### Test Filtering

1. Send messages on multiple platforms (SMS, WhatsApp, etc.)
2. Go to **Message History**
3. Test filters:
   - Select "Platform: SMS" → only SMS shown
   - Select "Direction: Outbound" → only sent messages
   - Select "Limit: 50" → max 50 messages

---

## Troubleshooting

### "No messages found"

**Possible causes:**
1. No messages sent yet
2. Filters are too restrictive
3. Database not migrated

**Solutions:**
```bash
# Check if messages table exists
php artisan migrate

# Check database directly
php artisan tinker
>>> \App\Models\Message::count()
>>> \App\Models\Message::all()
```

### Messages not saving

**Check:**
1. Database connection in `.env`
2. Laravel logs: `storage/logs/laravel.log`
3. Add debug in `MessagingService.php`:
   ```php
   \Log::info('Saving message', ['to' => $to, 'body' => $message]);
   ```

### Inbound messages not appearing

**Check:**
1. Webhooks configured (see `WEBHOOK_SETUP.md`)
2. ngrok running (for local testing)
3. Laravel logs for webhook errors
4. Twilio/Telegram webhook logs

### Statistics not updating

**Solution:**
Click the **Refresh** button in Message History tab to recalculate stats.

---

## Best Practices

1. **Regular cleanup** - Archive old messages:
   ```sql
   DELETE FROM messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
   ```

2. **Monitor failed messages**:
   ```sql
   SELECT * FROM messages WHERE status = 'failed' ORDER BY created_at DESC;
   ```

3. **Track delivery rates**:
   ```sql
   SELECT 
       platform,
       COUNT(*) as total,
       SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
       ROUND(SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as delivery_rate
   FROM messages
   WHERE direction = 'outbound'
   GROUP BY platform;
   ```

4. **Export data**:
   ```bash
   php artisan tinker
   >>> \App\Models\Message::whereDate('created_at', today())->get()->toJson();
   ```

---

## Advanced Features (Coming Soon)

- 📊 Charts and graphs
- 🔍 Full-text search
- 📅 Date range picker
- 💾 Export to CSV/Excel
- 📧 Email notifications for failed messages
- 🔔 Real-time WebSocket updates
- 📱 Mobile responsive improvements

---

## File Reference

| File | Purpose |
|------|---------|
| `resources/views/messages.blade.php` | Enhanced UI with history |
| `app/Http/Controllers/MessageController.php` | API and webhook handlers |
| `app/Services/MessagingService.php` | Sends messages, saves to DB |
| `app/Models/Message.php` | Eloquent model with scopes |
| `routes/web.php` | Routes for /api/messages and webhooks |
| `database/migrations/*_create_messages_table.php` | Database schema |

---

## Summary

The message history system provides complete visibility into your messaging operations:

✅ **Send messages** across 4 platforms  
✅ **Track outbound** messages automatically  
✅ **Receive inbound** messages via webhooks  
✅ **Filter and search** by platform, direction, date  
✅ **View statistics** in real-time  
✅ **Monitor failures** with error details  

For webhook setup, see: `WEBHOOK_SETUP.md`  
For general testing, see: `docs/TESTING.md`  
For API reference, see: `docs/API_DOCS.md`

**Happy tracking! 📊**
