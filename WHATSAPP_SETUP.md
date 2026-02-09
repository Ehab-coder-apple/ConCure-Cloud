# 📱 ConCure WhatsApp Integration Setup

This guide will help you set up automatic WhatsApp message sending for ConCure Clinic Management System.

## 🚀 Quick Setup (Recommended)

### 1. Run the Setup Script
```bash
./setup-whatsapp.sh
```

### 2. Start the WhatsApp Server
```bash
./start-whatsapp.sh
```

### 3. Connect WhatsApp
1. Open http://localhost:3000/qr in your browser
2. Scan the QR code with WhatsApp on your phone
3. Wait for "WhatsApp is connected and ready!" message

### 4. Test the Integration
1. Log in to ConCure as an admin user
2. Go to **Administration → WhatsApp**
3. Send a test message to verify it works

## 📋 Manual Setup

### Prerequisites
- Node.js 16+ installed
- npm package manager
- WhatsApp account on your phone

### Step 1: Install Dependencies
```bash
cd whatsapp-server
npm install
```

### Step 2: Configure Environment
```bash
cp .env.example .env
# Edit .env and set your API_TOKEN
```

### Step 3: Update ConCure Configuration
Add to your main `.env` file:
```env
WHATSAPP_PROVIDER=web
WHATSAPP_API_URL=http://localhost:3000
WHATSAPP_API_TOKEN=your-secret-token-here
```

### Step 4: Start Server
```bash
npm start
```

## 🔧 Configuration Options

ConCure supports multiple WhatsApp providers:

### Option 1: Web API (Default - Recommended)
```env
WHATSAPP_PROVIDER=web
WHATSAPP_API_URL=http://localhost:3000
WHATSAPP_API_TOKEN=your-secret-token
```

### Option 2: Twilio WhatsApp API
```env
WHATSAPP_PROVIDER=twilio
TWILIO_SID=your-twilio-sid
TWILIO_TOKEN=your-twilio-token
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### Option 3: Official WhatsApp Business API
```env
WHATSAPP_PROVIDER=official
WHATSAPP_API_TOKEN=your-facebook-token
WHATSAPP_PHONE_NUMBER_ID=your-phone-number-id
```

### Option 4: ChatAPI.com
```env
WHATSAPP_PROVIDER=chatapi
WHATSAPP_API_URL=https://api.chat-api.com/instance123456
WHATSAPP_API_TOKEN=your-chatapi-token
```

## 🎯 How It Works

### Before (Manual)
1. Click "Send via WhatsApp" 
2. System opens WhatsApp Web
3. **You manually send the message**
4. **You manually attach the PDF**

### After (Automatic)
1. Click "Send via WhatsApp"
2. **Message sent automatically**
3. **PDF attached automatically**
4. **Success confirmation**

## 📱 Features

- ✅ **Automatic Message Sending**: No manual intervention needed
- ✅ **PDF Attachments**: Lab requests, prescriptions, etc.
- ✅ **Phone Number Formatting**: Automatic country code handling
- ✅ **Error Handling**: Graceful fallback to manual sending
- ✅ **Admin Interface**: Monitor status and test messages
- ✅ **Multiple Providers**: Choose the best option for your needs

## 🔍 Troubleshooting

### WhatsApp Server Won't Start
```bash
# Check if Node.js is installed
node --version

# Check if port 3000 is available
lsof -i :3000

# Install dependencies
cd whatsapp-server && npm install
```

### QR Code Not Showing
- Make sure the server is running on port 3000
- Check if your browser can access http://localhost:3000
- Try refreshing the page

### Messages Not Sending
- Verify WhatsApp is connected (green status in admin panel)
- Check phone number format (include country code)
- Look at server logs for error messages

### Connection Lost
- Restart the WhatsApp server
- Re-scan the QR code
- Check your internet connection

## 🏥 Usage in ConCure

### Clinic WhatsApp Settings
1. Go to **Settings → General Settings**
2. Set your **clinic's default WhatsApp number**
3. This number will be used automatically when no specific WhatsApp number is provided

### Lab Requests
1. Create a lab request
2. **WhatsApp number auto-filled** from clinic settings
3. Click **"Send via WhatsApp"**
4. Message and PDF sent automatically to the lab

### Prescriptions
1. Create a prescription
2. Click **"Send via WhatsApp"**
3. Message and PDF sent automatically to the patient

### Appointments
1. Schedule an appointment
2. Click **"Send Reminder"**
3. WhatsApp reminder sent automatically

### Smart Fallback System
- **Lab-specific WhatsApp** (if provided) → **Clinic default WhatsApp** → **Manual entry**
- No more missing WhatsApp numbers!

## 🔐 Security

- **Token Authentication**: All API calls require authentication
- **Local Processing**: Messages processed on your server
- **No Data Storage**: Messages not stored by the WhatsApp server
- **Secure Connection**: HTTPS support for production

## 🚀 Production Deployment

For production use:

1. **Use PM2 for Process Management**
```bash
npm install -g pm2
pm2 start whatsapp-server/server.js --name whatsapp-api
pm2 startup
pm2 save
```

2. **Set up Reverse Proxy (nginx)**
```nginx
location /whatsapp-api/ {
    proxy_pass http://localhost:3000/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

3. **Use Environment Variables**
```env
WHATSAPP_API_URL=https://yourdomain.com/whatsapp-api
```

## 📞 Support

If you need help:
1. Check the troubleshooting section above
2. Look at server logs for error messages
3. Test with the admin interface first
4. Verify your WhatsApp connection status

## 🎉 Success!

Once set up, you'll see:
- ✅ Green status in WhatsApp admin panel
- ✅ Automatic message sending works
- ✅ PDF attachments included
- ✅ No more manual WhatsApp Web usage

Your clinic staff can now send lab requests, prescriptions, and appointment reminders with just one click! 🚀
