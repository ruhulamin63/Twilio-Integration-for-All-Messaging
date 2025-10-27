# 📊 ARCHITECTURE & FLOW DIAGRAMS

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Web Browser │  │   Postman    │  │     cURL     │          │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘          │
│         │                  │                  │                  │
│         └──────────────────┴──────────────────┘                  │
│                            │                                     │
└────────────────────────────┼─────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                     LARAVEL APPLICATION                          │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                    routes/web.php                         │   │
│  │  GET  /messages                                          │   │
│  │  POST /send-sms                                          │   │
│  │  POST /send-whatsapp                                     │   │
│  │  POST /send-messenger                                    │   │
│  │  POST /send-telegram                                     │   │
│  └──────────────────┬───────────────────────────────────────┘   │
│                     │                                            │
│                     ▼                                            │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         app/Http/Controllers/MessageController.php       │   │
│  │                                                          │   │
│  │  - index()                                               │   │
│  │  - sendSms(Request $request)                            │   │
│  │  - sendWhatsapp(Request $request)                       │   │
│  │  - sendMessenger(Request $request)                      │   │
│  │  - sendTelegram(Request $request)                       │   │
│  │                                                          │   │
│  │  [Validates Input, Returns JSON Response]               │   │
│  └──────────────────┬───────────────────────────────────────┘   │
│                     │                                            │
│                     ▼                                            │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         app/Services/MessagingService.php                │   │
│  │                                                          │   │
│  │  - sendSms(to, message)                                 │   │
│  │  - sendWhatsApp(to, message)                            │   │
│  │  - sendMessenger(to, message)                           │   │
│  │  - sendTelegram(chatId, message)                        │   │
│  │                                                          │   │
│  │  [Business Logic, Error Handling, Logging]              │   │
│  └──────────┬───────────────────────┬───────────────────────┘   │
│             │                       │                            │
└─────────────┼───────────────────────┼────────────────────────────┘
              │                       │
              ▼                       ▼
┌─────────────────────────┐  ┌─────────────────────────┐
│     TWILIO API          │  │   TELEGRAM BOT API      │
│                         │  │                         │
│  - SMS Service          │  │  - sendMessage          │
│  - WhatsApp Service     │  │  - getUpdates           │
│  - Messenger Service    │  │                         │
└─────────────────────────┘  └─────────────────────────┘
```

---

## Request Flow Diagram

### SMS/WhatsApp/Messenger Flow

```
User Action
    │
    ▼
┌─────────────────┐
│  Web Form       │
│  Submit         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  JavaScript     │
│  AJAX Request   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  POST Request   │
│  to Laravel     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Route Handler  │
│  (web.php)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Controller     │
│  Method         │
│  - Validate     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Service Layer  │
│  - Format       │
│  - Call API     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Twilio SDK     │
│  API Call       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Twilio Server  │
│  Process        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Response       │
│  (Success/Fail) │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Service        │
│  Format Response│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Controller     │
│  JSON Response  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  JavaScript     │
│  Display Result │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  User sees      │
│  Success/Error  │
└─────────────────┘
```

---

## Data Flow

```
┌──────────────────────────────────────────────────────────┐
│                     INPUT DATA                            │
│  {                                                        │
│    "to": "+1234567890",                                  │
│    "message": "Hello World"                              │
│  }                                                        │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│                  VALIDATION LAYER                         │
│  - to: required, string                                  │
│  - message: required, string, max length                 │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│               BUSINESS LOGIC LAYER                        │
│  - Format phone number                                   │
│  - Add prefix (whatsapp:, messenger:)                   │
│  - Prepare API request                                   │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│                  EXTERNAL API                             │
│  Twilio/Telegram API Request                             │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│                 API RESPONSE                              │
│  Success: { sid, status }                                │
│  Error: { error_code, message }                          │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│              RESPONSE FORMATTING                          │
│  {                                                        │
│    "success": true/false,                                │
│    "message": "...",                                     │
│    "sid": "...",                                         │
│    "status": "..."                                       │
│  }                                                        │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│                 JSON RESPONSE                             │
│  HTTP 200 (Success) or 500 (Error)                       │
└────────────────────┬─────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────┐
│                  UI UPDATE                                │
│  Display success/error message to user                   │
└──────────────────────────────────────────────────────────┘
```

---

## Component Interaction

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │
       │ HTTP Request
       │ POST /send-sms
       │ { to, message }
       │
       ▼
┌─────────────────────────────┐
│    Laravel Router            │
│    (routes/web.php)         │
└──────┬──────────────────────┘
       │
       │ Route to Controller
       │
       ▼
┌─────────────────────────────┐
│  MessageController          │
│  - Validates Input          │◄───┐
│  - Calls Service            │    │
│  - Returns Response         │    │
└──────┬──────────────────────┘    │
       │                            │
       │ Delegate to Service        │
       │                            │
       ▼                            │
┌─────────────────────────────┐    │
│  MessagingService           │    │
│  - Initialize Twilio Client │    │
│  - Format Message           │    │
│  - Handle Errors            │    │
│  - Log Activity             │────┘
└──────┬──────────────────────┘
       │
       │ API Call
       │
       ▼
┌─────────────────────────────┐
│    External API             │
│    (Twilio/Telegram)        │
└──────┬──────────────────────┘
       │
       │ API Response
       │
       ▼
   [Return to Service]
```

