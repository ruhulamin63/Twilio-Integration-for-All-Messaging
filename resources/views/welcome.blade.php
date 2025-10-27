<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Messaging Integration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            text-align: center;
        }
        .logo {
            font-size: 5rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-primary {
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: transform 0.3s ease;
        }
        .btn-primary:hover {
            transform: scale(1.05);
        }
        .features {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
        }
        .feature {
            text-align: center;
        }
        .feature i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="welcome-card">
        <div class="logo">
            <i class="fas fa-comments"></i>
        </div>
        <h1 class="mb-3">CRM Messaging Integration</h1>
        <p class="lead text-muted mb-4">
            Multi-platform messaging solution powered by Twilio API
        </p>
        
        <div class="features mb-4">
            <div class="feature">
                <i class="fas fa-sms"></i>
                <div class="small">SMS</div>
            </div>
            <div class="feature">
                <i class="fab fa-whatsapp"></i>
                <div class="small">WhatsApp</div>
            </div>
            <div class="feature">
                <i class="fab fa-facebook-messenger"></i>
                <div class="small">Messenger</div>
            </div>
            <div class="feature">
                <i class="fab fa-telegram"></i>
                <div class="small">Telegram</div>
            </div>
        </div>

        <a href="/messages" class="btn btn-primary">
            <i class="fas fa-rocket"></i> Launch Messaging Center
        </a>

        <div class="mt-4">
            <small class="text-muted">
                Laravel 10 + PHP 8.1 + Twilio SDK
            </small>
        </div>
    </div>
</body>
</html>
