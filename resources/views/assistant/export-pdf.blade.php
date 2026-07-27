<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AI Chat History Export</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #0066cc;
            margin: 0 0 5px 0;
            font-size: 20pt;
        }
        .header .clinic-info {
            font-size: 10pt;
            color: #666;
        }
        .export-info {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 9pt;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        .message-user {
            background: #e3f2fd;
            border-left: 3px solid #2196F3;
        }
        .message-assistant {
            background: #f5f5f5;
            border-left: 3px solid #4CAF50;
        }
        .message-header {
            font-size: 9pt;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .message-content {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🤖 AI Medical Assistant - Chat History</h1>
        @if($clinic)
        <div class="clinic-info">
            <strong>{{ $clinic->name }}</strong><br>
            {{ $clinic->address ?? '' }}
        </div>
        @endif
    </div>

    <div class="export-info">
        <strong>User:</strong> {{ $user->full_name }} ({{ $user->role }})<br>
        <strong>Export Date:</strong> {{ $exportDate->format('d M Y, h:i A') }}<br>
        <strong>Total Messages:</strong> {{ $messages->count() }}<br>
        <strong>Date Range:</strong> {{ $messages->first()->created_at->format('d M Y') }} - {{ $messages->last()->created_at->format('d M Y') }}
    </div>

    <div class="messages">
        @foreach($messages as $message)
            <div class="message message-{{ $message->role }}">
                <div class="message-header">
                    @if($message->role === 'assistant')
                        🤖 AI Assistant
                    @else
                        👤 {{ $user->full_name }}
                    @endif
                    • {{ $message->created_at->format('d M Y, h:i A') }}
                </div>
                <div class="message-content">{{ $message->content }}</div>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <strong>ConCure Clinic Management System</strong> | AI Medical Assistant Chat Export<br>
        Page <span class="page-number"></span> | Confidential Medical Information
    </div>
</body>
</html>
