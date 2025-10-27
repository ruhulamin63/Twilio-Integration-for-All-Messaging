# 🚀 QUICK START GUIDE

## Get Up and Running in 5 Minutes!

This is the fastest way to get the CRM Messaging Integration running on your machine.

---

## ⚡ Super Quick Setup (3 Commands!)

```powershell
# 1. Install dependencies
composer install

# 2. Setup environment
copy .env.example .env
php artisan key:generate

# 3. Start server
php artisan serve
```

Then open: **http://localhost:8000/messages**

---

## 🔑 Before You Can Send Messages

You need to add credentials to the `.env` file:

### For SMS & WhatsApp (Twilio)

1. Sign up at [twilio.com/try-twilio](https://www.twilio.com/try-twilio)
2. Get your Account SID and Auth Token from the dashboard
3. Add to `.env`:

```env
TWILIO_SID=ACxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_SMS_FROM=+1234567890
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### For Telegram

1. Open Telegram, search for `@BotFather`
2. Send `/newbot` and follow instructions
3. Copy the bot token
4. Add to `.env`:

```env
TELEGRAM_BOT_TOKEN=123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
```

---

## 📱 What You Can Do

✅ **Send SMS** - Traditional text messages  
✅ **Send WhatsApp** - WhatsApp Business messages  
✅ **Send Messenger** - Facebook Messenger (requires setup)  
✅ **Send Telegram** - Telegram bot messages  

---

## 🧪 Quick Test

### Using the Web Interface

1. Go to `http://localhost:8000/messages`
2. Choose SMS, WhatsApp, or Telegram
3. Enter phone number/chat ID
4. Type your message
5. Click Send!

### Using Postman

1. Import `postman_collection.json`
2. Choose an endpoint (Send SMS, WhatsApp, etc.)
3. Click Send

### Using cURL (PowerShell)

```powershell
$body = @{
    to = "+14155551234"
    message = "Hello from CRM!"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/send-sms" `
  -Method Post `
  -Headers @{"Content-Type"="application/json"} `
  -Body $body
```

---

## 📖 Need More Help?

- **Full Setup Guide**: [README.md](README.md)
- **API Documentation**: [API_DOCS.md](API_DOCS.md)
- **Testing Guide**: [TESTING.md](TESTING.md)
- **All Documentation**: [INDEX.md](INDEX.md)

---

## 🆘 Common Issues

### "Composer not found"
**Solution:** Install Composer from [getcomposer.org](https://getcomposer.org)

### "Class 'Twilio\Rest\Client' not found"
**Solution:** Run `composer install`

### "Authenticate error from Twilio"
**Solution:** Check your `TWILIO_SID` and `TWILIO_AUTH_TOKEN` in `.env`

### "Chat not found" (Telegram)
**Solution:** Start a conversation with your bot first

---

## ✨ That's It!

You now have a working multi-platform messaging system!

**Time to first message:** ~5 minutes  
**Platforms supported:** 4 (SMS, WhatsApp, Messenger, Telegram)  
**Lines of code you wrote:** 0  

🎉 **Enjoy!** 🎉

---

**For detailed documentation, see [INDEX.md](INDEX.md)**
