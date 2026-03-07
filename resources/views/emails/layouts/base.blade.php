<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Albumination</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f4f5;
            color: #1a1a1a;
        }
        .email-wrapper {
            max-width: 560px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .email-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px 32px;
        }
        .email-logo {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 32px;
        }
        .email-code {
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 8px;
            color: #1a1a1a;
            text-align: center;
            padding: 20px 0;
            background-color: #f4f4f5;
            border-radius: 8px;
            margin: 24px 0;
        }
        .email-text {
            font-size: 15px;
            line-height: 1.6;
            color: #4a4a4a;
            margin-bottom: 16px;
        }
        .email-footer {
            text-align: center;
            font-size: 13px;
            color: #9a9a9a;
            margin-top: 32px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-card">
            <div class="email-logo">Albumination</div>
            @yield('content')
        </div>
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} Albumination. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
