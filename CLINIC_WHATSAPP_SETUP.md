# 📱 WhatsApp Setup Guide for Clinics

## For Clinic Administrators

Each clinic can configure their own WhatsApp integration independently. This guide will walk you through setting up Twilio WhatsApp for your clinic.

---

## ✅ Step-by-Step Setup

### Step 1: Create Twilio Account (5 minutes)

1. **Go to Twilio**
   - Visit: [https://www.twilio.com/try-twilio](https://www.twilio.com/try-twilio)
   - Click **"Sign up"**

2. **Fill in your details:**
   - First Name
   - Last Name
   - Email address
   - Create a password

3. **Verify your account:**
   - Check your email for verification link
   - Click the link to verify
   - Verify your phone number when prompted

4. **You'll get $15 free credit!** 🎉

---

### Step 2: Get Your Credentials (2 minutes)

1. **After logging in**, you'll see the **Twilio Console Dashboard**

2. **Find the "Account Info" section** (usually on the right side)

3. **Copy these two values:**
   - **Account SID** (looks like: `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)
   - **Auth Token** (click the eye icon 👁️ to reveal it)

4. **Keep these safe!** You'll need them in the next step.

---

### Step 3: Enable WhatsApp Sandbox (3 minutes)

1. **In Twilio Console:**
   - Click **"Messaging"** in the left menu
   - Click **"Try it out"**
   - Click **"Send a WhatsApp message"**

2. **You'll see:**
   - A phone number: `+1 415 523 8886`
   - A code like: `join happy-dog` (yours will be different)

3. **On your phone:**
   - Open WhatsApp
   - Create a new message to: `+1 415 523 8886`
   - Send the code exactly as shown (e.g., `join happy-dog`)
   - You'll get a confirmation message ✅

---

### Step 4: Configure in ConCure (2 minutes)

1. **Log in to ConCure** with your clinic admin account

2. **Go to WhatsApp Configuration:**
   - Click **"Administration"** in the sidebar
   - Click **"WhatsApp Configuration"**

3. **Find the "Twilio WhatsApp Configuration" section**

4. **Fill in the form:**
   - **Twilio Account SID:** Paste from Step 2
   - **Twilio Auth Token:** Paste from Step 2
   - **Twilio WhatsApp Number:** `whatsapp:+14155238886` (already filled)

5. **Click "Save Twilio Configuration"**

6. **Wait for success message** ✅

---

### Step 5: Test It! (1 minute)

1. **Scroll down** to "Test WhatsApp Message"

2. **Enter a phone number:**
   - Use country code (e.g., `9647501234567` for Iraq)
   - No spaces or dashes

3. **Type a test message**

4. **Click "Send Test Message"**

5. **Check WhatsApp** - you should receive the message! 🎉

---

## 📞 Phone Number Format

**Always use this format:**
- ✅ **Correct:** `9647501234567` (Iraq)
- ✅ **Correct:** `14155551234` (USA)
- ❌ **Wrong:** `+964 750 123 4567` (has spaces)
- ❌ **Wrong:** `0750 123 4567` (missing country code)

**Country codes:**
- Iraq: `964`
- USA: `1`
- UK: `44`
- UAE: `971`

---

## 💰 Costs

### Free Trial
- **$15 credit** when you sign up
- Enough for **~3,000 messages**

### After Free Trial
- **~$0.005 per message** (half a cent)
- **100 messages = $0.50**
- **1,000 messages = $5.00**

### Example Monthly Costs
- Small clinic (100 messages): **$0.50/month**
- Medium clinic (500 messages): **$2.50/month**
- Large clinic (2,000 messages): **$10/month**

---

## ⚠️ Important Notes

### Sandbox Limitations
- **Only works with numbers that joined the sandbox**
- Each patient must send `join <code>` to `+1 415 523 8886` first
- **For testing only**

### Production (Sending to Any Number)
When ready for production:
1. Upgrade Twilio account
2. Request WhatsApp Business API approval
3. Get your own WhatsApp Business number
4. Update configuration in ConCure

---

## 🔧 Troubleshooting

### "Configuration not saved"
- ✅ Check all fields are filled
- ✅ Account SID should start with `AC`
- ✅ No extra spaces in credentials

### "Test message not received"
- ✅ Recipient must join Twilio sandbox first
- ✅ Check phone number format (country code!)
- ✅ Verify Twilio account has credit

### "Still shows 'Not Configured'"
- ✅ Refresh the page
- ✅ Clear browser cache
- ✅ Try logging out and back in

---

## 🔒 Security

- ✅ Your credentials are stored securely
- ✅ Each clinic has separate configuration
- ✅ Other clinics cannot see your settings
- ✅ Encrypted in database

---

## 📞 Need Help?

Contact ConCure support with:
- Your clinic name
- Screenshot of any error
- Steps you've tried

---

**Last Updated:** January 2026

