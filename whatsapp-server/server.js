const express = require('express');
const cors = require('cors');
const multer = require('multer');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const fs = require('fs');
const path = require('path');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 3000;
const apiToken = process.env.API_TOKEN || 'your-secret-token';

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// File upload configuration
const upload = multer({ dest: 'uploads/' });

// WhatsApp client
let client;
let isReady = false;
let qrCodeData = null;

// Initialize WhatsApp client
function initializeClient() {
    client = new Client({
        authStrategy: new LocalAuth({
            clientId: "concure-clinic"
        }),
        puppeteer: {
            headless: true,
            executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-web-security',
                '--disable-features=VizDisplayCompositor'
            ]
        }
    });

    client.on('qr', async (qr) => {
        console.log('QR Code received, scan with WhatsApp app');
        qrCodeData = await qrcode.toDataURL(qr);
        console.log('QR Code available at: http://localhost:' + port + '/qr');
    });

    client.on('ready', () => {
        console.log('WhatsApp client is ready!');
        isReady = true;
        qrCodeData = null;
    });

    client.on('authenticated', () => {
        console.log('WhatsApp client authenticated');
    });

    client.on('auth_failure', (msg) => {
        console.error('Authentication failed:', msg);
    });

    client.on('disconnected', (reason) => {
        console.log('WhatsApp client disconnected:', reason);
        isReady = false;
    });

    client.initialize();
}

// Authentication middleware
function authenticateToken(req, res, next) {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
        return res.status(401).json({ error: 'Access token required' });
    }

    if (token !== apiToken) {
        return res.status(403).json({ error: 'Invalid access token' });
    }

    next();
}

// Routes
app.get('/', (req, res) => {
    res.json({
        service: 'ConCure WhatsApp API Server',
        version: '1.0.0',
        status: isReady ? 'ready' : 'initializing',
        endpoints: {
            status: 'GET /status',
            qr: 'GET /qr',
            send_message: 'POST /send-message',
            send_file: 'POST /send-file'
        }
    });
});

app.get('/status', (req, res) => {
    res.json({
        ready: isReady,
        hasQR: !!qrCodeData,
        timestamp: new Date().toISOString()
    });
});

app.get('/qr', (req, res) => {
    if (qrCodeData) {
        res.send(`
            <html>
                <head><title>WhatsApp QR Code</title></head>
                <body style="text-align: center; font-family: Arial;">
                    <h2>Scan this QR code with WhatsApp</h2>
                    <img src="${qrCodeData}" alt="QR Code" style="max-width: 400px;">
                    <p>Refresh this page if the QR code expires</p>
                    <script>
                        setTimeout(() => location.reload(), 30000);
                    </script>
                </body>
            </html>
        `);
    } else if (isReady) {
        res.send(`
            <html>
                <head><title>WhatsApp Status</title></head>
                <body style="text-align: center; font-family: Arial;">
                    <h2>✅ WhatsApp is connected and ready!</h2>
                    <p>You can now send messages through the API</p>
                </body>
            </html>
        `);
    } else {
        res.send(`
            <html>
                <head><title>WhatsApp Status</title></head>
                <body style="text-align: center; font-family: Arial;">
                    <h2>⏳ WhatsApp is initializing...</h2>
                    <p>Please wait while we set up the connection</p>
                    <script>
                        setTimeout(() => location.reload(), 5000);
                    </script>
                </body>
            </html>
        `);
    }
});

app.post('/send-message', authenticateToken, async (req, res) => {
    if (!isReady) {
        return res.status(503).json({
            success: false,
            error: 'WhatsApp client not ready'
        });
    }

    const { phone, message } = req.body;

    if (!phone || !message) {
        return res.status(400).json({
            success: false,
            error: 'Phone number and message are required'
        });
    }

    try {
        // Format phone number
        let formattedPhone = phone.replace(/[^\d]/g, '');
        if (!formattedPhone.includes('@')) {
            formattedPhone = formattedPhone + '@c.us';
        }

        const sentMessage = await client.sendMessage(formattedPhone, message);
        
        res.json({
            success: true,
            id: sentMessage.id.id,
            timestamp: sentMessage.timestamp,
            to: phone
        });
    } catch (error) {
        console.error('Send message error:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

app.post('/send-file', authenticateToken, upload.single('file'), async (req, res) => {
    if (!isReady) {
        return res.status(503).json({
            success: false,
            error: 'WhatsApp client not ready'
        });
    }

    const { phone, caption } = req.body;
    const file = req.file;

    if (!phone) {
        return res.status(400).json({
            success: false,
            error: 'Phone number is required'
        });
    }

    if (!file) {
        return res.status(400).json({
            success: false,
            error: 'File is required'
        });
    }

    try {
        // Format phone number
        let formattedPhone = phone.replace(/[^\d]/g, '');
        if (!formattedPhone.includes('@')) {
            formattedPhone = formattedPhone + '@c.us';
        }

        const media = MessageMedia.fromFilePath(file.path);
        const sentMessage = await client.sendMessage(formattedPhone, media, { caption });
        
        // Clean up uploaded file
        fs.unlinkSync(file.path);
        
        res.json({
            success: true,
            id: sentMessage.id.id,
            timestamp: sentMessage.timestamp,
            to: phone
        });
    } catch (error) {
        console.error('Send file error:', error);
        
        // Clean up uploaded file on error
        if (file && fs.existsSync(file.path)) {
            fs.unlinkSync(file.path);
        }
        
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Start server
app.listen(port, () => {
    console.log(`ConCure WhatsApp API Server running on port ${port}`);
    console.log(`Access QR code at: http://localhost:${port}/qr`);
    initializeClient();
});
