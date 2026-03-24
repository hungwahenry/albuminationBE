<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Albumination</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #111111;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            max-width: 520px;
            margin: 0 auto;
            padding: 48px 24px;
        }
        .logo {
            margin-bottom: 40px;
        }
        .logo img {
            height: 26px;
            width: auto;
        }
        .content {
            padding-bottom: 40px;
            border-bottom: 1px solid #f0f0f0;
        }
        .email-text {
            font-size: 15px;
            line-height: 1.7;
            color: #555555;
            margin: 0 0 16px;
        }
        .email-code {
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 10px;
            color: #111111;
            background-color: #f7f7f7;
            border-radius: 8px;
            padding: 20px 24px;
            text-align: center;
            margin: 24px 0;
        }
        .email-footer {
            padding-top: 24px;
            font-size: 12px;
            color: #aaaaaa;
            margin: 0;
        }
        .email-footer a {
            color: #aaaaaa;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="logo">
            <img src="https://albumination.com/wordmark-dark.png" alt="Albumination" />
        </div>
        <div class="content">
            @yield('content')
        </div>
        <p class="email-footer">
            &copy; {{ date('Y') }} Albumination &middot;
            <a href="https://albumination.com/privacy">Privacy</a> &middot;
            <a href="https://albumination.com/terms">Terms</a>
        </p>
    </div>
</body>
</html>
