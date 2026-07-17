<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>&#128995; VoidForgeStore | Maintenance</title>
    <style>
        :root {
            --bg: #050816;
            --bg-deep: #02040d;
            --surface: #0c1326;
            --text: #edf3ff;
            --muted: #8e99b8;
            --line: rgba(136, 156, 211, 0.22);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top, rgba(91, 231, 255, 0.16) 0, transparent 26%),
                radial-gradient(circle at 80% 10%, rgba(131, 95, 255, 0.12) 0, transparent 22%),
                linear-gradient(180deg, #081022 0%, var(--bg) 38%, var(--bg-deep) 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .page {
            width: min(520px, 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2.5rem;
            text-align: center;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-decoration: none;
            text-transform: uppercase;
            text-shadow: 0 0 16px rgba(91, 231, 255, 0.24);
            color: var(--text);
        }

        .brand-mark {
            width: 1.1em;
            height: 1.1em;
            border-radius: 50%;
            background: radial-gradient(circle at 38% 38%, #5be7ff 0%, #835fff 60%, #1a1060 100%);
            box-shadow: 0 0 18px 4px rgba(91, 231, 255, 0.28), 0 0 6px 1px rgba(131, 95, 255, 0.38);
            flex-shrink: 0;
        }

        .card {
            width: 100%;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--line);
            border-radius: 0.75rem;
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .lead {
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        .divider {
            height: 1px;
            background: var(--line);
            margin: 0.25rem 0;
        }

        .contact {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .contact a {
            color: var(--text);
            text-decoration: underline;
            text-underline-offset: 3px;
        }
    </style>
</head>
<body>
    <div class="page">
        <a class="brand" href="/">
            <span class="brand-mark"></span>
            <span>VoidForgeStore</span>
        </a>

        <div class="card">
            <h1>Down for maintenance</h1>
            <p class="lead">
                We're making some updates to the store. Everything will be back up shortly — thank you for your patience.
            </p>
            <div class="divider"></div>
            <p class="contact">
                For urgent enquiries contact us at
                <a href="mailto:{{ config('legal.support_email') }}">{{ config('legal.support_email') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
