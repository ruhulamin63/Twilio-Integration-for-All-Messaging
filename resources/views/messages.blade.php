<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messaging Center - Full History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .card-header {
            background: white;
            border-bottom: 2px solid #f0f0f0;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .message-card {
            transition: transform 0.2s ease;
        }
        .message-card:hover {
            transform: translateY(-2px);
        }
        .btn-send {
            padding: 12px 35px;
            font-weight: 600;
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
        .message-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            border-left: 4px solid #ddd;
        }
        .message-item.outbound {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }
        .message-item.inbound {
            background: #f3e5f5;
            border-left-color: #9c27b0;
        }
        .message-item.failed {
            background: #ffebee;
            border-left-color: #f44336;
        }
        .badge-platform {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 12px;
        }
        .badge-sms { background: #4caf50; }
        .badge-whatsapp { background: #25d366; }
        .badge-messenger { background: #0084ff; }
        .badge-telegram { background: #0088cc; }
        .message-meta {
            font-size: 0.85rem;
            color: #666;
        }
        .tab-pane {
            min-height: 400px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .spinner-border-sm {
            display: none;
        }
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stats-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stats-card .label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <h1 class="mb-2"><i class="fas fa-comments"></i> Messaging Center</h1>
            <p class="mb-0">Send and track messages across SMS, WhatsApp, Messenger & Telegram</p>
        </div>
    </div>

    <div class="container">
        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4" id="messagingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="send-tab" data-bs-toggle="pill" data-bs-target="#send" type="button">
                    <i class="fas fa-paper-plane"></i> Send Messages
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="pill" data-bs-target="#history" type="button">
                    <i class="fas fa-history"></i> Message History
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="stats-tab" data-bs-toggle="pill" data-bs-target="#stats" type="button">
                    <i class="fas fa-chart-bar"></i> Statistics
                </button>
            </li>
        </ul>

        <div class="tab-content" id="messagingTabContent">
            <!-- Send Messages Tab -->
            <div class="tab-pane fade show active" id="send" role="tabpanel">
                <div class="row">
                    <!-- SMS Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card message-card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-sms"></i> Send SMS</h5>
                            </div>
                            <div class="card-body">
                                <form id="smsForm">
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" name="to" placeholder="+1234567890" required>
                                        <small class="text-muted">E.164 format</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-control" name="message" rows="4" maxlength="1600" required></textarea>
                                        <small class="text-muted">Max 1600 characters</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-send w-100">
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
                        <div class="card message-card">
                            <div class="card-header text-white" style="background: #25d366;">
                                <h5 class="mb-0"><i class="fab fa-whatsapp"></i> Send WhatsApp</h5>
                            </div>
                            <div class="card-body">
                                <form id="whatsappForm">
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" name="to" placeholder="+1234567890" required>
                                        <small class="text-muted">Number must join sandbox first</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-control" name="message" rows="4" maxlength="1600" required></textarea>
                                        <small class="text-muted">Max 1600 characters</small>
                                    </div>
                                    <button type="submit" class="btn text-white btn-send w-100" style="background: #25d366;">
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
                        <div class="card message-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fab fa-facebook-messenger"></i> Send Messenger</h5>
                            </div>
                            <div class="card-body">
                                <form id="messengerForm">
                                    <div class="mb-3">
                                        <label class="form-label">Recipient ID</label>
                                        <input type="text" class="form-control" name="to" placeholder="Facebook User ID" required>
                                        <small class="text-muted">Requires Messenger setup</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-control" name="message" rows="4" maxlength="2000" required></textarea>
                                        <small class="text-muted">Max 2000 characters</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-send w-100">
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
                        <div class="card message-card">
                            <div class="card-header text-white" style="background: #0088cc;">
                                <h5 class="mb-0"><i class="fab fa-telegram"></i> Send Telegram</h5>
                            </div>
                            <div class="card-body">
                                <form id="telegramForm">
                                    <div class="mb-3">
                                        <label class="form-label">Chat ID</label>
                                        <input type="text" class="form-control" name="to" placeholder="123456789" required>
                                        <small class="text-muted">Telegram chat ID or @username</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Message</label>
                                        <textarea class="form-control" name="message" rows="4" maxlength="4096" required></textarea>
                                        <small class="text-muted">Max 4096 chars, HTML supported</small>
                                    </div>
                                    <button type="submit" class="btn text-white btn-send w-100" style="background: #0088cc;">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        <i class="fas fa-paper-plane"></i> Send Telegram
                                    </button>
                                    <div class="response-box" id="telegram-response"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message History Tab -->
            <div class="tab-pane fade" id="history" role="tabpanel">
                <!-- Filters -->
                <div class="filter-bar">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Platform</label>
                            <select class="form-select" id="filterPlatform">
                                <option value="">All Platforms</option>
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="messenger">Messenger</option>
                                <option value="telegram">Telegram</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Direction</label>
                            <select class="form-select" id="filterDirection">
                                <option value="">All Messages</option>
                                <option value="outbound">Outbound (Sent)</option>
                                <option value="inbound">Inbound (Received)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Limit</label>
                            <select class="form-select" id="filterLimit">
                                <option value="50">50 messages</option>
                                <option value="100">100 messages</option>
                                <option value="200">200 messages</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="loadMessages()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Message History</h5>
                    </div>
                    <div class="card-body">
                        <div id="messagesContainer">
                            <div class="empty-state">
                                <i class="fas fa-inbox fa-4x mb-3"></i>
                                <p>Loading messages...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Tab -->
            <div class="tab-pane fade" id="stats" role="tabpanel">
                <div class="row" id="statsContainer">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="number" id="statTotal">-</div>
                            <div class="label">Total Messages</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="number text-success" id="statOutbound">-</div>
                            <div class="label">Sent</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="number text-primary" id="statInbound">-</div>
                            <div class="label">Received</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="number text-danger" id="statFailed">-</div>
                            <div class="label">Failed</div>
                        </div>
                    </div>
                </div>

                <!-- Platform Breakdown -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Messages by Platform</h5>
                    </div>
                    <div class="card-body">
                        <div id="platformStats"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Send message function
        async function sendMessage(formId, endpoint, responseBoxId) {
            const form = document.getElementById(formId);
            const responseBox = document.getElementById(responseBoxId);
            const submitBtn = form.querySelector('button[type="submit"]');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

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
                    
                    // Refresh history if on that tab
                    if (document.getElementById('history-tab').classList.contains('active')) {
                        setTimeout(loadMessages, 1000);
                    }
                } else {
                    responseBox.className = 'response-box response-error';
                    responseBox.innerHTML = `
                        <strong><i class="fas fa-exclamation-circle"></i> Error!</strong><br>
                        ${result.message}
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

        // Load messages function
        async function loadMessages() {
            const platform = document.getElementById('filterPlatform').value;
            const direction = document.getElementById('filterDirection').value;
            const limit = document.getElementById('filterLimit').value;
            
            const params = new URLSearchParams();
            if (platform) params.append('platform', platform);
            if (direction) params.append('direction', direction);
            if (limit) params.append('limit', limit);

            try {
                const response = await fetch(`/api/messages?${params.toString()}`);
                const data = await response.json();

                const container = document.getElementById('messagesContainer');
                
                if (data.messages && data.messages.length > 0) {
                    container.innerHTML = data.messages.map(msg => `
                        <div class="message-item ${msg.direction} ${msg.status === 'failed' ? 'failed' : ''}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge badge-platform badge-${msg.platform}">${msg.platform.toUpperCase()}</span>
                                    <span class="badge ${msg.direction === 'outbound' ? 'bg-info' : 'bg-warning'} text-dark">${msg.direction.toUpperCase()}</span>
                                    <span class="badge ${msg.status === 'failed' ? 'bg-danger' : 'bg-success'}">${msg.status.toUpperCase()}</span>
                                </div>
                                <small class="text-muted">${new Date(msg.created_at).toLocaleString()}</small>
                            </div>
                            <div class="message-meta mb-2">
                                <strong>From:</strong> ${msg.from_number || 'N/A'} → <strong>To:</strong> ${msg.to_number || 'N/A'}
                            </div>
                            <div class="message-body">
                                ${msg.message_body}
                            </div>
                            ${msg.message_sid ? `<small class="text-muted d-block mt-2">SID: ${msg.message_sid}</small>` : ''}
                            ${msg.error_message ? `<div class="alert alert-danger mt-2 mb-0 py-1"><small>${msg.error_message}</small></div>` : ''}
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-inbox fa-4x mb-3"></i>
                            <p>No messages found</p>
                        </div>
                    `;
                }

                updateStats(data.messages);
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // Update statistics
        function updateStats(messages) {
            const total = messages.length;
            const outbound = messages.filter(m => m.direction === 'outbound').length;
            const inbound = messages.filter(m => m.direction === 'inbound').length;
            const failed = messages.filter(m => m.status === 'failed').length;

            document.getElementById('statTotal').textContent = total;
            document.getElementById('statOutbound').textContent = outbound;
            document.getElementById('statInbound').textContent = inbound;
            document.getElementById('statFailed').textContent = failed;

            // Platform breakdown
            const platforms = {};
            messages.forEach(m => {
                platforms[m.platform] = (platforms[m.platform] || 0) + 1;
            });

            const platformStatsHtml = Object.entries(platforms).map(([platform, count]) => `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><span class="badge badge-platform badge-${platform}">${platform.toUpperCase()}</span></span>
                    <strong>${count} messages</strong>
                </div>
            `).join('');

            document.getElementById('platformStats').innerHTML = platformStatsHtml || '<p class="text-muted">No data available</p>';
        }

        // Form handlers
        document.getElementById('smsForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage('smsForm', '/send-sms', 'sms-response');
        });

        document.getElementById('whatsappForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage('whatsappForm', '/send-whatsapp', 'whatsapp-response');
        });

        document.getElementById('messengerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage('messengerForm', '/send-messenger', 'messenger-response');
        });

        document.getElementById('telegramForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage('telegramForm', '/send-telegram', 'telegram-response');
        });

        // Load messages when history tab is shown
        document.getElementById('history-tab').addEventListener('shown.bs.tab', function () {
            loadMessages();
        });

        // Load stats when stats tab is shown
        document.getElementById('stats-tab').addEventListener('shown.bs.tab', function () {
            loadMessages();
        });

        // Auto-refresh messages every 30 seconds if on history tab
        setInterval(() => {
            if (document.getElementById('history-tab').classList.contains('active')) {
                loadMessages();
            }
        }, 30000);
    </script>
</body>
</html>
