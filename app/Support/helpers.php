<?php

use Illuminate\Support\Facades\Request;

if (! function_exists('nav_is_active')) {
    /**
     * Check if current route matches any of the given patterns.
     *
     * @param  array<string>|null  $patterns
     */
    function nav_is_active(?array $patterns): bool
    {
        foreach ($patterns ?? [] as $pattern) {
            if (is_string($pattern) && $pattern !== '' && Request::routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
