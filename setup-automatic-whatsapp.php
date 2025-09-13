<?php

/**
 * Automatic WhatsApp Setup Helper
 * 
 * This script helps you configure automatic WhatsApp sending for invoices.
 * Run: php setup-automatic-whatsapp.php
 */

echo "🚀 Automatic WhatsApp Setup for Invoice Sending\n";
echo "===============================================\n\n";

echo "Choose your WhatsApp provider:\n";
echo "1. Twilio WhatsApp API (Recommended - Easy setup)\n";
echo "2. Meta WhatsApp Business API (Advanced - Requires business verification)\n";
echo "3. Web-based (Manual sending via WhatsApp Web)\n\n";

$choice = readline("Enter your choice (1-3): ");

switch ($choice) {
    case '1':
        setupTwilio();
        break;
    case '2':
        setupMeta();
        break;
    case '3':
        setupWeb();
        break;
    default:
        echo "Invalid choice. Exiting.\n";
        exit(1);
}

function setupTwilio() {
    echo "\n📱 Setting up Twilio WhatsApp API\n";
    echo "=================================\n\n";
    
    echo "1. Sign up at: https://www.twilio.com/\n";
    echo "2. Get your Account SID and Auth Token from Twilio Console\n";
    echo "3. Enable WhatsApp in your Twilio account\n\n";
    
    $sid = readline("Enter your Twilio Account SID: ");
    $token = readline("Enter your Twilio Auth Token: ");
    $from = readline("Enter your Twilio WhatsApp number (or press Enter for sandbox): ");
    
    if (empty($from)) {
        $from = 'whatsapp:+14155238886'; // Twilio sandbox number
    }
    
    updateEnvFile([
        'WHATSAPP_PROVIDER' => 'twilio',
        'TWILIO_SID' => $sid,
        'TWILIO_TOKEN' => $token,
        'TWILIO_WHATSAPP_FROM' => $from
    ]);
    
    echo "\n✅ Twilio configuration added to .env file!\n";
    echo "🔄 Please restart your application to apply changes.\n";
    echo "📱 Test by sending an invoice via WhatsApp.\n\n";
}

function setupMeta() {
    echo "\n🏢 Setting up Meta WhatsApp Business API\n";
    echo "=======================================\n\n";
    
    echo "1. Apply for WhatsApp Business API at: https://business.whatsapp.com/\n";
    echo "2. Complete business verification process\n";
    echo "3. Get your access token and phone number ID\n\n";
    
    $apiUrl = readline("Enter your WhatsApp API URL: ");
    $token = readline("Enter your access token: ");
    $phoneId = readline("Enter your phone number ID: ");
    
    updateEnvFile([
        'WHATSAPP_PROVIDER' => 'meta',
        'WHATSAPP_API_URL' => $apiUrl,
        'WHATSAPP_API_TOKEN' => $token,
        'WHATSAPP_PHONE_NUMBER_ID' => $phoneId
    ]);
    
    echo "\n✅ Meta WhatsApp configuration added to .env file!\n";
    echo "🔄 Please restart your application to apply changes.\n";
    echo "📱 Test by sending an invoice via WhatsApp.\n\n";
}

function setupWeb() {
    echo "\n🌐 Setting up Web-based WhatsApp\n";
    echo "===============================\n\n";
    
    updateEnvFile([
        'WHATSAPP_PROVIDER' => 'web'
    ]);
    
    echo "✅ Web-based WhatsApp configured!\n";
    echo "📱 Invoices will open WhatsApp Web for manual sending.\n";
    echo "💡 To enable automatic sending, choose option 1 or 2.\n\n";
}

function updateEnvFile($variables) {
    $envFile = '.env';
    
    if (!file_exists($envFile)) {
        echo "❌ .env file not found. Please copy .env.example to .env first.\n";
        exit(1);
    }
    
    $envContent = file_get_contents($envFile);
    
    foreach ($variables as $key => $value) {
        $pattern = "/^{$key}=.*$/m";
        $replacement = "{$key}={$value}";
        
        if (preg_match($pattern, $envContent)) {
            // Update existing variable
            $envContent = preg_replace($pattern, $replacement, $envContent);
        } else {
            // Add new variable
            $envContent .= "\n{$replacement}";
        }
    }
    
    file_put_contents($envFile, $envContent);
}

echo "🎉 Setup complete! Your WhatsApp integration is ready.\n";
echo "📖 For more details, check the .env.whatsapp.example file.\n";
