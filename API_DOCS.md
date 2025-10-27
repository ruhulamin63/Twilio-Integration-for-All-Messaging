# API Documentation - CRM Messaging Integration

## Base URL
```
http://localhost:8000
```

---

## Endpoints

### 1. Send SMS

Send an SMS message via Twilio.

**Endpoint:** `POST /send-sms`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "to": "string (required) - Phone number in E.164 format (+1234567890)",
    "message": "string (required) - Message content (max 1600 characters)"
}
```

**Example Request:**
```json
{
    "to": "+14155551234",
    "message": "Hello! This is a test SMS message."
}
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "SMS sent successfully",
    "sid": "SM1234567890abcdef1234567890abcdef",
    "status": "queued"
}
```

**Validation Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "to": ["The to field is required."],
        "message": ["The message field is required."]
    }
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Failed to send SMS: The 'To' number is not a valid phone number.",
    "error_code": 21211
}
```

---

### 2. Send WhatsApp

Send a WhatsApp message via Twilio.

**Endpoint:** `POST /send-whatsapp`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "to": "string (required) - Phone number in E.164 format or with 'whatsapp:' prefix",
    "message": "string (required) - Message content (max 1600 characters)"
}
```

**Example Request:**
```json
{
    "to": "+14155551234",
    "message": "Hello! This is a WhatsApp message 📱"
}
```

**Note:** The service automatically adds `whatsapp:` prefix if not present.

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "WhatsApp message sent successfully",
    "sid": "SM1234567890abcdef1234567890abcdef",
    "status": "queued"
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Failed to send WhatsApp message: [error details]",
    "error_code": 63007
}
```

---

### 3. Send Messenger

Send a Facebook Messenger message via Twilio.

**Endpoint:** `POST /send-messenger`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "to": "string (required) - Facebook User ID or Page ID",
    "message": "string (required) - Message content (max 2000 characters)"
}
```

**Example Request:**
```json
{
    "to": "1234567890",
    "message": "Hello via Facebook Messenger!"
}
```

**Note:** Requires Twilio Messenger channel setup.

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Messenger message sent successfully",
    "sid": "SM1234567890abcdef1234567890abcdef",
    "status": "queued"
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Failed to send Messenger message: [error details]",
    "error_code": 21606
}
```

---

### 4. Send Telegram

Send a Telegram message via Telegram Bot API.

**Endpoint:** `POST /send-telegram`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "to": "string (required) - Telegram Chat ID or @username",
    "message": "string (required) - Message content (max 4096 characters, HTML supported)"
}
```

**Example Request:**
```json
{
    "to": "123456789",
    "message": "Hello! This is a <b>Telegram</b> message with <i>HTML formatting</i>."
}
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Telegram message sent successfully",
    "message_id": 123
}
```

**Error Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Failed to send Telegram message: Bad Request: chat not found"
}
```

---

## Error Codes

### Twilio Error Codes

| Code | Description |
|------|-------------|
| 20003 | Permission Denied |
| 21211 | Invalid 'To' Phone Number |
| 21408 | Permission to send SMS not enabled |
| 21610 | Message cannot be sent to this number |
| 63007 | WhatsApp capable 'To' number not found |

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 422 | Validation Error |
| 500 | Server/API Error |

---

## Validation Rules

### SMS & WhatsApp
- **to**: Required, string
- **message**: Required, string, max 1600 characters

### Messenger
- **to**: Required, string
- **message**: Required, string, max 2000 characters

### Telegram
- **to**: Required, string
- **message**: Required, string, max 4096 characters

---

## Rate Limits

Rate limits depend on your Twilio account tier:

- **Trial Account**: Limited messages per day
- **Paid Account**: Refer to Twilio documentation

For Telegram: Default rate limit is 30 messages per second.

---

## Authentication

Currently, the API endpoints do not require authentication. For production:

1. Add Laravel Sanctum for API tokens
2. Add middleware to routes
3. Require API key in headers

---

## Phone Number Format

All phone numbers must be in **E.164 format**:

**Format:** `+[country code][number]`

**Examples:**
- USA: `+14155551234`
- UK: `+442071234567`
- India: `+919876543210`

**Invalid formats:**
- Missing `+`: `14155551234`
- With spaces: `+1 415 555 1234`
- With dashes: `+1-415-555-1234`
- With parentheses: `+1 (415) 555-1234`

---

## HTML Formatting (Telegram Only)

Telegram supports HTML formatting:

```html
<b>bold</b>
<i>italic</i>
<u>underline</u>
<s>strikethrough</s>
<code>code</code>
<pre>preformatted</pre>
<a href="URL">link text</a>
```

**Example:**
```json
{
    "to": "123456789",
    "message": "Visit <a href='https://example.com'>our website</a> for <b>special offers</b>!"
}
```

---

## Response Fields

### SMS/WhatsApp/Messenger Success Response

| Field | Type | Description |
|-------|------|-------------|
| success | boolean | Always `true` on success |
| message | string | Human-readable success message |
| sid | string | Twilio Message SID (unique identifier) |
| status | string | Message status (queued, sending, sent, failed) |

### Telegram Success Response

| Field | Type | Description |
|-------|------|-------------|
| success | boolean | Always `true` on success |
| message | string | Human-readable success message |
| message_id | integer | Telegram Message ID |

### Error Response

| Field | Type | Description |
|-------|------|-------------|
| success | boolean | Always `false` on error |
| message | string | Error description |
| error_code | integer | Error code (if available) |
| errors | object | Validation errors (422 only) |

---

## Example Usage

### JavaScript (Fetch API)
```javascript
const sendSMS = async (to, message) => {
    const response = await fetch('http://localhost:8000/send-sms', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ to, message })
    });
    
    const data = await response.json();
    return data;
};

// Usage
sendSMS('+14155551234', 'Hello World!')
    .then(result => console.log(result))
    .catch(error => console.error(error));
```

### Python (Requests)
```python
import requests

def send_sms(to, message):
    url = 'http://localhost:8000/send-sms'
    headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    data = {
        'to': to,
        'message': message
    }
    
    response = requests.post(url, json=data, headers=headers)
    return response.json()

# Usage
result = send_sms('+14155551234', 'Hello World!')
print(result)
```

### PHP (Guzzle)
```php
use GuzzleHttp\Client;

$client = new Client();

$response = $client->post('http://localhost:8000/send-sms', [
    'json' => [
        'to' => '+14155551234',
        'message' => 'Hello World!'
    ],
    'headers' => [
        'Accept' => 'application/json'
    ]
]);

$result = json_decode($response->getBody(), true);
print_r($result);
```

---

## Best Practices

1. **Always validate phone numbers** before sending
2. **Handle errors gracefully** in your application
3. **Log all messaging activities** for audit purposes
4. **Implement rate limiting** to avoid API abuse
5. **Use environment variables** for credentials
6. **Test with sandbox numbers** before production
7. **Monitor delivery status** via webhooks (future enhancement)

---

## Support

For API issues:
1. Check the `storage/logs/laravel.log` file
2. Verify credentials in `.env` file
3. Test with Postman collection
4. Review Twilio/Telegram API documentation

---

**Last Updated:** 2025-10-26  
**Version:** 1.0.0  
**Laravel:** 10.x  
**PHP:** 8.1+
