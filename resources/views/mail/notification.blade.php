<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { background: #4f46e5; padding: 28px 40px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 600; letter-spacing: -.3px; }
        .body { padding: 36px 40px; color: #374151; font-size: 15px; line-height: 1.65; }
        .body p { margin: 0 0 16px; }
        .body a { color: #4f46e5; text-decoration: none; }
        .body a.btn { display: inline-block; margin: 8px 0 16px; padding: 10px 24px; background: #4f46e5; color: #ffffff; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 40px; color: #9ca3af; font-size: 12px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ \App\Models\Setting::get('company_name', config('app.name')) }}</h1>
        </div>
        <div class="body">
            {!! $htmlBody !!}
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ \App\Models\Setting::get('company_name', config('app.name')) }}.
            {{ __('mail.footer_note') }}
        </div>
    </div>
</body>
</html>
