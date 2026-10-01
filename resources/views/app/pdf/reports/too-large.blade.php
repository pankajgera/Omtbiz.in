<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report too large</title>
    <style>
        body { margin: 0; padding: 48px 24px; font-family: "DejaVu Sans", Arial, sans-serif; color: #000; background: #fff; text-align: center; }
        h1 { margin: 0 0 12px; font-size: 18px; }
        p { margin: 0 auto 8px; max-width: 520px; font-size: 14px; line-height: 1.5; }
    </style>
</head>
<body>
    <h1>This period is too large to {{ $isPreview ? 'preview' : 'create as a PDF' }}</h1>
    <p><strong>{{ $ledger->account }}</strong> has {{ format_inr($rows, 0) }} transactions in the selected dates.
        {{ $isPreview ? 'The preview' : 'A PDF' }} can show up to {{ format_inr($maxRows, 0) }}.</p>
    <p>Choose a shorter date range (for example one month) and try again.</p>
</body>
</html>
