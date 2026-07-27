<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #008080;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #008080;
            margin: 0;
            font-size: 24px;
        }
        .content {
            white-space: pre-line;
            margin-bottom: 30px;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>ConCure Clinic Management</h1>
            <p style="margin: 0; color: #666;">{{ $subject }}</p>
        </div>
        
        <div class="content">
            {{ $message }}
        </div>
        
        <div class="footer">
            <p>This email was sent from ConCure Clinic Management System</p>
            <p>If you received this email in error, please ignore it.</p>
        </div>
    </div>
</body>
</html>
