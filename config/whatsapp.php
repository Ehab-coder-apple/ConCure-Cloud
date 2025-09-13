<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Configuration for Desktop Applications
    |--------------------------------------------------------------------------
    |
    | This configuration file allows you to set up WhatsApp API integration
    | for desktop applications. Choose your preferred provider and configure
    | the appropriate credentials.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | Supported providers: "twilio", "meta", "chatapi", "web", "wppconnect"
    |
    | For desktop applications, we recommend:
    | - "twilio" for reliable API-based sending
    | - "meta" for official WhatsApp Business API
    | - "wppconnect" for self-hosted solutions
    |
    */
    'default_provider' => env('WHATSAPP_PROVIDER', 'twilio'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    */

    'providers' => [
        
        /*
        |--------------------------------------------------------------------------
        | Twilio WhatsApp API
        |--------------------------------------------------------------------------
        | 
        | Easy to set up, reliable, and works great for desktop applications.
        | Sign up at: https://www.twilio.com/whatsapp
        |
        | Steps to configure:
        | 1. Create Twilio account
        | 2. Get WhatsApp sandbox or approved number
        | 3. Set your credentials in .env file
        |
        */
        'twilio' => [
            'account_sid' => env('TWILIO_SID'),
            'auth_token' => env('TWILIO_TOKEN'),
            'from_number' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'), // Sandbox number
            'enabled' => env('TWILIO_ENABLED', true),
        ],

        /*
        |--------------------------------------------------------------------------
        | Meta WhatsApp Business API
        |--------------------------------------------------------------------------
        |
        | Official WhatsApp Business API from Meta (Facebook).
        | More complex setup but official support.
        | 
        | Steps to configure:
        | 1. Create Meta Business account
        | 2. Set up WhatsApp Business API
        | 3. Get access token and phone number ID
        |
        */
        'meta' => [
            'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
            'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
            'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
            'app_secret' => env('WHATSAPP_APP_SECRET'),
            'enabled' => env('META_WHATSAPP_ENABLED', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | ChatAPI.com
        |--------------------------------------------------------------------------
        |
        | Third-party WhatsApp API service.
        | Good for desktop applications with simple setup.
        |
        */
        'chatapi' => [
            'api_url' => env('CHATAPI_URL'), // e.g., https://api.chat-api.com/instance123456
            'api_token' => env('CHATAPI_TOKEN'),
            'enabled' => env('CHATAPI_ENABLED', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | WPPConnect (Self-hosted)
        |--------------------------------------------------------------------------
        |
        | Open-source WhatsApp Web API.
        | Perfect for desktop applications that need full control.
        | GitHub: https://github.com/wppconnect-team/wppconnect
        |
        */
        'wppconnect' => [
            'api_url' => env('WPPCONNECT_URL', 'http://localhost:21465'),
            'session_name' => env('WPPCONNECT_SESSION', 'clinic_session'),
            'api_key' => env('WPPCONNECT_API_KEY'),
            'enabled' => env('WPPCONNECT_ENABLED', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | Web WhatsApp (Fallback)
        |--------------------------------------------------------------------------
        |
        | Fallback to web.whatsapp.com URLs.
        | No API credentials needed but requires manual sending.
        |
        */
        'web' => [
            'enabled' => true,
            'auto_open' => env('WHATSAPP_WEB_AUTO_OPEN', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'max_size' => env('WHATSAPP_MAX_FILE_SIZE', 16777216), // 16MB default
        'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'],
        'temp_directory' => storage_path('app/temp/whatsapp'),
        'cleanup_after' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'max_length' => 4096,
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '964'), // Iraq
        'retry_attempts' => 3,
        'retry_delay' => 5, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Desktop Application Settings
    |--------------------------------------------------------------------------
    */
    'desktop' => [
        'enable_logging' => env('WHATSAPP_DESKTOP_LOGGING', true),
        'log_file' => storage_path('logs/whatsapp.log'),
        'enable_notifications' => env('WHATSAPP_DESKTOP_NOTIFICATIONS', true),
        'auto_retry_failed' => env('WHATSAPP_AUTO_RETRY', true),
    ],
];
