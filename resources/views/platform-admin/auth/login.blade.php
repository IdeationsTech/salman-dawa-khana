<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Platform Admin Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* PLATFORM ADMIN — Login Screen */

        .platform-login-page {
            --platform-primary: #079b98;
            --platform-primary-hover: #087b79;
            --platform-text: #18324b;
            --platform-muted: #64748b;
            --platform-border: #dce5ed;

            margin: 0;
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f3f7fb;
            color: var(--platform-text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont,
                "Segoe UI", sans-serif;
        }

        .platform-login-page,
        .platform-login-page * {
            box-sizing: border-box;
        }

        .platform-login-card {
            width: 100%;
            max-width: 430px;
            padding: 36px;
            background: #ffffff;
            border: 1px solid var(--platform-border);
            border-radius: 20px;
            box-shadow: 0 16px 48px rgba(24, 50, 75, 0.07);
        }

        .platform-login-label {
            display: inline-block;
            margin-bottom: 18px;
            padding: 7px 12px;
            background: #e6f7f5;
            color: #087b79;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .platform-login-title {
            margin: 0 0 10px;
            font-size: 28px;
            font-weight: 700;
            line-height: 1.25;
        }

        .platform-login-description {
            margin: 0 0 28px;
            color: var(--platform-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .platform-login-field {
            margin-bottom: 20px;
        }

        .platform-login-field label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        .platform-login-input {
            display: block;
            width: 100%;
            min-height: 46px;
            padding: 11px 13px;
            border: 1px solid var(--platform-border);
            border-radius: 10px;
            background: #ffffff;
            color: var(--platform-text);
            font: inherit;
            font-size: 14px;
            transition: border-color 160ms ease, box-shadow 160ms ease;
        }

        .platform-login-input:focus {
            outline: none;
            border-color: var(--platform-primary);
            box-shadow: 0 0 0 3px rgba(7, 155, 152, 0.12);
        }

        .platform-login-input[aria-invalid="true"] {
            border-color: #dc3545;
        }

        .platform-login-error {
            margin: 7px 0 0;
            color: #b42318;
            font-size: 13px;
            line-height: 1.5;
        }

        .platform-login-remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 24px;
            font-size: 14px;
            cursor: pointer;
        }

        .platform-login-remember input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--platform-primary);
        }

        .platform-login-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 46px;
            padding: 12px 18px;
            border: 0;
            border-radius: 10px;
            background: var(--platform-primary);
            color: #ffffff;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 160ms ease, transform 160ms ease;
        }

        .platform-login-submit:hover {
            background: var(--platform-primary-hover);
            transform: translateY(-1px);
        }

        .platform-login-submit:focus-visible {
            outline: 3px solid #8ddbd7;
            outline-offset: 3px;
        }

        .platform-login-footer {
            margin: 24px 0 0;
            text-align: center;
            color: var(--platform-muted);
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .platform-login-card {
                padding: 28px 22px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .platform-login-input,
            .platform-login-submit {
                transition: none;
            }

            .platform-login-submit:hover {
                transform: none;
            }
        }
    </style>
</head>

<body class="platform-login-page">

    <main class="platform-login-card">
        <span class="platform-login-label">
            Platform administration
        </span>

        <h1 class="platform-login-title">
            Welcome back
        </h1>

        <p class="platform-login-description">
            Sign in to manage clinics, licenses and subscriptions.
        </p>

        <form
            method="POST"
            action="{{ route('platform-admin.login.submit') }}"
        >
            @csrf

            <div class="platform-login-field">
                <label for="platform-admin-email">
                    Email address
                </label>

                <input
                    type="email"
                    id="platform-admin-email"
                    name="email"
                    class="platform-login-input"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    maxlength="160"
                    required
                    autofocus
                    @error('email')
                        aria-invalid="true"
                        aria-describedby="platform-email-error"
                    @enderror
                >

                @error('email')
                    <p
                        id="platform-email-error"
                        class="platform-login-error"
                        role="alert"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="platform-login-field">
                <label for="platform-admin-password">
                    Password
                </label>

                <input
                    type="password"
                    id="platform-admin-password"
                    name="password"
                    class="platform-login-input"
                    autocomplete="current-password"
                    required
                    @error('password')
                        aria-invalid="true"
                        aria-describedby="platform-password-error"
                    @enderror
                >

                @error('password')
                    <p
                        id="platform-password-error"
                        class="platform-login-error"
                        role="alert"
                    >
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <label class="platform-login-remember">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                >

                <span>Remember me</span>
            </label>

            <button type="submit" class="platform-login-submit">
                Sign in
            </button>
        </form>

        <p class="platform-login-footer">
            Authorized platform administrators only.
        </p>
    </main>

</body>
</html>