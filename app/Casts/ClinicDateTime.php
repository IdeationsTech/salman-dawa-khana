<?php

namespace App\Casts;

use App\Support\ClinicClock;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class ClinicDateTime implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value === null ? null : Carbon::parse($value, ClinicClock::STORAGE_ZONE)->setTimezone(ClinicClock::zone($attributes['clinic_id'] ?? null));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value === null ? null : Carbon::parse($value, ClinicClock::zone($attributes['clinic_id'] ?? auth()->user()?->clinic_id))->setTimezone(ClinicClock::STORAGE_ZONE)->format('Y-m-d H:i:s');
    }
}
