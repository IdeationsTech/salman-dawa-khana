@php
    $languageOptions = [
        'en' => ['label' => 'English', 'native' => 'English'],
        'ur' => ['label' => 'Urdu', 'native' => 'اردو'],
    ];
@endphp

<form
    method="POST"
    action="{{ route('language.update') }}"
    class="language-switcher no-print"
    data-language-switcher
>
    @csrf
    <input
        type="hidden"
        name="context"
        value="{{ request()->is('platform-admin*') ? 'platform_admin' : 'web' }}"
    >

    <button
        type="button"
        class="language-switcher-trigger"
        data-language-trigger
        aria-label="{{ __('Language') }}"
        aria-haspopup="menu"
        aria-expanded="false"
        title="{{ __('Language') }}"
    >
        <i class="bi bi-translate" aria-hidden="true"></i>
        <span class="visually-hidden">{{ __('Language') }}</span>
    </button>

    <div class="language-switcher-options" role="menu" aria-label="{{ __('Language') }}">
        @foreach ($languageOptions as $locale => $language)
            <button
                type="submit"
                name="locale"
                value="{{ $locale }}"
                class="language-switcher-option {{ app()->getLocale() === $locale ? 'is-current' : '' }}"
                role="menuitem"
                lang="{{ $locale }}"
                @if (app()->getLocale() === $locale) aria-current="true" @endif
            >
                <span>{{ $language['native'] }}</span>
                @if (app()->getLocale() === $locale)
                    <i class="bi bi-check2" aria-hidden="true"></i>
                @endif
            </button>
        @endforeach
    </div>
</form>
