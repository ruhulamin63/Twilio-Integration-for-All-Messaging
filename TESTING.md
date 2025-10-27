# Twilio CRM Messaging Integration - Testing Guide

## Quick Start Testing

### 1. Using the Web Interface

1. Start the server:
   ```bash
   php artisan serve
   ```

2. Open browser: `http://localhost:8000/messages`

3. Fill in the form for your desired platform:
   - **Phone Number/Chat ID**: Recipient's contact info
   - **Message**: Your message content
   - Click the corresponding "Send" button

4. Check the response displayed below the form

### 2. Testing with Postman

#### Setup Postman Collection

**SMS Test Request:**
- Method: `POST`
- URL: `http://localhost:8000/send-sms`
- Headers:
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- Body (JSON):
  ```json
  {
      "to": "+14155551234",
      "message": "Hello from Postman!"
  }
  ```

**WhatsApp Test Request:**
- Method: `POST`
- URL: `http://localhost:8000/send-whatsapp`
- Headers:
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- Body (JSON):
  ```json
  {
      "to": "+14155551234",
      "message": "Hello from WhatsApp via Postman!"
  }
  ```

**Telegram Test Request:**
- Method: `POST`
- URL: `http://localhost:8000/send-telegram`
- Headers:
  ```
  Content-Type: application/json
  Accept: application/json
  ```
- Body (JSON):
  ```json
  {
      "to": "123456789",
      "message": "Hello from <b>Telegram</b> via Postman!"
  }
  ```

### 3. Testing with cURL (Windows PowerShell)

#### SMS Test:
```powershell
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

$body = @{
    to = "+14155551234"
    message = "Test SMS from PowerShell"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/send-sms" -Method Post -Headers $headers -Body $body
```

#### WhatsApp Test:
```powershell
$body = @{
    to = "+14155551234"
    message = "Test WhatsApp from PowerShell"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/send-whatsapp" -Method Post -Headers $headers -Body $body
```

#### Telegram Test:
```powershell
$body = @{
    to = "123456789"
    message = "Test Telegram from PowerShell"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/send-telegram" -Method Post -Headers $headers -Body $body
```

### 4. Testing with cURL (Git Bash/Linux Style)

#### SMS:
```bash
curl -X POST http://localhost:8000/send-sms \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"to":"+14155551234","message":"Test SMS"}'
```

#### WhatsApp:
```bash
curl -X POST http://localhost:8000/send-whatsapp \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"to":"+14155551234","message":"Test WhatsApp"}'
```

#### Telegram:
```bash
curl -X POST http://localhost:8000/send-telegram \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"to":"123456789","message":"Test Telegram"}'
```

## Response Examples

### Success Response:
```json
{
    "success": true,
    "message": "SMS sent successfully",
    "sid": "SM1234567890abcdef1234567890abcdef",
    "status": "queued"
}
```

### Validation Error Response:
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

### API Error Response:
```json
{
    "success": false,
    "message": "Failed to send SMS: The 'To' number is not a valid phone number.",
    "error_code": 21211
}
```

## Testing Checklist

### Before Testing:
- [ ] `.env` file configured with valid credentials
- [ ] Composer dependencies installed (`composer install`)
- [ ] Laravel server running (`php artisan serve`)
- [ ] For WhatsApp: Joined Twilio sandbox
- [ ] For Telegram: Started chat with bot

### SMS Testing:
- [ ] Test with valid phone number (E.164 format)
- [ ] Test with invalid phone number
- [ ] Test with empty message
- [ ] Test with message > 1600 characters
- [ ] Verify message received on phone

### WhatsApp Testing:
- [ ] Test with sandbox number
- [ ] Test with valid phone number
- [ ] Test with message containing emojis
- [ ] Verify message received on WhatsApp

### Telegram Testing:
- [ ] Get chat ID from bot
- [ ] Test with valid chat ID
- [ ] Test with HTML formatting
- [ ] Verify message received on Telegram

### Messenger Testing:
- [ ] Set up Messenger integration
- [ ] Test with valid Facebook user ID
- [ ] Verify message received

## Common Test Scenarios

### 1. Test Phone Number Formats

Valid formats:
- `+14155551234` (USA)
- `+442071234567` (UK)
- `+918912345678` (India)

Invalid formats:
- `4155551234` (missing +)
- `001-415-555-1234` (has dashes)
- `(415) 555-1234` (has parentheses)

### 2. Test Message Content

- Plain text: "Hello World"
- With emojis: "Hello 👋 World 🌍"
- With special characters: "Price: $50 & up"
- With newlines: "Line 1\nLine 2"
- HTML (Telegram): "<b>Bold</b> <i>Italic</i>"

### 3. Test Edge Cases

- Empty message
- Very long message (test character limits)
- Special characters in phone number
- Invalid credentials
- Network timeout simulation

## Debugging Tips

### Check Laravel Logs:
```bash
type storage\logs\laravel.log
```

### Enable Debug Mode:
In `.env`:
```
APP_DEBUG=true
```

### Test Twilio Connection:
```php
// Create a test route in routes/web.php
Route::get('/test-twilio', function() {
    $client = new \Twilio\Rest\Client(
        config('services.twilio.sid'),
        config('services.twilio.auth_token')
    );
    
    try {
        $account = $client->api->v2010->accounts(config('services.twilio.sid'))->fetch();
        return "Twilio connected! Status: " . $account->status;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
```

### Test Telegram Connection:
```php
Route::get('/test-telegram', function() {
    $client = new \GuzzleHttp\Client();
    $botToken = config('services.telegram.bot_token');
    
    try {
        $response = $client->get("https://api.telegram.org/bot{$botToken}/getMe");
        $data = json_decode($response->getBody(), true);
        return $data;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
```

## Performance Testing

### Load Testing with Apache Bench:
```bash
ab -n 100 -c 10 -p sms_data.json -T application/json http://localhost:8000/send-sms
```

Where `sms_data.json` contains:
```json
{"to":"+14155551234","message":"Load test message"}
```

## Expected Results

### Successful SMS:
- HTTP Status: 200
- Response contains `success: true`
- Twilio SID returned
- Message received on phone within seconds

### Successful WhatsApp:
- HTTP Status: 200
- Response contains `success: true`
- Message received on WhatsApp within seconds
- Green checkmark appears when delivered

### Successful Telegram:
- HTTP Status: 200
- Response contains `success: true`
- Message ID returned
- Message appears in Telegram chat immediately

---

**Happy Testing! 🧪✅**
