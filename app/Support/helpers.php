<?php

if (! function_exists('kes')) {
    /** Format an integer amount of Kenyan shillings. */
    function kes(int|float|null $amount, bool $symbol = true): string
    {
        $formatted = number_format((float) ($amount ?? 0), 0);

        return $symbol ? 'KES '.$formatted : $formatted;
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::value($key, $default);
    }
}

if (! function_exists('company')) {
    function company(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::value('company')[$key] ?? $default;
    }
}
