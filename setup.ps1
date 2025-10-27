# Quick Setup Script for Windows PowerShell
# Run this file to set up the Laravel project

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "CRM Messaging Integration Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Composer is installed
Write-Host "Checking for Composer..." -ForegroundColor Yellow
if (Get-Command composer -ErrorAction SilentlyContinue) {
    Write-Host "✓ Composer found" -ForegroundColor Green
} else {
    Write-Host "✗ Composer not found! Please install Composer first." -ForegroundColor Red
    Write-Host "Download from: https://getcomposer.org/download/" -ForegroundColor Yellow
    exit
}

# Check if PHP is installed
Write-Host "Checking for PHP..." -ForegroundColor Yellow
if (Get-Command php -ErrorAction SilentlyContinue) {
    $phpVersion = php -v
    Write-Host "✓ PHP found" -ForegroundColor Green
    Write-Host $phpVersion.Split("`n")[0] -ForegroundColor Gray
} else {
    Write-Host "✗ PHP not found! Please install PHP 8.1 or higher." -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Installing Dependencies..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Install Composer dependencies
composer install

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Dependencies installed successfully" -ForegroundColor Green
} else {
    Write-Host "✗ Failed to install dependencies" -ForegroundColor Red
    exit
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setting up Environment..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Copy .env.example to .env if it doesn't exist
if (-Not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "✓ .env file created" -ForegroundColor Green
} else {
    Write-Host "⚠ .env file already exists, skipping..." -ForegroundColor Yellow
}

# Generate application key
Write-Host "Generating application key..." -ForegroundColor Yellow
php artisan key:generate

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Application key generated" -ForegroundColor Green
} else {
    Write-Host "✗ Failed to generate application key" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Edit .env file and add your credentials:" -ForegroundColor White
Write-Host "   - TWILIO_SID" -ForegroundColor Gray
Write-Host "   - TWILIO_AUTH_TOKEN" -ForegroundColor Gray
Write-Host "   - TWILIO_WHATSAPP_FROM" -ForegroundColor Gray
Write-Host "   - TWILIO_SMS_FROM" -ForegroundColor Gray
Write-Host "   - TELEGRAM_BOT_TOKEN" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Start the development server:" -ForegroundColor White
Write-Host "   php artisan serve" -ForegroundColor Cyan
Write-Host ""
Write-Host "3. Open your browser:" -ForegroundColor White
Write-Host "   http://localhost:8000/messages" -ForegroundColor Cyan
Write-Host ""
Write-Host "For detailed instructions, see README.md" -ForegroundColor Yellow
Write-Host ""
