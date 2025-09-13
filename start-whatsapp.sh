#!/bin/bash

echo "🚀 Starting ConCure WhatsApp API Server..."

# Check if whatsapp-server directory exists
if [ ! -d "whatsapp-server" ]; then
    echo "❌ WhatsApp server directory not found!"
    echo "   Please run ./setup-whatsapp.sh first"
    exit 1
fi

# Navigate to whatsapp-server directory
cd whatsapp-server

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "❌ Dependencies not installed!"
    echo "   Please run ./setup-whatsapp.sh first"
    exit 1
fi

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "❌ Configuration file not found!"
    echo "   Please run ./setup-whatsapp.sh first"
    exit 1
fi

echo "✅ Starting WhatsApp server..."
echo "📱 QR Code will be available at: http://localhost:3000/qr"
echo "🔧 Server status at: http://localhost:3000/status"
echo ""
echo "💡 Keep this terminal open to maintain WhatsApp connection"
echo "   Press Ctrl+C to stop the server"
echo ""

# Start the server
npm start
