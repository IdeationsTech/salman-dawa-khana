<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Exception\MissingResourceException;

final class ClinicLocaleCatalog
{
    public static function countries(): array
    {
        $countries = Countries::getNames('en');

        uasort(
            $countries,
            static fn ($left, $right) =>
                strnatcasecmp(Str::ascii($left), Str::ascii($right))
        );

        return $countries;
    }

    public static function defaults(): array
    {
        $defaults = [];

        foreach (array_keys(self::countries()) as $country) {
            try {
                $codes = Currencies::forCountry($country);
                $defaults[$country] = $codes[0] ?? null;
            } catch (MissingResourceException) {
                $defaults[$country] = null;
            }
        }

        return $defaults;
    }

    public static function currencies(): array
    {
        static $currencies = null;

        if ($currencies !== null) {
            return $currencies;
        }

        $currencies = [];

        foreach (array_keys(self::countries()) as $country) {
            try {
                foreach (Currencies::forCountry($country) as $code) {
                    $currencies[$code] = Currencies::getName($code, 'en');
                }
            } catch (MissingResourceException) {
                // A territory without a listed currency needs manual selection.
            }
        }

        ksort($currencies);

        return $currencies;
    }

    public static function knownCurrency(?string $value): bool
    {
        $code = strtoupper(trim((string) $value));

        return preg_match('/^[A-Z]{3}$/', $code) === 1
            && !in_array($code, ['XXX', 'XTS'], true)
            && Currencies::exists($code);
    }

    public static function countryName(?string $code): string
    {
        return self::countries()[$code ?? ''] ?? 'Not recorded';
    }

    public static function idCountry(?string $country): string
    {
        return in_array($country, ['PK', 'AE'], true)
            ? $country
            : 'ZZ';
    }

    public static function idLabels(): array
    {
        return [
            'PK' => 'Pakistan — CNIC',
            'AE' => 'United Arab Emirates — Emirates ID',
            'ZZ' => 'Other — National ID / Passport / Other ID',
        ];
    }
}