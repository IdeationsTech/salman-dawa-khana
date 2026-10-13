<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class InternationalPhone implements ValidationRule
{
    /*
    |--------------------------------------------------------------------------
    | Phone — International Format Validation
    |--------------------------------------------------------------------------
    */

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (! is_string($value)) {
            $fail('Enter a valid phone number.');

            return;
        }

        $value = trim($value);

        /*
         * The shared phone input will submit the complete number:
         * +971501234567, +923001234567, etc.
         */
        if (! preg_match('/^\+[1-9]\d{1,14}$/', $value)) {
            $fail(
                'Select a country and enter a valid phone number.'
            );

            return;
        }

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $number = $phoneUtil->parse($value, null);

            if (! $phoneUtil->isValidNumber($number)) {
                $fail(
                    'The phone number has an invalid length or prefix for its country.'
                );
            }
        } catch (NumberParseException $exception) {
            $fail(
                'Select a valid country code and phone number.'
            );
        }
    }
}