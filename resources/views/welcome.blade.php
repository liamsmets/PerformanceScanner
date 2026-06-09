<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Performance Scanner</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #090909;
            color: #f5f5f5;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 60px;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #facc15;
        }

        .nav-link {
            color: #f5f5f5;
            text-decoration: none;
            border: 1px solid #333;
            padding: 10px 18px;
            border-radius: 8px;
        }

        .nav-link:hover {
            border-color: #facc15;
            color: #facc15;
        }

        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 60px;
        }

        .hero-content {
            max-width: 1000px;
            width: 100%;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .label {
            color: #facc15;
            font-weight: bold;
            margin-bottom: 16px;
        }

        h1 {
            font-size: 56px;
            margin: 0 0 20px;
            line-height: 1.05;
        }

        .description {
            color: #b5b5b5;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .button {
            display: inline-block;
            background: #facc15;
            color: #111;
            text-decoration: none;
            padding: 14px 22px;
            border-radius: 8px;
            font-weight: bold;
        }

        .button:hover {
            background: #eab308;
        }

        .card {
            background: #151515;
            border: 1px solid #2b2b2b;
            border-radius: 18px;
            padding: 28px;
        }

        .card h2 {
            margin-top: 0;
            color: #facc15;
        }

        .features {
            display: grid;
            gap: 14px;
            margin-top: 24px;
        }

        .feature {
            background: #0d0d0d;
            border: 1px solid #2b2b2b;
            border-radius: 12px;
            padding: 14px;
        }

        .feature strong {
            display: block;
            margin-bottom: 4px;
        }

        .feature span {
            color: #a3a3a3;
            font-size: 14px;
        }

        .metrics {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
        }

        .metric {
            border: 1px solid #333;
            border-radius: 999px;
            padding: 8px 12px;
            color: #d4d4d4;
            font-size: 14px;
        }

        @media (max-width: 800px) {
            .navbar {
                padding: 20px;
            }

            .hero {
                padding: 30px 20px;
            }

            .hero-content {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 38px;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <nav class="navbar">
        <div class="logo">Performance Scanner</div>

        @auth
            <a class="nav-link" href="{{ url('/admin') }}">Naar adminpaneel</a>
        @else
            <a class="nav-link" href="{{ url('/admin/login') }}">Inloggen</a>
        @endauth
    </nav>

    <main class="hero">
        <div class="hero-content">
            <section>
                <div class="label">Graduaatsproef 2026</div>

                <h1>Website performance overzichtelijk opvolgen</h1>

                <p class="description">
                    Performance Scanner helpt om websites automatisch te controleren met Lighthouse.
                    De resultaten worden bewaard zodat je performance, accessibility, SEO en andere
                    scores doorheen de tijd kan vergelijken.
                </p>

                @auth
                    <a class="button" href="{{ url('/admin') }}">Open adminpaneel</a>
                @else
                    <a class="button" href="{{ url('/admin/login') }}">Start met scannen</a>
                @endauth

                <div class="metrics">
                    <span class="metric">Performance</span>
                    <span class="metric">Accessibility</span>
                    <span class="metric">Best practices</span>
                    <span class="metric">SEO</span>
                    <span class="metric">LCP</span>
                    <span class="metric">FCP</span>
                    <span class="metric">TBT</span>
                    <span class="metric">CLS</span>
                </div>
            </section>

            <section class="card">
                <h2>Wat doet de applicatie?</h2>

                <div class="features">
                    <div class="feature">
                        <strong>Websites beheren</strong>
                        <span>Voeg websites toe die regelmatig gecontroleerd moeten worden.</span>
                    </div>

                    <div class="feature">
                        <strong>Audits uitvoeren</strong>
                        <span>Start scans en bewaar de belangrijkste Lighthouse-resultaten.</span>
                    </div>

                    <div class="feature">
                        <strong>Historiek bekijken</strong>
                        <span>Bekijk evoluties in grafieken en vergelijk vorige scans.</span>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
