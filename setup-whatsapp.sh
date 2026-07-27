#!/bin/bash

echo "🚀 Setting up ConCure WhatsApp API Server..."

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js first."
    echo "   Visit: https://nodejs.org/"
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm first."
    exit 1
fi

echo "✅ Node.js and npm are installed"

# Navigate to whatsapp-server directory
cd whatsapp-server

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    
    # Generate a random API token
    API_TOKEN=$(openssl rand -hex 32 2>/dev/null || echo "concure-whatsapp-$(date +%s)")
    
    # Update .env file
    sed -i.bak "s/your-secret-token-here/$API_TOKEN/" .env
    
    echo "🔑 Generated API token: $API_TOKEN"
    echo "   This token has been saved to whatsapp-server/.env"
    
    # Update main ConCure .env file
    cd ..
    if grep -q "WHATSAPP_API_TOKEN=" .env; then
        sed -i.bak "s/WHATSAPP_API_TOKEN=.*/WHATSAPP_API_TOKEN=$API_TOKEN/" .env
    else
        echo "WHATSAPP_API_TOKEN=$API_TOKEN" >> .env
    fi
    echo "✅ Updated ConCure .env with API token"
    cd whatsapp-server
fi

# Install dependencies
echo "📦 Installing dependencies..."
npm install

if [ $? -eq 0 ]; then
    echo "✅ Dependencies installed successfully"
else
    echo "❌ Failed to install dependencies"
    exit 1
fi

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Start the WhatsApp server:"
echo "   cd whatsapp-server && npm start"
echo ""
echo "2. Open http://localhost:3000/qr in your browser"
echo ""
echo "3. Scan the QR code with WhatsApp on your phone"
echo ""
echo "4. Once connected, ConCure will automatically send WhatsApp messages!"
echo ""
echo "💡 Tip: Keep the WhatsApp server running in the background for automatic sending"
