<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ConCure Application Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options specific to the ConCure
    | clinic management system.
    |
    */

    'company_name' => env('CONCURE_COMPANY_NAME', 'ConCure Clinic'),
    
    'primary_color' => env('CONCURE_PRIMARY_COLOR', '#008080'),
    
    'supported_languages' => [
        'en' => 'English',
        'ar' => 'العربية',
        'ku' => 'کوردی',
    ],
    
    'default_language' => env('CONCURE_DEFAULT_LANGUAGE', 'en'),
    
    'currency' => env('CONCURE_CURRENCY', 'USD'),
    
    'currency_symbol' => env('CONCURE_CURRENCY_SYMBOL', '$'),
    
    'timezone' => env('CONCURE_TIMEZONE', 'UTC'),
    
    'date_format' => env('CONCURE_DATE_FORMAT', 'Y-m-d'),
    
    'time_format' => env('CONCURE_TIME_FORMAT', 'H:i'),
    
    'datetime_format' => env('CONCURE_DATETIME_FORMAT', 'Y-m-d H:i'),
    
    'pagination' => [
        'per_page' => env('CONCURE_PAGINATION_PER_PAGE', 15),
        'max_per_page' => env('CONCURE_PAGINATION_MAX_PER_PAGE', 100),
    ],
    
    'features' => [
        'patient_management' => true,
        'prescription_system' => true,
        'lab_requests' => true,
        'financial_management' => true,
        'food_database' => true,
        'advertisement_system' => true,
        'audit_logging' => true,
        'multilingual_support' => true,
        'pdf_generation' => true,
        'file_uploads' => true,
    ],
    
    'roles' => [
        'admin' => 'Administrator',
        'doctor' => 'Doctor',
        'assistant' => 'Assistant',
        'nurse' => 'Nurse',
        'accountant' => 'Accountant',
        'patient' => 'Patient',
    ],
    
    'patient' => [
        'id_prefix' => env('CONCURE_PATIENT_ID_PREFIX', 'P'),
        'id_length' => env('CONCURE_PATIENT_ID_LENGTH', 6),
    ],
    
    'prescription' => [
        'id_prefix' => env('CONCURE_PRESCRIPTION_ID_PREFIX', 'RX'),
        'id_length' => env('CONCURE_PRESCRIPTION_ID_LENGTH', 8),
    ],
    
    'invoice' => [
        'id_prefix' => env('CONCURE_INVOICE_ID_PREFIX', 'INV'),
        'id_length' => env('CONCURE_INVOICE_ID_LENGTH', 8),
    ],
    
    'uploads' => [
        'max_file_size' => env('CONCURE_MAX_FILE_SIZE', 10240), // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'storage_disk' => env('CONCURE_STORAGE_DISK', 'public'),
    ],
    
    'notifications' => [
        'email_enabled' => env('CONCURE_EMAIL_NOTIFICATIONS', true),
        'sms_enabled' => env('CONCURE_SMS_NOTIFICATIONS', false),
    ],
    
    'backup' => [
        'enabled' => env('CONCURE_BACKUP_ENABLED', false),
        'frequency' => env('CONCURE_BACKUP_FREQUENCY', 'daily'),
        'retention_days' => env('CONCURE_BACKUP_RETENTION_DAYS', 30),
    ],

];
