<script>
    window.ClinicI18n = {
        locale: @json(app()->getLocale()),
        messages: @json(app()->getLocale() === 'ur' ? json_decode(file_get_contents(lang_path('ur.json')), true) : new stdClass()),
        t(key, replacements = {}) {
            let value = this.messages[key] || key;
            Object.entries(replacements).forEach(([name, text]) => { value = value.replaceAll(':' + name, String(text)); });
            return value;
        }
    };
</script>
