<form method="POST" action="{{ route('language.update') }}" class="language-switcher no-print">
    @csrf
    <input type="hidden" name="context" value="{{ request()->is('platform-admin*') ? 'platform_admin' : 'web' }}">
    <label>
        <span class="visually-hidden">{{ __('Language') }}</span>
        <select name="locale" class="form-select form-select-sm" onchange="this.form.requestSubmit()" aria-label="{{ __('Language') }}">
            <option value="en" lang="en" @selected(app()->getLocale() === 'en')>English</option>
            <option value="ur" lang="ur" @selected(app()->getLocale() === 'ur')>اردو</option>
        </select>
    </label>
    <noscript><button type="submit" class="btn btn-sm btn-primary">{{ __('Apply') }}</button></noscript>
</form>
