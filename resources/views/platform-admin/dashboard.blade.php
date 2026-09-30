<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Platform Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="margin: 0; background: #f3f7fb; color: #18324b; font-family: system-ui, sans-serif;">

    <main style="max-width: 960px; margin: 48px auto; padding: 24px;">
        <section style="background: #fff; border: 1px solid #dce5ed; border-radius: 16px; padding: 32px;">
            <h1 style="margin: 0 0 12px; font-size: 28px;">
                Platform dashboard
            </h1>

            <p>
                Welcome, {{ auth('platform_admin')->user()->name }}.
            </p>

            <p style="color: #64748b;">
                You are signed in as a platform administrator.
            </p>

            <form
                method="POST"
                action="{{ route('platform-admin.logout') }}"
                style="margin-top: 24px;"
            >
                @csrf

                <button
                    type="submit"
                    style="padding: 12px 20px; border: 0; border-radius: 8px; background: #079b98; color: #fff; font: inherit; cursor: pointer;"
                >
                    Sign out
                </button>
            </form>
        </section>
    </main>

</body>
</html>