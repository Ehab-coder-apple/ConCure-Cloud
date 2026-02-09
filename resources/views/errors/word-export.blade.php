<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Export Error - ConCure</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            text-align: center;
            margin: 20px;
        }
        
        .error-icon {
            font-size: 64px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 16px;
        }
        
        .error-message {
            font-size: 16px;
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .error-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 30px;
            font-size: 14px;
            color: #6c757d;
            text-align: left;
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-1px);
        }
        
        .support-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            font-size: 12px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">📄❌</div>
        
        <h1 class="error-title">Word Export Error</h1>
        
        <p class="error-message">
            We encountered an issue while generating your Word document. This could be due to a temporary server issue or a problem with the nutrition plan data.
        </p>
        
        @if($dietPlan)
        <div class="error-details">
            <strong>Plan Details:</strong><br>
            Plan Number: {{ $dietPlan->plan_number ?? 'N/A' }}<br>
            Patient: {{ $dietPlan->patient->name ?? 'Unknown' }}<br>
            Meals: {{ $dietPlan->meals->count() ?? 0 }} meals
        </div>
        @endif
        
        <div class="btn-group">
            <a href="javascript:history.back()" class="btn btn-secondary">
                ← Go Back
            </a>
            
            @if($dietPlan)
            <a href="{{ route('nutrition.show', $dietPlan) }}" class="btn btn-primary">
                View Plan
            </a>
            
            <a href="{{ route('nutrition.pdf', $dietPlan) }}" class="btn btn-primary">
                Try PDF Export
            </a>
            @endif
        </div>
        
        <div class="support-info">
            <p>If this problem persists, please contact your system administrator.</p>
            <p><strong>Error ID:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
