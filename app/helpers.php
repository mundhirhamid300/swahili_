<?php

/** Hii faili ni sehemu ya mantiki kuu ya programu. */

if (! function_exists('template_asset')) {
    function template_asset(string $path = ''): string
    {
        return asset('lms-assets/'.ltrim($path, '/'));
    }
}
