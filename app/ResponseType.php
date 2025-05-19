<?php

namespace App;

use Illuminate\Support\Carbon;

enum ResponseType: string
{
    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case Boolean = 'boolean';

    public function cast(mixed $value): string
    {
        return match ($this) {
            self::Text => (string) $value,
            self::Number => (float) $value,
            self::Date => Carbon::parse($value)->format('d.m.Y'),
            self::Boolean => (bool) $value ? 'Ja' : 'Nein',
        };
    }
}