---

## File Interaction Map

```
message.blade.php
    │
    │ (User submits form)
    │
    ▼
JavaScript (AJAX)
    │
    │ POST request
    │
    ▼
routes/web.php
    │
    │ Route to controller
    │
    ▼
MessageController.php
    │
    ├──> Validator (validate input)
    │
    ├──> MessagingService.php
    │        │
    │        ├──> Twilio Client (SMS, WhatsApp, Messenger)
    │        │
    │        └──> Guzzle HTTP (Telegram)
    │
    └──> JSON Response
         │
         ▼
    Browser displays result
```

---

## Error Handling Flow

```
┌─────────────┐
│   Request   │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│  Validation     │
│  Failed?        │
└────┬────────┬───┘
     │        │
    Yes      No
     │        │
     ▼        ▼
┌─────────┐  ┌──────────────┐
│ Return  │  │ Call Service │
│ 422     │  └──────┬───────┘
│ Error   │         │
└─────────┘         ▼
              ┌─────────────────┐
              │  API Call       │
              │  Exception?     │
              └────┬────────┬───┘
                   │        │
                  Yes      No
                   │        │
                   ▼        ▼
              ┌─────────┐  ┌─────────┐
              │ Catch   │  │ Return  │
              │ Log     │  │ Success │
              │ Return  │  │ 200     │
              │ 500     │  └─────────┘
              └─────────┘
```

---

## State Diagram (Form Submission)

```
┌─────────┐
│  Idle   │
└────┬────┘
     │
     │ User clicks Send
     │
     ▼
┌─────────────┐
│ Validating  │
└────┬────┬───┘
     │    │
  Valid Invalid
     │    │
     │    ▼
     │  ┌──────────┐
     │  │  Show    │
     │  │  Error   │
     │  │  Message │
     │  └─────┬────┘
     │        │
     │        ▼
     │  ┌─────────┐
     │  │  Idle   │
     │  └─────────┘
     │
     ▼
┌─────────────┐
│  Sending    │
│  (Loading)  │
└────┬────┬───┘
     │    │
 Success Error
     │    │
     ▼    ▼
┌─────────┐  ┌──────────┐
│  Show   │  │   Show   │
│ Success │  │  Error   │
│ Message │  │  Message │
└────┬────┘  └─────┬────┘
     │             │
     ▼             ▼
┌─────────┐  ┌─────────┐
│  Reset  │  │  Stay   │
│  Form   │  │  Filled │
└────┬────┘  └─────┬───┘
     │             │
     ▼             ▼
┌─────────┐  ┌─────────┐
│  Idle   │  │  Idle   │
└─────────┘  └─────────┘
```

---

## Deployment Flow

```
┌─────────────────┐
│  1. Clone Repo  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ 2. Run Setup    │
│    composer     │
│    install      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ 3. Configure    │
│    .env file    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ 4. Generate     │
│    App Key      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ 5. Start Server │
│    php artisan  │
│    serve        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ 6. Test in      │
│    Browser      │
└─────────────────┘
```

---

## Platform Comparison

```
Feature         SMS    WhatsApp  Messenger  Telegram
─────────────────────────────────────────────────────
Max Length      1600   1600      2000       4096
Media Support   ✓      ✓         ✓          ✓
Rich Format     ✗      Limited   ✓          ✓ (HTML)
API Provider    Twilio Twilio    Twilio     Direct
Cost per Msg    $$     $$        $$         Free
Delivery Speed  Fast   Fast      Fast       Instant
Read Receipts   ✗      ✓         ✓          ✓
Group Support   ✗      ✓         ✓          ✓
```

---

## Technology Stack

```
┌─────────────────────────────────────┐
│         Frontend Layer              │
│  - Bootstrap 5                      │
│  - JavaScript (Vanilla)             │
│  - Font Awesome Icons               │
│  - Blade Templates                  │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│       Application Layer             │
│  - Laravel 10 Framework             │
│  - PHP 8.1                          │
│  - MVC Architecture                 │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│        Service Layer                │
│  - MessagingService                 │
│  - Error Handling                   │
│  - Logging                          │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│       Integration Layer             │
│  - Twilio SDK (SMS/WhatsApp/MSG)    │
│  - Guzzle HTTP (Telegram)           │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│        External APIs                │
│  - Twilio REST API                  │
│  - Telegram Bot API                 │
└─────────────────────────────────────┘
```

---

**Diagrams Version:** 1.0  
**Last Updated:** 2025-10-26
