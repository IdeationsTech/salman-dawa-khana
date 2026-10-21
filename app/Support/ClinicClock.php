<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use DateTimeZone;

class ClinicClock
{
    // Existing clinical timestamps were recorded as Dubai wall-clock times.
    // Keep their storage convention; convert only at input, display and query boundaries.
    public const STORAGE_ZONE = 'Asia/Dubai';

    public static function zone(?int $clinicId = null): string
    {
        $clinicId ??= auth('web')->user()?->clinic_id;
        if (! $clinicId) {
            return self::STORAGE_ZONE;
        }
        $cacheKey = 'clinic.timezone.'.$clinicId;
        if (! app()->runningInConsole() && request()->attributes->has($cacheKey)) {
            return request()->attributes->get($cacheKey);
        }
        $zone = Setting::where('clinic_id', $clinicId)->where('setting_key', 'timezone')->value('setting_value');

        $zone = in_array($zone, DateTimeZone::listIdentifiers(), true) ? $zone : self::STORAGE_ZONE;
        if (! app()->runningInConsole()) {
            request()->attributes->set($cacheKey, $zone);
        }

        return $zone;
    }

    public static function now(?int $clinicId = null): Carbon
    {
        return Carbon::now(self::zone($clinicId));
    }

    public static function stored($value): string
    {
        return Carbon::parse($value, self::zone())->setTimezone(self::STORAGE_ZONE)->format('Y-m-d H:i:s');
    }

    public static function display($value, string $format = 'd M Y, h:i A'): string
    {
        if ($value === null) {
            return '';
        }

        return Carbon::parse((string) $value, self::STORAGE_ZONE)->setTimezone(self::zone())->format($format);
    }
}
