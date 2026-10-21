<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Platform Admin') | Clinicora</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="auth-page">
        <div class="auth-card">
            <section class="auth-brand-panel">
                <div class="brand-mark">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                </div>

                <h1>Clinicora</h1>

                <p class="brand-tagline">
                    {{ __('Platform Administration') }}
                </p>

                <div class="brand-divider"></div>

                <p class="brand-description">
                    {{ __('Manage clinics, licenses and subscriptions. Authorized administrators only.') }}
                </p>
            </section>

            <section class="auth-form-panel">
                <div class="auth-form-content">
                    <div class="auth-heading">
                        <p class="eyebrow">{{ __('PLATFORM CONSOLE') }}</p>
                        <h2>@yield('heading')</h2>
                        <p>@yield('description')</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success" role="status">
                            {{ __(session('status')) }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ __($error) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('form')
                </div>
            </section>
        </div>
    </main>
</body>
</html>