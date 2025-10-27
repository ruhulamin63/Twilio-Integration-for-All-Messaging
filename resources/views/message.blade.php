<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messaging Integration - Twilio API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
            padding: 20px;
        }
        .messaging-card {
            transition: transform 0.3s ease;
        }
        .messaging-card:hover {
            transform: translateY(-5px);
        }
        .btn-send {
            padding: 10px 30px;
            font-weight: bold;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-send:hover {
            transform: scale(1.05);
        }
        .response-box {
            margin-top: 15px;
            padding: 15px;
            border-radius: 10px;
            display: none;
        }
        .response-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .response-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .icon-box {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .badge-info {
            font-size: 0.75rem;
            padding: 5px 10px;
        }
        .form-label {
            font-weight: 600;
        }
        .spinner-border-sm {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="text-white fw-bold mb-3">
                <i class="fas fa-comments"></i> Messaging Integration
            </h1>
            <p class="text-white">Send messages via SMS, WhatsApp, Messenger & Telegram using Twilio API</p>
        </div>

        <div class="row">
            <!-- SMS Card -->
            <div class="col-md-6 mb-4">
                <div class="card messaging-card">
                    <div class="card-header bg-primary text-white">
                        <div class="icon-box text-center">
                            <i class="fas fa-sms"></i>
                        </div>
                        <h4 class="text-center mb-0">Send SMS</h4>
                        <p class="text-center mb-0 small">Traditional text messaging</p>
                    </div>
                    <div class="card-body">
                        <form id="smsForm">
                            <div class="mb-3">
                                <label for="sms-to" class="form-label">
                                    Phone Number <span class="badge bg-info">+1234567890</span>
                                </label>
                                <input type="text" class="form-control" id="sms-to" name="to" 
                                       placeholder="+1234567890" required>
                            </div>
                            <div class="mb-3">
                                <label for="sms-message" class="form-label">Message</label>
                                <textarea class="form-control" id="sms-message" name="message" 
                                          rows="4" placeholder="Enter your SMS message here..." 
                                          maxlength="1600" required></textarea>
                                <small class="text-muted">Max 1600 characters</small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-send w-100">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <i class="fas fa-paper-plane"></i> Send SMS
                            </button>
                            <div class="response-box" id="sms-response"></div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Card -->
            <div class="col-md-6 mb-4">
                <div class="card messaging-card">
                    <div class="card-header bg-success text-white">
                        <div class="icon-box text-center">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h4 class="text-center mb-0">Send WhatsApp</h4>
                        <p class="text-center mb-0 small">WhatsApp Business messaging</p>
                    </div>
                    <div class="card-body">
                        <form id="whatsappForm">
                            <div class="mb-3">
                                <label for="whatsapp-to" class="form-label">
                                    Phone Number <span class="badge bg-info">+1234567890</span>
                                </label>
                                <input type="text" class="form-control" id="whatsapp-to" name="to" 
                                       placeholder="+1234567890" required>
                                <small class="text-muted">Format: +[country code][number]</small>
                            </div>
                            <div class="mb-3">
                                <label for="whatsapp-message" class="form-label">Message</label>
                                <textarea class="form-control" id="whatsapp-message" name="message" 
                                          rows="4" placeholder="Enter your WhatsApp message here..." 
                                          maxlength="1600" required></textarea>
                                <small class="text-muted">Max 1600 characters</small>
                            </div>
                            <button type="submit" class="btn btn-success btn-send w-100">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <i class="fas fa-paper-plane"></i> Send WhatsApp
                            </button>
                            <div class="response-box" id="whatsapp-response"></div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Messenger Card -->
            <div class="col-md-6 mb-4">
                <div class="card messaging-card">
                    <div class="card-header bg-info text-white">
                        <div class="icon-box text-center">
                            <i class="fab fa-facebook-messenger"></i>
                        </div>
                        <h4 class="text-center mb-0">Send Messenger</h4>
                        <p class="text-center mb-0 small">Facebook Messenger integration</p>
                    </div>
                    <div class="card-body">
                        <form id="messengerForm">
                            <div class="mb-3">
                                <label for="messenger-to" class="form-label">
                                    Recipient ID <span class="badge bg-info">Facebook User ID</span>
                                </label>
                                <input type="text" class="form-control" id="messenger-to" name="to" 
                                       placeholder="Facebook User/Page ID" required>
                                <small class="text-muted">Requires Twilio Messenger setup</small>
                            </div>
                            <div class="mb-3">
                                <label for="messenger-message" class="form-label">Message</label>
                                <textarea class="form-control" id="messenger-message" name="message" 
                                          rows="4" placeholder="Enter your Messenger message here..." 
                                          maxlength="2000" required></textarea>
                                <small class="text-muted">Max 2000 characters</small>
                            </div>
                            <button type="submit" class="btn btn-info btn-send w-100">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <i class="fas fa-paper-plane"></i> Send Messenger
                            </button>
                            <div class="response-box" id="messenger-response"></div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Telegram Card -->
            <div class="col-md-6 mb-4">
                <div class="card messaging-card">
                    <div class="card-header bg-warning text-dark">
                        <div class="icon-box text-center">
                            <i class="fab fa-telegram"></i>
                        </div>
                        <h4 class="text-center mb-0">Send Telegram</h4>
                        <p class="text-center mb-0 small">Telegram Bot messaging</p>
                    </div>
                    <div class="card-body">
                        <form id="telegramForm">
                            <div class="mb-3">
                                <label for="telegram-to" class="form-label">
                                    Chat ID <span class="badge bg-info">Telegram Chat ID</span>
                                </label>
                                <input type="text" class="form-control" id="telegram-to" name="to" 
                                       placeholder="123456789 or @username" required>
                                <small class="text-muted">User Chat ID or Channel username</small>
                            </div>
                            <div class="mb-3">
                                <label for="telegram-message" class="form-label">Message</label>
                                <textarea class="form-control" id="telegram-message" name="message" 
                                          rows="4" placeholder="Enter your Telegram message here..." 
                                          maxlength="4096" required></textarea>
                                <small class="text-muted">Max 4096 characters, HTML supported</small>
                            </div>
                            <button type="submit" class="btn btn-warning btn-send w-100">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                <i class="fas fa-paper-plane"></i> Send Telegram
                            </button>
                            <div class="response-box" id="telegram-response"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Card -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Setup Instructions</h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Configuration Required:</h6>
                <ol>
                    <li><strong>SMS & WhatsApp:</strong> Configure Twilio credentials in <code>.env</code> file</li>
                    <li><strong>Telegram:</strong> Create a bot via @BotFather and add token to <code>.env</code></li>
                    <li><strong>Messenger:</strong> Set up Facebook Messenger integration with Twilio</li>
                </ol>
                <h6 class="fw-bold mt-3">Testing Tips:</h6>
                <ul>
                    <li>For WhatsApp: Use Twilio Sandbox number or approved template</li>
                    <li>For Telegram: Start a chat with your bot first</li>
                    <li>For SMS: Ensure phone number is in E.164 format (+country code)</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // CSRF Token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Helper function to send messages
        async function sendMessage(formId, endpoint, responseBoxId) {
            const form = document.getElementById(formId);
            const responseBox = document.getElementById(responseBoxId);
            const submitBtn = form.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Show loading state
            submitBtn.disabled = true;
            spinner.style.display = 'inline-block';
            responseBox.style.display = 'none';

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                // Display response
                responseBox.style.display = 'block';
                if (result.success) {
                    responseBox.className = 'response-box response-success';
                    responseBox.innerHTML = `
                        <strong><i class="fas fa-check-circle"></i> Success!</strong><br>
                        ${result.message}
                        ${result.sid ? `<br><small>Message SID: ${result.sid}</small>` : ''}
                        ${result.message_id ? `<br><small>Message ID: ${result.message_id}</small>` : ''}
                    `;
                    form.reset();
                } else {
                    responseBox.className = 'response-box response-error';
                    responseBox.innerHTML = `
                        <strong><i class="fas fa-exclamation-circle"></i> Error!</strong><br>
                        ${result.message}
                        ${result.errors ? '<br><small>' + JSON.stringify(result.errors) + '</small>' : ''}
                    `;
                }
            } catch (error) {
                responseBox.style.display = 'block';
                responseBox.className = 'response-box response-error';
                responseBox.innerHTML = `
                    <strong><i class="fas fa-exclamation-circle"></i> Error!</strong><br>
                    Network error: ${error.message}
                `;
            } finally {
                submitBtn.disabled = false;
                spinner.style.display = 'none';
            }
        }

        // SMS Form Handler
        document.getElementById('smsForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage('smsForm', '/send-sms', 'sms-response');
        });

        // WhatsApp Form Handler
        document.getElementById('whatsappForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage('whatsappForm', '/send-whatsapp', 'whatsapp-response');
        });

        // Messenger Form Handler
        document.getElementById('messengerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage('messengerForm', '/send-messenger', 'messenger-response');
        });

        // Telegram Form Handler
        document.getElementById('telegramForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage('telegramForm', '/send-telegram', 'telegram-response');
        });
    </script>
</body>
</html>
