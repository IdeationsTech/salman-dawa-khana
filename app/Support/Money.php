<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function scaled($value, int $places = 2): int
    {
        $text = trim((string) ($value ?? '0'));

        if (!preg_match('/^([+-]?)(\d+)(?:\.(\d+))?$/', $text, $matches)) {
            throw new InvalidArgumentException('Invalid decimal amount.');
        }

        $fraction = $matches[3] ?? '';

        $result = ((int) $matches[2] * (10 ** $places))
            + (int) str_pad(
                substr($fraction, 0, $places),
                $places,
                '0',
                STR_PAD_RIGHT
            );

        return $matches[1] === '-' ? -$result : $result;
    }

    public static function decimal(int $value, int $places = 2): string
    {
        $sign = $value < 0 ? '-' : '';
        $absolute = abs($value);
        $factor = 10 ** $places;

        return $sign
            . intdiv($absolute, $factor)
            . '.'
            . str_pad(
                (string) ($absolute % $factor),
                $places,
                '0',
                STR_PAD_LEFT
            );
    }
}