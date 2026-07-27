# ConCure WhatsApp API Server

A simple WhatsApp API server for ConCure Clinic Management System that enables automatic WhatsApp message sending.

## Features

- 🚀 **Automatic Message Sending**: Send WhatsApp messages programmatically
- 📎 **File Attachments**: Send PDFs, images, and documents
- 🔐 **Secure API**: Token-based authentication
- 📱 **QR Code Setup**: Easy WhatsApp Web authentication
- 🔄 **Auto-reconnect**: Handles connection drops gracefully

## Quick Setup

### 1. Install Dependencies
```bash
cd whatsapp-server
npm install
```

### 2. Configure Environment
```bash
cp .env.example .env
# Edit .env and set your API_TOKEN
```

### 3. Start Server
```bash
npm start
```

### 4. Scan QR Code
1. Open http://localhost:3000/qr in your browser
2. Scan the QR code with WhatsApp on your phone
3. Wait for "WhatsApp is connected and ready!" message

### 5. Configure ConCure
Update your ConCure `.env` file:
```env
WHATSAPP_PROVIDER=web
WHATSAPP_API_URL=http://localhost:3000
WHATSAPP_API_TOKEN=your-secret-token-here
```

## API Endpoints

### GET /status
Check if WhatsApp is ready
```json
{
  "ready": true,
  "hasQR": false,
  "timestamp": "2025-01-27T10:30:00.000Z"
}
```

### POST /send-message
Send a text message
```json
{
  "phone": "9647501234567",
  "message": "Hello from ConCure!"
}
```

### POST /send-file
Send a file with optional caption
```form-data
phone: 9647501234567
caption: Lab Request PDF
file: [PDF file]
```

## Production Deployment

For production use, consider:
- Using PM2 for process management
- Setting up SSL/HTTPS
- Using a reverse proxy (nginx)
- Implementing rate limiting
- Adding logging and monitoring

## Troubleshooting

**QR Code not showing?**
- Make sure port 3000 is not blocked
- Check if WhatsApp Web works in your browser

**Messages not sending?**
- Verify the phone number format (with country code)
- Check if WhatsApp is still connected
- Look at server logs for errors

**Connection lost?**
- Restart the server
- Re-scan the QR code if needed
- Check your internet connection
