# WhatsApp Integration Setup Guide for Desktop Applications

This guide will help you set up WhatsApp integration for your desktop clinic management application to send invoices, lab reports, and other documents directly via WhatsApp.

## 🚀 Quick Start (Recommended for Desktop Apps)

### Option 1: Twilio WhatsApp API (Easiest)

**Best for**: Desktop applications that need reliable, immediate setup

1. **Create Twilio Account**
   - Go to [https://www.twilio.com/whatsapp](https://www.twilio.com/whatsapp)
   - Sign up for a free account
   - Get $15 free credit

2. **Get WhatsApp Sandbox Credentials**
   - Go to Console → Messaging → Try it out → Send a WhatsApp message
   - Note your Account SID and Auth Token
   - Use sandbox number: `whatsapp:+14155238886`

3. **Configure Your Application**
   Add to your `.env` file:
   ```env
   WHATSAPP_PROVIDER=twilio
   TWILIO_SID=your_account_sid_here
   TWILIO_TOKEN=your_auth_token_here
   TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
   TWILIO_ENABLED=true
   ```

4. **Test the Integration**
   - Go to WhatsApp settings in your app
   - Send a test message
   - PDFs will be sent as attachments automatically!

### Option 2: Meta WhatsApp Business API (Official)

**Best for**: Production applications with high volume

1. **Create Meta Business Account**
   - Go to [https://business.facebook.com](https://business.facebook.com)
   - Create business account
   - Set up WhatsApp Business API

2. **Get API Credentials**
   - Access Token
   - Phone Number ID
   - App Secret

3. **Configure Your Application**
   ```env
   WHATSAPP_PROVIDER=meta
   WHATSAPP_ACCESS_TOKEN=your_access_token
   WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
   WHATSAPP_APP_SECRET=your_app_secret
   META_WHATSAPP_ENABLED=true
   ```

### Option 3: WPPConnect (Self-hosted, Free)

**Best for**: Desktop apps that need full control and no monthly fees

1. **Install WPPConnect Server**
   ```bash
   npm install -g @wppconnect-team/wppconnect-server
   wppconnect-server --port 21465
   ```

2. **Configure Your Application**
   ```env
   WHATSAPP_PROVIDER=wppconnect
   WPPCONNECT_URL=http://localhost:21465
   WPPCONNECT_SESSION=clinic_session
   WPPCONNECT_ENABLED=true
   ```

3. **Initialize Session**
   - Go to WhatsApp settings in your app
   - Click "Setup WPPConnect"
   - Scan QR code with your phone
   - Done! Now you can send PDFs directly

## 📱 How It Works

### For Invoice Sending:

1. **User clicks WhatsApp button** on any invoice
2. **Enters patient's phone number** and optional message
3. **System automatically**:
   - Generates PDF invoice
   - Uploads to WhatsApp API
   - Sends as attachment with message
   - Patient receives PDF directly in WhatsApp

### Supported File Types:
- ✅ PDF invoices
- ✅ Lab reports
- ✅ Medical images (JPG, PNG)
- ✅ Documents (DOC, DOCX)

## 🔧 Advanced Configuration

### Custom Message Templates
Edit `config/whatsapp.php` to customize messages:

```php
'message_templates' => [
    'invoice' => 'Invoice #{invoice_number} from {clinic_name}. Total: {amount}',
    'lab_report' => 'Lab results for {patient_name} are ready.',
    'appointment' => 'Appointment reminder for {date} at {time}.',
],
```

### File Size Limits
```php
'file_upload' => [
    'max_size' => 16777216, // 16MB
    'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
],
```

## 🛠️ Troubleshooting

### Common Issues:

1. **"WhatsApp opens but no message"**
   - Check your API credentials in `.env`
   - Verify phone number format (include country code)

2. **"PDF not sending as attachment"**
   - Ensure your provider supports file uploads
   - Check file size (max 16MB for most providers)

3. **"Invalid phone number"**
   - Use international format: +964xxxxxxxxx
   - Remove spaces and special characters

### Debug Mode:
Add to `.env`:
```env
WHATSAPP_DESKTOP_LOGGING=true
LOG_LEVEL=debug
```

Check logs at: `storage/logs/whatsapp.log`

## 💰 Cost Comparison

| Provider | Setup | Monthly Cost | Per Message | File Attachments |
|----------|-------|--------------|-------------|------------------|
| Twilio | Easy | $0 | $0.005 | ✅ Yes |
| Meta | Complex | $0 | $0.004 | ✅ Yes |
| WPPConnect | Medium | $0 | $0 | ✅ Yes |
| ChatAPI | Easy | $20+ | Included | ✅ Yes |

## 🎯 Recommendations

### For Small Clinics (< 100 messages/month):
- **Use Twilio** - Easy setup, reliable, low cost

### For Medium Clinics (100-1000 messages/month):
- **Use WPPConnect** - Free, self-hosted, full control

### For Large Clinics (1000+ messages/month):
- **Use Meta WhatsApp Business API** - Official, scalable, enterprise features

## 📞 Support

If you need help setting up WhatsApp integration:

1. Check the logs: `storage/logs/whatsapp.log`
2. Test with the built-in WhatsApp test page
3. Verify your credentials are correct
4. Ensure your server can make outbound HTTPS requests

## 🔐 Security Notes

- Store API credentials securely in `.env` file
- Use HTTPS for all API communications
- Regularly rotate access tokens
- Monitor usage to detect unauthorized access

---

**Ready to start?** Choose your preferred option above and follow the setup steps. Your patients will love receiving their invoices and reports directly in WhatsApp! 🎉
