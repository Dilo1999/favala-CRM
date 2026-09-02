<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Favala CRM') }}</title>
</head>
<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; padding: 48px;">
    <h1 style="font-size: 40px; margin: 0 0 12px;">{{ config('app.name', 'Favala CRM') }}</h1>
    <p style="font-size: 18px; color: #555; margin: 0 0 24px;">Customer relationship management system</p>
    <a href="{{ url('/admin') }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">Go to admin panel</a>
</body>
</html>
