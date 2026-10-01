<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Registration received</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .registration-pending-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            margin: 0;
            padding: 24px;
            background: #f3f7fb;
            color: #18324b;
            font-family: "Segoe UI", system-ui, sans-serif;
        }

        .registration-pending-card {
            width: 100%;
            max-width: 500px;
            padding: 36px;
            border: 1px solid #dce5ed;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 16px 48px rgba(24, 50, 75, 0.07);
        }

        .registration-pending-label {
            display: inline-block;
            margin-bottom: 18px;
            padding: 7px 12px;
            border-radius: 8px;
            background: #e6f7f5;
            color: #087b79;
            font-size: 12px;
            font-weight: 700;
        }

        .registration-pending-card h1 {
            margin: 0 0 12px;
            font-size: 27px;
        }

        .registration-pending-card p {
            margin: 0 0 16px;
            color: #526477;
            line-height: 1.65;
        }

        .registration-pending-card a {
            display: inline-block;
            margin-top: 8px;
            color: #087b79;
            font-weight: 600;
        }
    </style>
</head>

<body class="registration-pending-page">
    <main class="registration-pending-card">
        <span class="registration-pending-label">
            Registration received
        </span>

        <h1>Your clinic is awaiting approval</h1>

        <p>
            Your clinic registration has been submitted. Once your
            manual payment is confirmed and a subscription is assigned,
            your account will be activated.
        </p>

        <p>
            Contact the software provider for payment details and
            confirmation.
        </p>

        <a href="{{ route('login') }}">
            Go to login
        </a>
    </main>
</body>
</html>